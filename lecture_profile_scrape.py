import re
import pandas as pd
import requests
import time

# -------- Step 1: Profile URLs to re-scrape --------
profile_urls = [
    "https://expert.unimas.my/profile/K0889",
    "https://expert.unimas.my/profile/507",
    "https://expert.unimas.my/profile/K0856",
    "https://expert.unimas.my/profile/K0909",
    "https://expert.unimas.my/profile/K0872",
    "https://expert.unimas.my/profile/1664",
    "https://expert.unimas.my/profile/1122",
    "https://expert.unimas.my/profile/429",
    "https://expert.unimas.my/profile/K0887",
    "https://expert.unimas.my/profile/1127",
    "https://expert.unimas.my/profile/1319",
    "https://expert.unimas.my/profile/1331"
]

# -------- Step 2: Load existing CSV --------
df = pd.read_csv("all_lecturers.csv")

# -------- Step 3: Extract staff_no --------
staff_list = []

for url in profile_urls:
    match = re.search(r"profile/([A-Za-z0-9]+)$", url)
    if match:
        staff_list.append(match.group(1))
    else:
        print(f"Invalid URL: {url}")

print(f"Will update {len(staff_list)} lecturers")

# -------- Helper: fetch publications (single page only, simple) --------
def fetch_titles(staff_no, pub_type):
    url = f"https://research.unimas.my/iris7/rest/public/iapi/path/ecv-pub?pub_type={pub_type}&staff_no={staff_no}&page=0&size=50"
    
    try:
        res = requests.get(url, timeout=10)
        if res.status_code != 200:
            return []

        items = res.json().get("content", [])
        return [i.get("TITLE", "") for i in items if i.get("TITLE")]

    except:
        return []

# -------- Helper: count using ';' --------
def count_items(text):
    if pd.isna(text) or text.strip() == "":
        return 0
    return len([t for t in text.split(";") if t.strip() != ""])

# -------- Step 4: Update lecturers --------
for staff_no in staff_list:
    try:
        match_rows = df[df["staff_no"] == staff_no]

        if match_rows.empty:
            print(f"Not found in CSV: {staff_no}")
            continue

        index = match_rows.index[0]

        print(f"Updating publications for: {staff_no}")

        # ---- Fetch publications ----
        articles = fetch_titles(staff_no, "A")
        proceedings = fetch_titles(staff_no, "P")
        books = fetch_titles(staff_no, "B")
        chapters = fetch_titles(staff_no, "C")

        # ---- Convert to string ----
        articles_str = "; ".join(articles)
        proceedings_str = "; ".join(proceedings)
        books_str = "; ".join(books)
        chapters_str = "; ".join(chapters)

        # ---- Update dataframe ----
        df.at[index, "articles"] = articles_str
        df.at[index, "proceedings"] = proceedings_str
        df.at[index, "books"] = books_str
        df.at[index, "chapters"] = chapters_str

        # ---- Auto count ----
        df.at[index, "num_articles"] = count_items(articles_str)
        df.at[index, "num_proceedings"] = count_items(proceedings_str)
        df.at[index, "num_books"] = count_items(books_str)
        df.at[index, "num_chapters"] = count_items(chapters_str)

        print(f"Updated: {df.at[index, 'name']}")

        time.sleep(0.3)

    except Exception as e:
        print(f"Error with {staff_no}: {e}")

# -------- Step 5: Save --------
df.to_csv("all_lecturers.csv", index=False)

print("Done updating publications + counts!")