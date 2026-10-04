from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import pandas as pd

driver = webdriver.Chrome()

try:
    driver.get("https://directory.unimas.my/directory/all/all/F08-001")

    # wait until Angular cards load
    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.CLASS_NAME, "custom-card"))
    )

    links = driver.find_elements(By.TAG_NAME, "a")

    profile_urls = set()

    for link in links:
        href = link.get_attribute("href")
        if href and "expert.unimas.my/profile/" in href:
            profile_urls.add(href)

    driver.quit()

    # print row-by-row
    print(f"Total expert profiles: {len(profile_urls)}\n")
    for url in profile_urls:
        print(url)

except Exception as e:
    print("Error:", e)
    driver.quit()

df = pd.DataFrame(profile_urls, columns=["profile_url"])
df.to_csv("expert_links.csv", index=False)