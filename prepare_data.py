import pandas as pd
from config import OUTPUT_ALL_CSV

def quality_tier(total_words):
    if total_words == 0:    return "EMPTY"
    elif total_words < 100: return "VERY_SPARSE"
    elif total_words < 200: return "SPARSE"
    elif total_words < 400: return "MODERATE"
    else:                   return "RICH"


# =========================
# MAIN SCRIPT
# =========================

df = pd.read_csv(OUTPUT_ALL_CSV)

# make sure no NaN issues
df["profile"] = df["profile"].fillna("").astype(str)

# compute word count
df["total_words"] = df["profile"].apply(lambda x: len(x.split()))

# assign quality tier
df["quality_tier"] = df["total_words"].apply(quality_tier)

# overwrite the same file (or save new one if you prefer)
df.to_csv(OUTPUT_ALL_CSV, index=False)

print("Done. Quality tier added without rebuilding profiles.")