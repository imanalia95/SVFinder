\# SVFinder — FYP Supervisor Recommendation System



> An AI-assisted Final Year Project (FYP) supervisor recommendation system that helps students discover suitable supervisors based on their project title, description, and uploaded project documents.



\## Overview



Choosing an appropriate FYP supervisor can be challenging for students when information about lecturers, research interests, and expertise is spread across different university sources.



\*\*SVFinder\*\* was developed as an AI-assisted recommendation system to help students identify potentially suitable FYP supervisors based on their project requirements.



The system combines \*\*embedding-based information retrieval\*\* with a \*\*Large Language Model (LLM)\*\* to retrieve relevant lecturer profiles and generate contextual explanations for the recommendations.



The system is designed to \*\*assist students in exploring suitable supervisors, not replace human decision-making or automatically assign supervisors.\*\*



\---



\## Key Features



\* 🔎 Search for suitable FYP supervisors using a project title and description

\* 📄 Upload a project document as an additional source of project information

\* 🧠 Embedding-based retrieval of relevant lecturer profiles

\* 🤖 LLM-generated recommendation reasoning and context

\* 👨‍🏫 Display lecturer research interests, expertise, and profile information

\* 🔗 Access lecturer Google Scholar and academic profile information

\* 📧 Immediately contact the lecturer via email using the Student's FYP Project title or description

\* 🔐 Student authentication using student official matric as registered student in the uni

\* 📱 Web-based interface for accessing recommendations



\---



\## How It Works



SVFinder uses a retrieval and generation pipeline to process a student's project information.



```text

Student Input

&#x20;    │

&#x20;    ├── Project Title

&#x20;    ├── Project Description

&#x20;    └── Uploaded Document

&#x20;           │

&#x20;           ▼

&#x20;    Text Processing

&#x20;           │

&#x20;           ▼

&#x20;    Query Embedding

&#x20;           │

&#x20;           ▼

&#x20;  Vector Database Retrieval

&#x20;           │

&#x20;           ▼

&#x20;  Relevant Lecturer Profiles

&#x20;           │

&#x20;           ▼

&#x20;    LLM Processing

&#x20;           │

&#x20;           ▼

&#x20; Recommendation Explanation

&#x20;           │

&#x20;           ▼

&#x20;      Top 3 Lecturers

```



\### Recommendation Pipeline



\*\*1. Project information\*\*



Students provide a project title and description, with the option to upload a project document.



\*\*2. Text processing\*\*



The system extracts and prepares relevant project information to form the retrieval query.



\*\*3. Embedding\*\*



The project query is converted into a vector representation using a sentence-transformer embedding model.



\*\*4. Retrieval\*\*



The vector representation is compared against lecturer profile information stored in a Chroma vector database.



\*\*5. LLM processing\*\*



The retrieved lecturer information is provided to the LLM to generate contextual recommendation output.



\*\*6. Recommendation\*\*



The system presents the top recommended lecturers together with relevant profile information and supporting context.



\---



\## AI Architecture



SVFinder does not rely on the LLM alone to search through all lecturer profiles.



Instead, the system separates \*\*retrieval\*\* from \*\*LLM reasoning\*\*:



```text

&#x20;                ┌─────────────────────┐

&#x20;                │ Student Project     │

&#x20;                │ Information         │

&#x20;                └──────────┬──────────┘

&#x20;                           │

&#x20;                           ▼

&#x20;                ┌─────────────────────┐

&#x20;                │ Embedding Model     │

&#x20;                │ all-mpnet-base-v2   │

&#x20;                └──────────┬──────────┘

&#x20;                           │

&#x20;                           ▼

&#x20;                ┌─────────────────────┐

&#x20;                │ Chroma Vector DB    │

&#x20;                │ Lecturer Profiles   │

&#x20;                └──────────┬──────────┘

&#x20;                           │

&#x20;                     Top Relevant

&#x20;                      Profiles

&#x20;                           │

&#x20;                           ▼

&#x20;                ┌─────────────────────┐

&#x20;                │ Qwen2.5-7B-Instruct │

&#x20;                │ LLM                 │

&#x20;                └──────────┬──────────┘

&#x20;                           │

&#x20;                           ▼

&#x20;                ┌─────────────────────┐

&#x20;                │ Recommendation      │

&#x20;                │ Context / Reasoning │

&#x20;                └─────────────────────┘

```



This architecture allows the system to retrieve relevant lecturer information first and then use the LLM to process the retrieved context.



\---



\## Technology Stack



\### Frontend



\* Laravel

\* PHP

\* Blade

\* HTML / CSS

\* JavaScript

\* Vite

\* MySQL



\### Backend



\* Python

\* FastAPI

\* LangChain

\* ChromaDB

\* Sentence Transformers

\* Hugging Face



\### AI / Machine Learning



\* \*\*Embedding model:\*\* `sentence-transformers/all-mpnet-base-v2`

\* \*\*LLM:\*\* `Qwen/Qwen2.5-7B-Instruct`

\* Vector-based lecturer profile retrieval

\* Retrieval-Augmented Generation (RAG) pipeline



\### Development Tools



\* Git / GitHub

\* VS Code

\* Jupyter Notebook

\* Selenium



\---



\## Data Preparation



Lecturer information was collected from publicly available university academic sources and prepared for use in the recommendation pipeline.



The data preparation process included:



1\. Collecting lecturer profile information

2\. Extracting research-related information

3\. Cleaning and preparing profile text

4\. Combining relevant lecturer information

5\. Preparing the text for embedding

6\. Storing the resulting representations in the vector database



The original collected datasets and generated vector database are \*\*not included in this public repository\*\*.



This keeps the repository focused on the implementation while keeping the lecturer's dataset confidential. 



\---



\## System Evaluation



SVFinder was evaluated using both recommendation accuracy and usability measurements.



\### Recommendation Evaluation



A set of \*\*20 ground-truth test cases\*\* was used to evaluate the recommendation results.



| Metric                 |  Result |

| ---------------------- | ------: |

| Test cases             |      20 |

| Top-3 successful cases |       9 |

| Top-3 hit rate         | \*\*45%\*\* |



The results indicate that the system was able to retrieve an expected supervisor within the top three recommendations for 9 out of 20 evaluated cases.



\### Usability Evaluation



The System Usability Scale (SUS) was used to evaluate the usability of the system.



| Metric       |          Result |

| ------------ | --------------: |

| Participants |              30 |

| SUS Score    | \*\*88.58 / 100\*\* |

| Scores ≥ 90  | 19 participants |



The SUS result indicates a strong overall usability perception among the evaluation participants.



\### Response Time



The recommendation process was also tested for response time.



Observed response times included:



\* 29.00 seconds

\* 20.89 seconds

\* 19.88 seconds

\* 8.47 seconds

\* 5.51 seconds



The tested recommendation requests remained within the project's target of \*\*less than one minute\*\*.



\---



\## Project Structure



```text

SVFinder/

│

├── fyp-backend/

│   └── main.py

│

├── fyp-frontend/

│   ├── app/

│   ├── bootstrap/

│   ├── config/

│   ├── database/

│   ├── resources/

│   ├── routes/

│   └── ...

│

├── data\_preparation.py

├── prepare\_data.py

├── rag\_chain.py

├── vector\_store.py

├── scrape\_profile.py

├── scrape\_links.py

├── lecture\_profile\_scrape.py

├── all\_lecturer\_profile\_links.py

├── testing.py

├── requirements.txt

├── Data Preparation.ipynb

├── SVFinder\_User\_Manual.pdf

├── .gitignore

└── README.md

```



\---



\## Running the Project



\### Prerequisites



Make sure the following are installed:



\* Python 3.11+

\* PHP

\* Composer

\* Node.js and npm

\* MySQL

\* Git



\### Backend



Navigate to the backend directory:



```bash

cd fyp-backend

```



Create and activate a Python virtual environment:



```bash

python -m venv .venv

```



Activate it on Windows:



```bash

.venv\\Scripts\\activate

```



Install the Python dependencies:



```bash

pip install -r ../requirements.txt

```



Create the required environment variables based on your local configuration.



Then start the FastAPI server:



```bash

uvicorn main:app --reload --port 8000

```



The backend will run locally on:



```text

http://127.0.0.1:8000

```



\### Frontend



Open another terminal and navigate to:



```bash

cd fyp-frontend

```



Install PHP dependencies:



```bash

composer install

```



Install JavaScript dependencies:



```bash

npm install

```



Configure the Laravel environment:



```bash

copy .env.example .env

```



Generate the application key:



```bash

php artisan key:generate

```



Configure the database in `.env`, then run:



```bash

php artisan migrate

```



Start the Laravel development server:



```bash

php artisan serve

```



The frontend will then be available through the local Laravel server.



> The exact environment configuration may need to be adjusted depending on the local database and backend API setup.



\---



\## Screenshots



Screenshots of the completed system can be added here to provide a quick visual overview.



\### Landing Page



\*Add screenshot here.\*



\### Project Input



\*Add screenshot here.\*



\### Recommendation Results



\*Add screenshot here.\*



\### Lecturer Information



\*Add screenshot here.\*



\---



\## Design Approach



The system was developed using the \*\*Rapid Application Development (RAD)\*\* methodology.



The development process focused on iterative development, testing, and refinement of the recommendation system and user interface based on feedback.



The system was designed around the following principle:



> \*\*AI assists students in discovering potentially suitable supervisors while the final supervisor selection remains a human decision.\*\*



\---



\## Limitations



The current system has several limitations:



\* Recommendation quality depends on the available lecturer profile information.

\* The recommendation model does not guarantee that a lecturer will accept a student.

\* The system does not automatically assign supervisors.

\* The evaluation dataset was limited to 20 ground-truth test cases.

\* Retrieval performance varies with project title specificity, with specific titles generally producing more relevant recommendations than broad titles.

\* Lecturer information may require periodic updating as research interests and academic profiles change.



\---



\## Future Improvements



Potential improvements include:



\* Improving lecturer profile data coverage and update

\* Introducing measurable criteria for recommendation refinement

\* Improving retrieval performance for ambiguous project descriptions

\* Expanding the recommendation evaluation dataset

\* Exploring alternative embedding models



\---



\## Academic Project



SVFinder was developed as a Final Year Project in the field of \*\*Multimedia Computing / Computer Science\*\*.



The project explores the application of \*\*Large Language Models, vector retrieval, and web-based systems\*\* to support the FYP supervisor selection process.



\---



\## Author



\*\*Nur Iman Alia\*\*



Final Year Project — SVFinder



UNIMAS



