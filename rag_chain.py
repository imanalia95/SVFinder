import os
import re
import json
import chromadb
from collections import defaultdict
from langchain_huggingface import ChatHuggingFace, HuggingFaceEndpoint
from langchain_core.prompts import PromptTemplate
from langchain_core.output_parsers import StrOutputParser
from sentence_transformers import SentenceTransformer
from config import (
    HF_API_KEY,
    MODEL_NAME,
    LLM_MODEL_ID,
    LLM_MAX_TOKENS,
    LLM_TEMPERATURE,
    TOP_N,
    CHROMA_PATH,
    SIM_RELEVANT,
    SIM_GOOD,
)

# Prompts
REASONING_PROMPT_TEMPLATE = """<|im_start|>system
You are an academic advisor at FCSIT UNIMAS helping students find the most suitable FYP supervisor. Your job is to explain clearly why each lecturer is a good match based strictly on their profile. Do not invent or assume expertise not mentioned in the profile.
<|im_end|>
<|im_start|>user
A student has already decided on their FYP topic and is looking for the most suitable supervisor:

TITLE: {title}
DESCRIPTION: {description}

The {n} most relevant lecturers retrieved are shown below.

{context}

For each lecturer, write a clear and specific explanation of WHY they are a good match for this student's research interest. Base your reasoning ONLY on the profile information provided — do not invent publications or expertise not mentioned.

Be specific — mention actual research areas or publications from their profile when explaining the match. Avoid vague or generic statements.

Structure your response exactly like this for each lecturer:

**[Rank] [Lecturer Name]**
Similarity score: [score] ([match quality])
-Match reasoning: [2-3 sentences explaining the specific overlap between the student's interest and this lecturer's expertise]
-Suggested FYP angle: [one concrete and specific FYP direction the student could pursue with this supervisor]

<|im_end|>
<|im_start|>assistant
"""

REASONING_PROMPT = PromptTemplate(
    input_variables=["title", "description", "context", "n"],
    template=REASONING_PROMPT_TEMPLATE,
)

# Build RAG
def build_rag_chain() -> dict:
    if HF_API_KEY and not HF_API_KEY.startswith("hf_xxx"):
        os.environ["HUGGING_FACE_HUB_TOKEN"] = HF_API_KEY
    else:
        raise EnvironmentError(
            "HF_API_KEY is not set in your .env file.\n"
            "Get a free token at https://huggingface.co/settings/tokens\n"
            "Then add it to your .env as: HF_API_KEY=hf_..."
        )
    
    # Connect to chromaDB
    client     = chromadb.PersistentClient(path=CHROMA_PATH)
    collection = client.get_collection("lecturers_chunked")
    print(f"ChromaDB loaded: {collection.count()} lecturers")

    # Load embedding model
    embed_model = SentenceTransformer(MODEL_NAME, use_auth_token=HF_API_KEY)
    print(f"Embedding model loaded: {MODEL_NAME}")

    # LLM setup
    llm_endpoint = HuggingFaceEndpoint(
        repo_id=LLM_MODEL_ID,
        task="text-generation", 
        huggingfacehub_api_token=HF_API_KEY,
        max_new_tokens=LLM_MAX_TOKENS,
        temperature=LLM_TEMPERATURE,
        repetition_penalty=1.1,
        return_full_text=False,
        do_sample=True,
    )   

    llm = ChatHuggingFace(llm=llm_endpoint)
    print(f"LLM loaded: {LLM_MODEL_ID}")

    return {
        "reasoning_chain": REASONING_PROMPT | llm | StrOutputParser(),
        "collection":      collection,
        "embed_model":     embed_model, 
    }

# Retrieval 

def retrieve_candidates(collection, embed_model, query: str, top_n: int = TOP_N) -> list[dict]:

    q_vec = embed_model.encode([query], normalize_embeddings=True).tolist()

    # Fetch more chunks than needed so grouping by lecturer has enough data
    results = collection.query(
        query_embeddings=q_vec,
        n_results=top_n * 10
    )

    # Group chunks by lecturer 
    grouped = defaultdict(list)
    for meta, dist, doc in zip(results["metadatas"][0], results["distances"][0], results["documents"][0]):
        staff_no = meta.get("staff_no")

        if staff_no:
            grouped[staff_no].append((meta, dist, doc))

    # Build ONE candidate per lecturer 
    candidates = []
    for staff_no, chunks in grouped.items():
        best_meta, best_dist, _= min(chunks, key=lambda x: x[1])

        # Cosine space: distance is in [0, 2], convert to similarity [−1, 1
        similarity = round(1 - (best_dist / 2), 4)

        # Separate summary and publication chunks
        summary_text = ""
        publication_titles = []

        for meta, dist, doc in chunks:
            chunk_id = meta.get("chunk_id", "")
            if "summary" in chunk_id:
                summary_text = doc
            elif "chunk_" in chunk_id:
                # Extract publication titles from doc text
                publication_titles.append(doc)

        candidates.append({
            "name": best_meta.get("name", "Unknown"),
            "staff_no": staff_no,
            "similarity": similarity,
            "summary": summary_text or best_meta.get("summary", ""),
            "publications": publication_titles,  # all pub chunks
            "email": best_meta.get("email", ""),
            "google_scholar": best_meta.get("google_scholar", ""),
            "img_url": best_meta.get("img_url", ""),
            "profile_url":     best_meta.get("profile_url", ""),
            "num_articles":   best_meta.get("num_articles", 0),
            "num_proceedings": best_meta.get("num_proceedings", 0),
            "is_relevant": similarity >= SIM_RELEVANT,
            "is_strong": similarity >= SIM_GOOD,
        })

    # Sort by similarity, assign rank, return top N
    candidates.sort(key=lambda x: x["similarity"], reverse=True)
    for rank, c in enumerate(candidates, start=1):
        c["rank"] = rank

    return candidates[:top_n]

# Format helpers

def format_context(candidates: list[dict]) -> str:
    blocks = []
    for c in candidates:
        sim_label = (
            "strong match" if c["is_strong"]
            else "relevant" if c["is_relevant"]
            else "weak match"
        )

        # Flatten all publication chunks into one list
        all_pubs = []
        for pub_chunk in c.get("publications", []):
            # Each chunk is a string like "Ts. Dr X published: title1; title2; ..."
            if "published:" in pub_chunk:
                titles = pub_chunk.split("published:")[-1].strip()
                all_pubs.extend([t.strip() for t in titles.split(";") if t.strip()])

        pub_text = "\n".join(f"  - {p}" for p in all_pubs) if all_pubs else "  No publications listed."


        blocks.append(
            f"---\n"
            f"LECTURER #{c['rank']}: {c['name']}\n"
            f"Similarity: {c['similarity']} ({sim_label})\n"
            f"Publications: {c['num_articles']} articles, {c['num_proceedings']} proceedings\n"
            f"\nBackground:\n{c['summary'] or 'No summary available.'}\n"
            f"\nPublications:\n{pub_text}\n"
            f"---"
        )
    return "\n\n".join(blocks)

# Main 
def run_rag(rag: dict, title: str, description: str, top_n: int = TOP_N) -> dict:
    
    query = f"{title}. {description}"

    # Retrieve top N candidates
    print(f"Retrieving top-{top_n} candidates from ChromaDB...")
    candidates = retrieve_candidates(
        rag["collection"], 
        rag["embed_model"],
        query, 
        top_n=top_n
    )

    # Format context and run reasoning
    context = format_context(candidates)

    # LLM reasons the final top-N
    print(f"LLM generating reasoning for top {top_n} candidates...")

    reasoning = rag["reasoning_chain"].invoke({
        "title":       title,
        "description": description,
        "context":     context,
        "n":           len(candidates),
    })

    return {
        "candidates":   candidates, 
        "context":      context,
        "reasoning":    reasoning.strip(),
    }


# Quick test
if __name__ == "__main__":
    print("=" * 65)
    print("  RAG CHAIN — retrieve → rerank → reason test")
    print("=" * 65)

    rag = build_rag_chain()

    test_cases = [
        {
            "title":       "I want to do something with AI and sign language detection",
            "description": "I am interested in using artificial intelligence to detection sign language gesture.",
        }
    ]

    for tc in test_cases:
        print(f"\n{'─' * 65}")
        print(f"Title      : {tc['title']}")
        print(f"Description: {tc['description']}")
        print(f"{'─' * 65}")

        result = run_rag(rag, title=tc["title"], description=tc["description"])

        print(f"\nTop {TOP_N} candidates (by cosine similarity):")
        for c in result["candidates"]:
            tag = "STRONG" if c["is_strong"] else ("relevant" if c["is_relevant"] else "weak")
            print(f"  #{c['rank']:2d} [{c['similarity']:.4f} | {tag:8s}] {c['name']}")

        print("\nLLM Reasoning:")
        print(result["reasoning"])
        print()