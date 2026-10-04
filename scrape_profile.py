import requests
import pandas as pd
import re
import time
import os
from config import INPUT_CSV

profile_urls = ["https://expert.unimas.my/profile/861",
    "https://expert.unimas.my/profile/K0537",
    "https://expert.unimas.my/profile/858",
    "https://expert.unimas.my/profile/1330",
    "https://expert.unimas.my/profile/1664",
    "https://expert.unimas.my/profile/491",
    "https://expert.unimas.my/profile/1122",
    "https://expert.unimas.my/profile/1150",
    "https://expert.unimas.my/profile/1089",
    "https://expert.unimas.my/profile/3159",
    "https://expert.unimas.my/profile/1379",
    "https://expert.unimas.my/profile/1652",
    "https://expert.unimas.my/profile/K0889",
    "https://expert.unimas.my/profile/685",
    "https://expert.unimas.my/profile/763",
    "https://expert.unimas.my/profile/507",
    "https://expert.unimas.my/profile/2108",
    "https://expert.unimas.my/profile/1336",
    "https://expert.unimas.my/profile/686",
    "https://expert.unimas.my/profile/1663",
    "https://expert.unimas.my/profile/315",
    "https://expert.unimas.my/profile/2157",
    "https://expert.unimas.my/profile/684",
    "https://expert.unimas.my/profile/374",
    "https://expert.unimas.my/profile/2115",
    "https://expert.unimas.my/profile/877",
    "https://expert.unimas.my/profile/835",
    "https://expert.unimas.my/profile/429",
    "https://expert.unimas.my/profile/1351",
    "https://expert.unimas.my/profile/704",
    "https://expert.unimas.my/profile/3036",
    "https://expert.unimas.my/profile/2848",
    "https://expert.unimas.my/profile/1372",
    "https://expert.unimas.my/profile/1550",
    "https://expert.unimas.my/profile/1357",
    "https://expert.unimas.my/profile/1105",
    "https://expert.unimas.my/profile/853",
    "https://expert.unimas.my/profile/1218",
    "https://expert.unimas.my/profile/K0788",
    "https://expert.unimas.my/profile/368",
    "https://expert.unimas.my/profile/1567",
    "https://expert.unimas.my/profile/1326",
    "https://expert.unimas.my/profile/2110",
    "https://expert.unimas.my/profile/2121",
    "https://expert.unimas.my/profile/1634",
    "https://expert.unimas.my/profile/K0856",
    "https://expert.unimas.my/profile/K0887",
    "https://expert.unimas.my/profile/931",
    "https://expert.unimas.my/profile/1648",
    "https://expert.unimas.my/profile/2147",
    "https://expert.unimas.my/profile/K0909",
    "https://expert.unimas.my/profile/1127",
    "https://expert.unimas.my/profile/1319",
    "https://expert.unimas.my/profile/665",
    "https://expert.unimas.my/profile/K0877",
    "https://expert.unimas.my/profile/1381",
    "https://expert.unimas.my/profile/1397",
    "https://expert.unimas.my/profile/1321",
    "https://expert.unimas.my/profile/846",
    "https://expert.unimas.my/profile/2109",
    "https://expert.unimas.my/profile/1331",
    "https://expert.unimas.my/profile/697",
    "https://expert.unimas.my/profile/1651",
    "https://expert.unimas.my/profile/K0872",
    "https://expert.unimas.my/profile/1373",
    "https://expert.unimas.my/profile/1380",
    "https://expert.unimas.my/profile/321",
    "https://expert.unimas.my/profile/1028",
    "https://expert.unimas.my/profile/800"
]

csv_file = INPUT_CSV

# Load existing CSV if it exists
if os.path.exists(csv_file):
    existing_df = pd.read_csv(csv_file)
    scraped_staff = set(existing_df["staff_no"].astype(str))
    all_lecturers = existing_df.to_dict("records")
    print(f"Loaded existing CSV with {len(scraped_staff)} lecturers")
else:
    scraped_staff = set()
    all_lecturers = []

failed_staff = []

pub_types = {
    "articles": "A",
    "proceedings": "P",
    "books": "B",
    "chapters": "C"
}

# Helpers
def get_image_url(staff_no):
    staff_no = str(staff_no).strip()
    return f"https://directory.unimas.my/assets/images/staffpic/{staff_no}.jpg"

def fetch_with_retry(url, retries=3, delay=2):
    for attempt in range(retries):
        try:
            response = requests.get(url, timeout=10)
            response.raise_for_status()
            return response
        except requests.exceptions.RequestException as e:
            print(f"Retry {attempt+1} failed:", e)
            time.sleep(delay)
    return None

def fetch_email(staff_no):
    """
    Extract email from IRIS API
    """
    try:
        url = f"https://research.unimas.my/iris7/rest/public/iapi/path/ecv-consultancy?staff_no={staff_no}&page=0&size=10"
        response = fetch_with_retry(url)

        if not response:
            return None

        data = response.json()

        person = data.get("person") or {}
        email = person.get("email")

        return email

    except Exception as e:
        print(f"Email fetch error for {staff_no}:", e)
        return None

# Scraping loop
for profile_url in profile_urls:

    staff_match = re.search(r"profile/([A-Za-z0-9]+)$", profile_url)
    if not staff_match:
        continue

    staff_no = staff_match.group(1)

    # Skip if already scraped
    if staff_no in scraped_staff:
        print("Skipping already scraped:", staff_no)
        continue

    try:
        print("Scraping:", staff_no)

        profile_api = f"https://research.unimas.my/iris7/rest/public/iapi/path/iris-profile?staff_no={staff_no}"
        response = fetch_with_retry(profile_api)

        if not response:
            failed_staff.append(staff_no)
            continue

        content = response.json().get("content", [{}])[0]

        name = content.get("SALUTATION", "") + " " + content.get("NAME", "")
        summary = content.get("PRO_PROFILE", "")

        email = fetch_email(staff_no)

        publications = {}

        for key, ptype in pub_types.items():

            pub_api = f"https://research.unimas.my/iris7/rest/public/iapi/path/ecv-pub?pub_type={ptype}&staff_no={staff_no}&page=0&size=50"

            response = fetch_with_retry(pub_api)

            if not response:
                publications[key] = []
                continue

            items = response.json().get("content", [])

            publications[key] = [i.get("TITLE", "") for i in items]

        image_url = get_image_url(staff_no)

        lecturer = {
            "staff_no": staff_no,
            "name": name.strip(),
            "email": email,
            "summary": summary,
            "image_url": image_url,
            "articles": "; ".join(publications["articles"]),
            "proceedings": "; ".join(publications["proceedings"]),
            "books": "; ".join(publications["books"]),
            "chapters": "; ".join(publications["chapters"]),
        }

        all_lecturers.append(lecturer)
        scraped_staff.add(staff_no)

        time.sleep(0.3)

    except Exception as e:
        print("Error:", e)
        failed_staff.append(staff_no)


# Save updated CSV
df = pd.DataFrame(all_lecturers)
df.to_csv(csv_file, index=False)

print("Dataset Created:", len(df), "lecturers saved")

# Save failed lecturers
if failed_staff:
    with open("failed_staff.txt", "w") as f:
        for s in failed_staff:
            f.write(s + "\n")

    print("Failed lecturers saved for retry")