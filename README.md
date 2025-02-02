# 🗺️ JobHunter - Intelligent Job Board

[![React Version](https://img.shields.io/badge/react-18.2.0-blue)](https://reactjs.org/)
[![Leaflet Version](https://img.shields.io/badge/leaflet-1.9.4-green)](https://leafletjs.com/)

An intelligent platform for viewing jobs in Romania and IT events, integrating:
- **Interactive maps** with location clusters
- **Semantic ontology** for classifying jobs and events
- **Recommendation system** based on preferences
- **Event statistics**
- **Search for events**

![Demo Screenshot](screenshot.png)

## 🚀 Key Features
- ✔️ Geographical view of jobs with dynamic clusters
- 🔍 Ontology-based intelligent filtering
- 📌 Semantic recommendation system
- 📊 Dashboard with statistics
- 🔗 SPARQL endpoint integration

## 🛠️ Tech Stack
**Frontend:**
- React 18 + TypeScript
- Leaflet + React-Leaflet
- React-Bootstrap (UI)
- Axios (comunicare API)

**Backend/Semantic:**
- API Node.js
- Apache Jena Fuseki (SPARQL endpoint)
- Ontologie OWL2 
- Python
- PHP
- FastAPI
- 
### :key: Environment Variables

To run this project, you will need to add the following environment variables to your .env file

`API_STORE_EVENT_TRIPLE`
`FRONTEND_URL`
`RSS_FEED_URL`
`JOB_HUNTER_QUERY_API_URL`
`JOB_HUNTER_API_USERNAME`
`JOB_HUNTER_API_PASSWORD`
`ONTOLOGY_URL`
`PAPERTRAIL_HOST`
`PAPERTRAIL_PORT`


## 📦 Installation
1. Clone the repository:
```bash
git clone https://github.com/JobHunter2024/BetaTeam.git
