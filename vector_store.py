import pandas as pd
import chromadb
from config import CHROMA_PATH, embedding_fn

df = pd.read_csv('lecturers_clean_2.csv')
df['profile'] = df['profile'].fillna('')

print(f"Loaded {len(df)} lecturers")

# Connect to ChromaDB
client = chromadb.PersistentClient(path=CHROMA_PATH)
try:
    client.delete_collection("lecturers")
except:
    pass

collection = client.create_collection(
    name="lecturers",
    embedding_function=embedding_fn,
    metadata={"hnsw:space": "cosine"},
)

def safe_str(val):
    """Convert NaN/None to empty string for ChromaDB metadata."""
    if pd.isna(val):
        return ""
    return str(val)

def safe_int(val):
    try:
        return int(val)
    except:
        return 0
    
# Dataset Preparation

ids       = [str(i) for i in range(len(df))]
documents = df['profile'].tolist()

metadatas = []
for _, row in df.iterrows():
    # Parse top articles (stored as a string list)
    try:
        articles_raw = safe_str(row.get('articles', ''))
        top_articles = '; '.join(
            [t.strip() for t in articles_raw.split(';') if t.strip()][:5]
        )
    except:
        top_articles = ""

    metadatas.append({
        "name":            safe_str(row['name']),
        "summary":         safe_str(row['summary'])[:500],  # ChromaDB metadata limit
        "num_articles":    safe_int(row.get('num_articles', 0)),
        "num_proceedings": safe_int(row.get('num_proceedings', 0)),
        "top_articles":    top_articles,
        "google_scholar":  safe_str(row.get('google_scholar', '')),
        "img_url":         safe_str(row.get('img_url', '')),
        "email":           safe_str(row.get('email', ''))
    })

collection.add(
    ids=ids,
    documents=documents,
    metadatas=metadatas,
)

count = collection.count()
print(f"Inserted {count} lecturers into ChromaDB")
print("Saved to: ./chroma_db/")

# Quick test
test_query = "deep learning for image classification"

results = collection.query(
    query_texts=[test_query],
    n_results=3
)

print(f"\n--- Test Query: '{test_query}' ---")
for i, meta in enumerate(results['metadatas'][0]):
    dist = results['distances'][0][i]
    print(f"  #{i+1} [{1-dist:.4f} sim] {meta['name']}")
    print(f"   Image URL: {meta.get('img_url', 'N/A')}")
    print(f"       {meta['summary'][:100]}...")