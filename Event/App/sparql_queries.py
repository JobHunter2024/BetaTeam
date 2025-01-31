# Calendar heatmap
events_per_date_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT ?eventDate (COUNT(?event) AS ?eventCount)
WHERE {{
  ?event rdf:type :Event ;
         :eventDate ?eventDate .
}}
GROUP BY ?eventDate
ORDER BY ASC(?eventDate)
"""
# pie chart percentage of type of events: meeting, conference, etc
events_by_type_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT ?eventType (COUNT(?event) AS ?eventCount)
WHERE {{
  ?event rdf:type :Event ;
         :eventType ?eventType .
}}
GROUP BY ?eventType
ORDER BY DESC(?eventCount)
"""
#  pie chart percentage of online/offline
events_is_online_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT ?isOnline (COUNT(?event) AS ?eventCount)
WHERE {{
  ?event rdf:type :Event ;
         :isOnline ?isOnline .
}}
GROUP BY ?isOnline
ORDER BY DESC(?eventCount)
"""
# bar chart nr of events per topic
events_per_topic_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT ?topic (COUNT(?event) AS ?eventCount)
WHERE {{
  ?event rdf:type :Event ;
         :hasTopic ?topic .
}}
GROUP BY ?topic
ORDER BY DESC(?eventCount)
"""

events_types = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?type
WHERE {{
  ?event rdf:type :Event ;
         :eventType ?type .
}}
"""

events_topics = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?topic 
WHERE {{
  ?event rdf:type :Event ;
         :hasTopic ?topic .
}}
"""

events_locations = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT DISTINCT ?location 
WHERE {{
  ?event rdf:type :Event ;
         :takesPlaceIn ?location .
}}
"""

base_events_online_true_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT  ?eventTitle ?eventType ?topic ?isOnline ?date WHERE {{
        ?event rdf:type :Event ;
               :eventTitle ?eventTitle;
               :eventType ?eventType;
               :hasTopic ?topic;
               :isOnline ?isOnline;
               :eventDate ?date.

"""

base_events_online_false_query = f"""
PREFIX : <http://www.semanticweb.org/ana/ontologies/2024/10/JobHunterOntology#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT  ?eventTitle ?eventType ?topic ?isOnline ?location ?date WHERE {{
        ?event rdf:type :Event ;
               :eventTitle ?eventTitle;
               :eventType ?eventType;
               :hasTopic ?topic;
               :isOnline ?isOnline;
               :takesPlaceIn ?location;
               :eventDate ?date.

"""