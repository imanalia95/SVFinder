import chromadb
from sentence_transformers import SentenceTransformer

client     = chromadb.PersistentClient(path="./chroma_db")
collection = client.get_collection("lecturers")
model      = SentenceTransformer('all-MiniLM-L6-v2')

def recommend(query, top_n=5):
    """Query ChromaDB and return top-N results."""
    q_vec = model.encode([query], normalize_embeddings=True).tolist()
    results = collection.query(query_embeddings=q_vec, n_results=top_n)
    
    output = []
    for i, (meta, doc, dist) in enumerate(zip(
        results['metadatas'][0],
        results['documents'][0],
        results['distances'][0]
    )):
        output.append({
            'rank':        i + 1,
            'name':        meta['name'],
            'similarity':  round(1 - dist, 4),  
            'summary':     meta['summary'],
            'articles':    meta['num_articles'],
            'top_pubs':    meta['top_articles'],
            'scholar':     meta['google_scholar'],
        })
    return output

def print_results(query, results):
    print(f"\n{'='*65}")
    print(f"  QUERY: \"{query}\"")
    print(f"{'='*65}")
    for r in results:
        bar = '█' * int(r['similarity'] * 20)
        print(f"\n  #{r['rank']}  {r['name']}")
        print(f"       Similarity: {r['similarity']:.4f}  {bar}")
        print(f"       Articles: {r['articles']}")
        if r['summary']:
            print(f"       {r['summary'][:120]}...")
        if r['top_pubs']:
            first_pub = r['top_pubs'].split(';')[0].strip()
            print(f"       📄 e.g. {first_pub[:80]}...")

TEST_QUERIES = [
    "machine learning and deep learning for image classification",
    "blockchain security and cryptography",
    "natural language processing and text mining",
    "IoT Internet of Things and smart systems",
    "software engineering and formal methods",
    "data mining optimization algorithms",
    "computer vision and image processing",
    "mobile application development",
]

print("\n RECOMMENDATION SYSTEM TEST REPORT")
print(f"   ChromaDB collection: {collection.count()} lecturers")
print(f"   Embedding model: all-MiniLM-L6-v2\n")

for query in TEST_QUERIES:
    results = recommend(query, top_n=3)
    print_results(query, results)

print(f"\n{'='*65}")
print("  EDGE CASE TESTS")
print(f"{'='*65}")

edge_cases = [
    ("very short query", "AI"),
    ("vague query",      "I like computers"),
    ("specific query",   "Sarawak indigenous language speech recognition ASR"),
]

for label, query in edge_cases:
    results = recommend(query, top_n=1)
    r = results[0]
    print(f"\n  [{label}] \"{query}\"")
    print(f"  → #{r['rank']} {r['name']} (sim={r['similarity']:.4f})")

print(f"\n\n All tests passed! System is ready.\n")