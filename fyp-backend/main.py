import sys, os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), ".."))  # import from parent

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from contextlib import asynccontextmanager
from typing import Optional
import traceback

from rag_chain import build_rag_chain, run_rag, retrieve_candidates
from config import (
    TOP_N, MODEL_NAME, LLM_MODEL_ID,
    SIM_RELEVANT, SIM_GOOD,
)

# Load models once at startup
rag_state: dict = {}

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Load the RAG chain once when the server starts."""
    print("Loading RAG chain...")
    try:
        rag_state["rag"] = build_rag_chain()
        print("RAG chain ready.")
    except Exception as e:
        print(f"ERROR loading RAG chain: {e}")
        rag_state["error"] = str(e)
    yield
    # Shutdown: nothing to clean up for ChromaDB/sentence-transformers
    rag_state.clear()

# App
app = FastAPI(
    title="FYP Supervisor Recommender API",
    description="RAG pipeline: ChromaDB retrieval → LLM reranking → LLM reasoning",
    version="1.0.0",
    lifespan=lifespan,
)

# Allow Laravel dev server 
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:8080",   # Laravel php artisan serve default
        "http://localhost:8001",   # alternate Laravel port
        "http://127.0.0.1:8080",
        "http://127.0.0.1:8001",
    ],
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["Content-Type", "Accept", "X-Requested-With"],
)


# Request / Response models 
class RecommendRequest(BaseModel):
    title:       str            = Field(..., min_length=2, description="FYP title")
    description: Optional[str] = Field("", description="Research description")
    top_n: Optional[int] = Field(TOP_N, ge=1, le=10)

class LecturerHit(BaseModel):
    llm_rank:       int
    vector_rank:    int
    name:           str
    similarity:     float
    summary:        str
    num_articles:   int
    num_proceedings: int
    email:          str   
    google_scholar: str
    img_url:        str
    is_relevant:    bool
    is_strong:      bool

class RecommendResponse(BaseModel):
    #top_hits: list[LecturerHit]
    candidates: list[dict]
    reasoning: str
    meta: dict

# Helpers
def _clean_hit(h: dict) -> dict:
    """Ensure all fields exist and are the right type for serialisation."""
    return {
        "llm_rank":       int(h.get("llm_rank") or 0),
        "vector_rank":    int(h.get("vector_rank", 0)),
        "name":           str(h.get("name", "")),
        "similarity":     float(h.get("similarity", 0)),
        "summary":        str(h.get("summary", "")),
        "num_articles":   int(h.get("num_articles", 0)),
        "num_proceedings": int(h.get("num_proceedings", 0)),
        "email":          str(h.get("email", "")),
        "google_scholar": str(h.get("google_scholar", "")),
        "img_url":        str(h.get("img_url", "")),
        "is_relevant":    bool(h.get("is_relevant", False)),
        "is_strong":      bool(h.get("is_strong", False)),
    }

# Routes
@app.get("/api/health")
def health():
    if "error" in rag_state:
        raise HTTPException(status_code=503, detail=rag_state["error"])
    rag = rag_state.get("rag")
    if not rag:
        raise HTTPException(status_code=503, detail="RAG chain not loaded yet.")
    return {
        "status": "OK",
        "lecturer_count": rag["collection"].count(),
        "model": MODEL_NAME,
        "llm": LLM_MODEL_ID,
    }

@app.get("/api/stats")
def stats():
    """System stats"""
    if "rag" not in rag_state:
        raise HTTPException(status_code=503, detail="RAG chain not ready")
    rag = rag_state["rag"]
    return {
        "lecturer_count":  rag["collection"].count(),
        "embed_model":     MODEL_NAME,
        "llm_model":       LLM_MODEL_ID,
        "top_n":           TOP_N,
        "sim_relevant":    SIM_RELEVANT,
        "sim_good":        SIM_GOOD,
    }

@app.get("/api/lecturers")
def list_lecturers():
    """
    Return all lecturers in the DB with name, summary, img_url, and counts.
    Used by the Laravel frontend to build a staff directory page.
    """
    if "rag" not in rag_state:
        raise HTTPException(status_code=503, detail="RAG chain not ready")
    
    rag = rag_state["rag"]
    results = rag["collection"].get(include=["metadatas"])
    lecturers = []
    for meta in results["metadatas"]:
        lecturers.append({
            "name":            meta.get("name", ""),
            "summary":         meta.get("summary", ""),
            "img_url":         meta.get("img_url", ""),
            "google_scholar":  meta.get("google_scholar", ""),
            "num_articles":    meta.get("num_articles", 0),
            "num_proceedings": meta.get("num_proceedings", 0),
        })

    lecturers.sort(key=lambda x: x["name"])
    return {"lecturers": lecturers, "count": len(lecturers)}
    
@app.post("/api/recommend", response_model=RecommendResponse)
def recommend(req: RecommendRequest):
    if "error" in rag_state:
        raise HTTPException(status_code=503, detail=rag_state["error"])
    if "rag" not in rag_state:
        raise HTTPException(status_code=503, detail="RAG chain not ready")
    
    rag = rag_state["rag"]

    try:
        import time
        t0 = time.time()
        result = run_rag(
            rag,
            title=req.title,
            description=req.description or req.title,
            top_n=req.top_n,
        )
        elapsed = round(time.time() - t0, 2)

        return {
            #"top_hits": [_clean_hit(h) for h in result["candidates"]],
            "candidates": [_clean_hit(c) for c in result["candidates"]],
            "reasoning": result["reasoning"],
            "meta": 
            {
                "elapsed_seconds": elapsed,
                "top_n":           req.top_n,
                "embed_model":     MODEL_NAME,
                "llm_model":       LLM_MODEL_ID,
            },
        }

    except Exception as e:
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))     

