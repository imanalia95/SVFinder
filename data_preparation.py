import re
import pandas as pd
import chromadb
from config import CHROMA_PATH
import time
from sentence_transformers import SentenceTransformer

# Load data 
df = pd.read_csv("lecturers_clean_2.csv")

# Model 
MODEL_NAME = 'all-mpnet-base-v2'
print(f"Loading model: {MODEL_NAME}")
model = SentenceTransformer(MODEL_NAME)
print("Model loaded")

# DB
client = chromadb.PersistentClient(path=CHROMA_PATH)

try:
    client.delete_collection("lecturers_chunked")
except:
    pass

collection = client.create_collection(
    name="lecturers_chunked",
    metadata={"hnsw:space": "cosine"}
)

# Helpers 
def clean(text):
    if pd.isna(text):
        return ""
    return re.sub(r'\s+', ' ', str(text)).strip()

keywords = ["research", "interest", "expertise", "focus", "speciali", "skill"]
pattern = re.compile(
    r'\b(' + '|'.join(keywords) + r')\w*\b',
    re.IGNORECASE
)

def extract_research_sentences(text):
    if pd.isna(text):
        return ""

    text = str(text)
    sentences = re.split(r'(?<=[.!?])\s+', text)
    selected = [s for s in sentences if pattern.search(s)]
    return " ".join(selected)

def chunk_list(items, chunk_size=5):
    return [items[i:i+chunk_size] for i in range(0, len(items), chunk_size)]

# Build data 
documents = []
metadatas = []
ids = []

for _, row in df.iterrows():
    staff_id = str(row["staff_no"])
    name = row["name"]

    # Summary 
    summary = clean(extract_research_sentences(row.get("summary", "")))

    if summary:
        documents.append(f"{name} expertise: {summary}")
        metadatas.append({
            "name": name,
            "staff_no": staff_id,
            "summary": row.get("summary", ""),  
            "type": "summary",
            "num_articles": int(row.get("num_articles", 0) or 0),
            "num_proceedings": int(row.get("num_proceedings", 0) or 0),
            "email": row.get("email"),
            "google_scholar": row.get("google_scholar"),
            "img_url": row.get("img_url"),
            "profile_url": row.get("profile_url")
        })
        ids.append(f"{staff_id}_summary")

    # Publications 
    titles_all = []

    for col in ['articles', 'proceedings']:
        text = clean(row.get(col, ''))
        if text:
            titles = [t.strip() for t in text.split(";") if t.strip()]
            titles_all.extend(titles)

    titles_all = list(dict.fromkeys(titles_all))

    chunks = chunk_list(titles_all, chunk_size=5)

    for i, chunk in enumerate(chunks):
        documents.append(f"{name} published: " + "; ".join(chunk))
        metadatas.append({
            "name": name,
            "staff_no": staff_id,
            "summary": row.get("summary", ""),
            "type": "publication_chunk",
            "num_articles": int(row.get("num_articles", 0) or 0),
            "num_proceedings": int(row.get("num_proceedings", 0) or 0),
            "email": row.get("email"),
            "google_scholar": row.get("google_scholar"),
            "img_url": row.get("img_url"),
            "profile_url": row.get("profile_url")
        })
        ids.append(f"{staff_id}_chunk_{i}")

# Embed FIRST 
start = time.time()
embeddings = model.encode(documents, normalize_embeddings=True)
elapsed = time.time() - start

print(f"\n✅ Done! {len(embeddings)} embeddings in {elapsed:.1f}s")

# Add to DB 
collection.add(
    documents=documents,
    embeddings=embeddings.tolist(),
    metadatas=metadatas,
    ids=ids
)

# Test query 
test_query = "Sign language detection"
query_embedding = model.encode([test_query], normalize_embeddings=True)

results = collection.query(
    query_embeddings=query_embedding.tolist(),
    n_results=3
)


for i in range(len(results["documents"][0])):
    print(f"\nResult {i+1}")
    print("Document:", results["documents"][0][i])
    print("Metadata:", results["metadatas"][0][i])
    print("Cosine Distance:", results["distances"][0][i])
    print("-" * 50)