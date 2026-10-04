import os
from pathlib import Path
from dotenv import load_dotenv
from chromadb.utils.embedding_functions import SentenceTransformerEmbeddingFunction

load_dotenv(Path(__file__).parent / ".env")

# HuggingFace Authentication
HF_API_KEY = os.getenv("HF_TEMP_KEY", "")

# Embedding model and function
HF_MODEL = os.getenv("HF_MODEL", "")
MODEL_NAME = "all-mpnet-base-v2"

embedding_fn = SentenceTransformerEmbeddingFunction(
    model_name=MODEL_NAME
)

# LLM for reasoning (HuggingFace Serverless Inference API — no local GPU)
# Uses HF_API_KEY to call the model remotely via HuggingFaceEndpoint.
LLM_MODEL_ID = os.getenv("LLM_MODEL_ID", "Qwen/Qwen2.5-7B-Instruct")
LLM_MAX_TOKENS = int(os.getenv("LLM_MAX_TOKENS", 1024 ))
LLM_TEMPERATURE = float(os.getenv("LLM_TEMPERATURE", 0.2))

# Retrieval & reranking
TOP_N = int(os.getenv("TOP_N", 3)) # Final results shown

CHROMA_PATH = os.getenv("CHROMA_PATH", "./chroma_db")

# Files
INPUT_CSV = "all_lecturers.csv"
OUTPUT_ALL_CSV = "lecturers_clean_2.csv"
EMBEDDINGS_FILE  = "embeddings.npy"

# Similarity thresholds
SIM_RELEVANT = 0.40
SIM_GOOD     = 0.55

if __name__ == "__main__":
    key_status = "SET" if HF_API_KEY and not HF_API_KEY.startswith("hf_xxx") else "NOT SET"
    print("Config loaded:")
    print(f"  HF_API_KEY     : {key_status}")
    print(f"  MODEL_NAME     : {MODEL_NAME}")
    print(f"  LLM_MODEL_ID   : {LLM_MODEL_ID}")
    print(f"  LLM_MAX_TOKENS : {LLM_MAX_TOKENS}")
    print(f"  LLM_TEMPERATURE: {LLM_TEMPERATURE}")
    print(f"  TOP_N          : {TOP_N}")
    print(f"  CHROMA_PATH    : {CHROMA_PATH}")
    print(f"  SIM thresholds : relevant>={SIM_RELEVANT}  good>={SIM_GOOD}")