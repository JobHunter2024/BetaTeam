from fastapi import FastAPI, Query
import fuseki_querying, sparql_queries
from fastapi.middleware.cors import CORSMiddleware
from typing import Optional
import os
from dotenv import load_dotenv

load_dotenv()

FRONTEND_URL = os.getenv('FRONTEND_URL')
ONTOLOGY_URL = os.getenv('ONTOLOGY_URL')

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=[FRONTEND_URL],  # Allow React frontend's origin
    allow_credentials=True,
    allow_methods=["*"],  # Allow all HTTP methods (GET, POST, etc.)
    allow_headers=["*"],  # Allow all headers
)


@app.get("/events-by-type")
async def get_events_by_type():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_by_type_query)
    bindings = sparql_results['results']['bindings']

    result = [
        {
            'label': binding['eventType']['value'], 
            'value': int(binding['eventCount']['value'])  
        }
        for binding in bindings
    ]
    
    return result

@app.get("/events-per-date")
async def get_events_per_date():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_per_date_query)
    bindings = sparql_results['results']['bindings']
    
    result = [
        {
            'label': binding['eventDate']['value'],
            'value': int(binding['eventCount']['value']) 
        }
        for binding in bindings
    ]
    
    return result

@app.get("/events-is-online")
async def get_events_is_online():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_is_online_query)
    bindings = sparql_results['results']['bindings']
    
    result = [
        {
            'label': binding['isOnline']['value'],  # The 'isOnline' value (true/false)
            'value': int(binding['eventCount']['value']) 
        }
        for binding in bindings
    ]
    
    return result

@app.get("/events-per-topic")
async def get_events_per_topic():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_per_topic_query)
    bindings = sparql_results['results']['bindings']

    result = [
        {
            'label': binding['topic']['value'].split('#')[-1], 
            'value': int(binding['eventCount']['value'])  
        }
        for binding in bindings
    ]
    
    return result

@app.get("/events-topics")
async def get_events_per_topic():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_topics)
    bindings = sparql_results['results']['bindings']

    result = [ binding['topic']['value'].split('#')[-1] for binding in bindings]
    
    return result

@app.get("/events-types")
async def get_events_types():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_types)
    bindings = sparql_results['results']['bindings']

    result = [binding['type']['value'].split('#')[-1] for binding in bindings]
    
    return result 

@app.get("/events-locations")
async def get_events_locations():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_locations)
    bindings = sparql_results['results']['bindings']

    result = [ binding['location']['value'].split('#')[-1] for binding in bindings]
    
    return result   

@app.get("/events-per-technical-skill")
async def get_events_per_technical_skill():
    sparql_results = fuseki_querying.query_fuseki(sparql_queries.events_per_specific_technical_skill_query)
    bindings = sparql_results['results']['bindings']

    result = [
        {
            'name': binding['topic']['value'].split('#')[-1], 
            'count': int(binding['eventCount']['value']),  
            'category': binding['topicType']['value'].split('#')[-1]
        }
        for binding in bindings
    ]  
    
    return result

@app.get("/events")
async def get_events(
    event_types: Optional[str] = Query(None, alias="type"),
    topics: Optional[str] = Query(None),
    locations: Optional[str] = Query(None),
    is_online: Optional[bool] = Query(None, alias="isOnline"),
    dates: Optional[str] = Query(None)
):
    
    filters = []

    if is_online is not None:
        if is_online:
            is_online_str = "true" 
            sparql_query = sparql_queries.base_events_online_true_query 
        else: 
            is_online_str = "false"
            sparql_query = sparql_queries.base_events_online_false_query
        filters.append(f'FILTER(str(?isOnline) = "{is_online_str}")')
    else:
        sparql_query1 = sparql_queries.base_events_online_false_query
        sparql_query2 = sparql_queries.base_events_online_true_query   

    if event_types:
        event_types_list = event_types.split(",")
        values_clause = " ".join(f'"{event_type}"' for event_type in event_types_list)
        if is_online is not None:
            sparql_query += f"\n  VALUES ?eventType {{ {values_clause} }}"
        else:
            sparql_query1 += f"\n  VALUES ?eventType {{ {values_clause} }}"
            sparql_query2 += f"\n  VALUES ?eventType {{ {values_clause} }}"    

    if topics:
        topics_list = topics.split(",")
        values_clause = " ".join(f'<{ONTOLOGY_URL}#{topic}>' for topic in topics_list)
        if is_online is not None:
            sparql_query += f"\n  VALUES ?topic {{ {values_clause} }}"
        else:
            sparql_query1 += f"\n  VALUES ?topic {{ {values_clause} }}"
            sparql_query2 += f"\n  VALUES ?topic {{ {values_clause} }}"

    if locations:
        locations_list = locations.split(",")
        values_clause = " ".join(f':{location}' for location in locations_list)
        if is_online is not None:
            sparql_query += f"\n  VALUES ?location {{ {values_clause} }}"
        else:
            sparql_query1 += f"\n  VALUES ?location {{ {values_clause} }}"
            sparql_query2 += f"\n  VALUES ?location {{ {values_clause} }}"

    if dates:
        dates_list = dates.split(",")
        values_clause = " ".join(f'"{date}"' for date in dates_list)
        if is_online is not None:
            sparql_query += f"\n  VALUES ?date {{ {values_clause} }}"
        else:
            sparql_query1 += f"\n  VALUES ?date {{ {values_clause} }}"
            sparql_query2 += f"\n  VALUES ?date {{ {values_clause} }}"
    
    # Add filters to query
    if filters:
        if is_online is not None:
            sparql_query += "\n".join(filters)
        else:
            sparql_query1 += "\n".join(filters)
            sparql_query2 += "\n".join(filters)

    if is_online is not None:
        sparql_query += " }"
    else:
        sparql_query1 += " }"
        sparql_query2 += " }"

    if is_online is not None:
        sparql_results = fuseki_querying.query_fuseki(sparql_query)
        bindings = sparql_results['results']['bindings']
    else:
        sparql_results1 = fuseki_querying.query_fuseki(sparql_query1)
        bindings1 = sparql_results1['results']['bindings']
        sparql_results2 = fuseki_querying.query_fuseki(sparql_query2)
        bindings2 = sparql_results2['results']['bindings']

    if is_online is not None:
        if is_online:

            result = [
                {   
                    "title": binding["eventTitle"]["value"],
                    "type": binding["eventType"]["value"],
                    "topic": binding["topic"]["value"].split("#")[-1],
                    "isOnline": binding["isOnline"]["value"] == "true",
                    "date": binding["date"]["value"]
                }
                for binding in bindings
            ]
        else:
            result = [
                {
                    "title": binding["eventTitle"]["value"],
                    "type": binding["eventType"]["value"],
                    "topic": binding["topic"]["value"].split("#")[-1],
                    "isOnline": binding["isOnline"]["value"] == "true",
                    "location": binding["location"]["value"].split("#")[-1],
                    "date": binding["date"]["value"]
                }
                for binding in bindings
            ]
    else:
        result1 = [
            {
                "title": binding["eventTitle"]["value"],
                "type": binding["eventType"]["value"],
                "topic": binding["topic"]["value"].split("#")[-1],
                "isOnline": binding["isOnline"]["value"] == "true",
                "location": binding["location"]["value"].split("#")[-1],
                "date": binding["date"]["value"]
            }
            for binding in bindings1
        ]
        result2 = [
                {   
                    "title": binding["eventTitle"]["value"],
                    "type": binding["eventType"]["value"],
                    "topic": binding["topic"]["value"].split("#")[-1],
                    "isOnline": binding["isOnline"]["value"] == "true",
                    "location": "-",
                    "date": binding["date"]["value"]
                }
                for binding in bindings2
            ]
        result = result1 + result2
            
    
    return result        
