import os
from SPARQLWrapper import SPARQLWrapper, JSON
from dotenv import load_dotenv
import os

load_dotenv()

JOB_HUNTER_QUERY_API_URL = os.getenv('JOB_HUNTER_QUERY_API_URL')
JOB_HUNTER_API_USERNAME = os.getenv('JOB_HUNTER_API_USERNAME')
JOB_HUNTER_API_PASSWORD = os.getenv('JOB_HUNTER_API_PASSWORD')

def query_fuseki(query):
    sparql = SPARQLWrapper(JOB_HUNTER_QUERY_API_URL)
    sparql.setQuery(query)
    sparql.setReturnFormat(JSON)
    sparql.setHTTPAuth("BASIC")
    sparql.setCredentials(JOB_HUNTER_API_USERNAME, JOB_HUNTER_API_PASSWORD)
    results = sparql.query().convert()
    
    return results
