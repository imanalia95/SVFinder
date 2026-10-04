# SVFinder — FYP Supervisor Recommendation System

An AI-assisted web application that helps FYP students discover suitable supervisors based on their project information.

## Overview

**SVFinder** is a FYP supervisor recommendation system that I developed to help students identify lecturers whose expertise and research interests are relevant to their projects with the help of AI.

The system combines **embedding-based retrieval** with an **LLM** to retrieve relevant lecturer profiles and generate recommendation context.

It was designed to **assist students**, while the final supervisor selection remains a human decision.

## How It Works

Project Title / Description
          ↓
     Text Processing
          ↓
     Query Embedding
          ↓
      ChromaDB
          ↓
 Relevant Lecturer Profiles
          ↓
     Qwen LLM
          ↓
   Top 3 Recommendations

## End-to-End System

I developed the system across the full pipeline:

* **Data Collection** — collected and prepared lecturer academic profile information
* **Data Processing** — cleaned and structured lecturer information for retrieval
* **AI / Retrieval** — implemented embedding-based lecturer profile retrieval using ChromaDB
* **LLM Integration** — integrated Qwen2.5-7B-Instruct to generate recommendation context
* **Backend** — developed the recommendation API using Python and FastAPI
* **Frontend** — developed the student-facing web application using Laravel, Blade, JavaScript and CSS
* **Database** — implemented MySQL for student accounts and system data
* **Authentication** — implemented student authentication using student's matrics
* **Evaluation** — tested recommendation performance, response time and usability

## Technology Stack

**Frontend**

* Laravel
* PHP / Blade
* JavaScript
* Vite
* CSS

**Backend & AI**

* Python
* FastAPI
* LangChain
* ChromaDB
* Sentence Transformers
* Hugging Face
* Qwen2.5-7B-Instruct

**Database & Tools**

* MySQL
* Selenium
* Pandas
* Jupyter Notebook
* Git / GitHub

## Results

### Recommendation Performance

* **20** ground-truth test cases
* **9/20** successful top-3 recommendations
* **45%** top-3 hit rate

The evaluation also found that **specific project titles generally produced better recommendations than broad titles**.

### Usability

* **30** evaluation participants
* **88.58 / 100** SUS score
* **19 participants** scored 90 or above

### Response Time

Tested recommendation requests remained within the project's target of **less than one minute**.

## Project Structure

SVFinder/
├── fyp-backend/
├── fyp-frontend/
├── data_preparation.py
├── prepare_data.py
├── rag_chain.py
├── vector_store.py
├── scrape_profile.py
├── scrape_links.py
├── testing.py
├── requirements.txt
├── Data Preparation.ipynb
└── SVFinder_User_Manual.pdf

## Future Improvements

* Improve lecturer profile data coverage and update
* Improve retrieval for broad or ambiguous project descriptions
* Expand the evaluation dataset
* Introduce clearer measurable recommendation criteria

## Live Demo

[▶️ Watch the SVFinder Demo](https://youtu.be/3Rcn3pZHBWc)

A short walkthrough demonstrating the end-to-end supervisor recommendation workflow.

## Academic Project

**SVFinder** was developed as a Final Year Project.

The project demonstrates end-to-end ownership of a web-based AI recommendation system, from **data collection and AI pipeline development to backend, frontend, database integration and evaluation**.
