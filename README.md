# 🗺️ Job Hunter 2024- Intelligent Job Board
  <p>
    <a href="https://www.python.org/" target="_blank"><img src="https://img.shields.io/badge/python-3.9-blue" alt="Python Version"></a>
    <a href="https://www.php.net/" target="_blank"><img src="https://img.shields.io/badge/php-8.0-blue" alt="PHP Version"></a>
    <a href="https://reactjs.org/" target="_blank"><img src="https://img.shields.io/badge/react-18.2.0-blue" alt="React Version"></a>
    <a href="https://www.typescriptlang.org/" target="_blank"><img src="https://img.shields.io/badge/typescript-4.9-blue" alt="TypeScript"></a>
    <a href="https://nodejs.org/" target="_blank"><img src="https://img.shields.io/badge/node.js-18.16.0-green" alt="Node.js"></a>
    <a href="https://laravel.com/" target="_blank"><img src="https://img.shields.io/badge/laravel-8.0-red" alt="Laravel Version"></a>

  </p>

  🚀 **JobHunter** is intelligent platform for viewing jobs in Romania and IT events, integrating:  
- 🏯️ **Interactive maps** with location clusters  
- 📌 **Semantic ontology** for classifying jobs and events  
- 🤖 **AI-based recommendation system** based on user preferences  
- 📊 **Event statistics** and real-time insights  
- 🔍 **Advanced job & event search**  

## About the Project

This **#project** is part of a larger collaborative initiative that we built together with our colleagues for the **#Wade** class at Alexandru Ioan Cuza University of Iași (**#infoiasi**). As part of our **#Web Development** course, we combined our skills and ideas to create a cutting-edge **#web** application that addresses real-world challenges in the job listing domain. This endeavor not only highlights our technical expertise but also our commitment to teamwork and innovation.

## 📂 Resources (Deliverables)

- 📄 [Technical Report](https://github.com/JobHunter2024/BetaTeam/tree/main/Documentation)  
- 📜 [OpenAPI Specification Directory](https://github.com/JobHunter2024/BetaTeam/tree/main/openAPIspecifications)
- 🎥 [Demo Video](https://drive.google.com/file/d/1TtBJh8zh-k3DXM9MzoIYm6JunJv8VYH9/view?usp=sharing)

## 👥 Members

- **Ciobanu Ana**
- **Roca Maria-Magdalena**

## 🙌 Contributors ( Job Hunter 2024 - Team Gamma) **#JobHunter**

- **Harton Amalia**
- **Habasescu Andrei**

## 📸 Screenshot
<div style="text-align: left;"> 
    <img src="https://github.com/JobHunter2024/BetaTeam/blob/main/Documentation/map.png" alt="screenshot" style="width: 50%; max-width: 600px; height: auto;"/>
<p>This is a preview of the interactive map displaying job/event clusters in **#JobHunter** .</p>
    <p> <img src="https://github.com/JobHunter2024/BetaTeam/blob/main/Documentation/documentation_images/pieChart.png" alt="screenshot" style="width: 50%; max-width: 600px; height: auto;"/></p>
Here we have diffresent event distributions pie charts.

</div>


  <hr>

  <h2>📚 Table of Contents</h2>
  <ol>
    <li><a href="#key-features">🚀 Key Features</a></li>
    <li><a href="#tech-stack">🛠️ Tech Stack</a>
      <ul>
        <li><a href="#frontend">Frontend</a></li>
        <li><a href="#backendsemantic">Backend/Semantic</a></li>
      </ul>
    </li>
    <li><a href="#environment-variables">🔧 Environment Variables</a></li>
    <li><a href="#installation">📦 Installation</a></li>
    <li><a href="#acknowledgements">💎 Acknowledgements</a></li>
    <li><a href="#tags">🏷️ Tags</a></li>
  </ol>

  <hr>

  <h2 id="key-features">🚀 Key Features</h2>
  <ul>
    <li>✔️ Geographical view of jobs and events with dynamic clusters</li>
    <li>🔍 Ontology-based intelligent filtering</li>
    <li>📊 Dashboard with statistics</li>
    <li>🔗 SPARQL endpoint integration</li>
  </ul>

  <h2 id="tech-stack">🛠️ Tech Stack</h2>

  <h3 id="frontend">Frontend</h3>
  <ul>
    <li>⚪ React + TypeScript</li>
    <li>🌟 React-Bootstrap (UI Components)</li>
    <li>🔗 Axios (API Communication)</li>
    <li>🏯️ Leaflet + React-Leaflet (Interactive Maps)</li>
  </ul>

  <h3 id="backendsemantic">Backend/Semantic</h3>
  <ul>
    <li>🐍 Python (Data Processing)</li>
    <li>🐗 PHP Laravel(Additional API Integrations)</li>
    <li>🏢 Node.js + Express (API)</li>
    <li>🐢 Apache Jena Fuseki (SPARQL Endpoint)</li>
    <li>📚 Ontology OWL2</li>
    <li>⚡ FastAPI (Job Query API)</li>
    <li>🟢 Swagger UI (API Documentation)</li>
    <li>📌 SPARQL & RDF - Used to retrieve and manipulate semantic data from the ontology.</li>
  </ul>

  <h2 id="environment-variables">🔧 Environment Variables</h2>

  <p>To run this project, you will need to add the following environment variables to your <strong>.env</strong> file in folder CiobanuAna\Processing:</p>

  <pre>
    <code>
API_STORE_EVENT_TRIPLE=
FRONTEND_URL=
RSS_FEED_URL=
JOB_HUNTER_QUERY_API_URL=
JOB_HUNTER_API_USERNAME=
JOB_HUNTER_API_PASSWORD=
ONTOLOGY_URL=
PAPERTRAIL_HOST=
PAPERTRAIL_PORT=
    </code>
  </pre>
<p> Also you need to have another <strong>.env</strong> file in Roca Maria\SparqlEndpoint</p>
 <pre>
    <code>
SERVER_PORT=
JOB_HUNTER_UPDATE_API_URL=
JOB_HUNTER_QUERY_API_URL=
JOB_HUNTER_API_USERNAME=
JOB_HUNTER_API_PASSWORD=
PYTHON_SCRIPT_PATH=
PAPERTRAIL_HOST=
PAPERTRAIL_PORT=
    </code>
  </pre>
  <h2 id="installation">📦 Installation</h2>
  <p>Follow these steps to install and run the project:</p>

  <h3>1. Clone the repository:</h3>
  <pre>
    <code>
git clone https://github.com/JobHunter2024/BetaTeam.git
    </code>
  </pre>

  <h3>2. Install dependencies:</h3>
  <pre>
    <code>
cd Roca Maria\SparqlEndpoint
npm install
    </code>
  </pre>

  <h3>3. Start the development server:</h3>
  <pre>
    <code>
php artisan serve
    </code>
  </pre>

  <h3>4. Backend Setup:</h3>
  <p>Ensure the backend API and <strong>Apache Jena Fuseki</strong> are running. Configure SPARQL endpoints accordingly.</p>

  <h2 id="roadmap">🎯 Roadmap</h2>
  <ul>
    <li>🔹 Phase 1: Core map functionality, job/event clustering ✅</li>
    <li>🔹 Phase 2: Semantic ontology integration ✅</li>
    <li>🔹 Phase 3: AI-powered recommendation system ⏳</li>
    <li>🔹 Phase 4: Real-time event statistics ⏳</li>
    <li>🔹 Phase 5: Mobile responsiveness & UX enhancements ⏳</li>
  </ul>

  <h2 id="acknowledgements">💎 Acknowledgements</h2>
  <p>Useful resources and libraries that we have used in our project:</p>
  <ul>
    <li>🛠️ <strong>skillNER</strong> – Python library for skills extraction</li>
    <li>🏢 <strong>Protégé</strong> – Ontology development tool</li>
    <li>📚 <strong>Stanza</strong> – NLP library for location extraction</li>
    <li>🧠 <strong>spaCy</strong> – Used for job details extraction(degree level)</li>
  </ul>


  <h2 id="tags">🏷️ Tags</h2>
  <p>project, infoiasi, wade, web, Web Development</p>

</body>
</html>
