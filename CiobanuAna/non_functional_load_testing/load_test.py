from locust import HttpUser, task, between
import random

class SkillQueryLoadTest(HttpUser):
    wait_time = between(1, 3)  # Simulate a wait time between requests (1 to 3 seconds)

    # A list of sample skills to query
    skills = ["Python", "Java", "JavaScript", "Django", "C++", "Ruby", "React"]

    @task
    def query_skill(self):
        """Query a skill by calling the `query_skill` function."""
        skill = random.choice(self.skills)  # Randomly choose a skill from the list
        self.query_skill_api(skill)

    def query_skill_api(self, skill_name):
        """Send an HTTP GET request to query the Wikidata API for skill information."""
        url = "https://query.wikidata.org/sparql"
        
        # Construct the SPARQL query for the given skill
        query = f"""
        SELECT ?item ?influencedByLabel ?programmedInLabel ?officialWebsite ?type ?description WHERE {{
          # Match the label or alternative labels for the skill
          ?item (rdfs:label|skos:altLabel) "{skill_name}"@en.

          # Get its type (Programming Language, Library, Framework)
          ?item wdt:P31/wdt:P279* ?type.

          # Optional: influenced by
          OPTIONAL {{ ?item wdt:P737 ?influencedBy. }}

          # Optional: programmed in
          OPTIONAL {{ ?item wdt:P277 ?programmedIn. }}

          # Optional: official website
          OPTIONAL {{ ?item wdt:P856 ?officialWebsite. }}
          
          OPTIONAL {{
                        ?item schema:description ?description.
                        FILTER(LANG(?description) = "en")  # Restrict to English description
                    }}

          # Filter for relevant types
          FILTER (?type IN (wd:Q9143, wd:Q29642950, wd:Q188860, wd:Q783866, wd:Q271680, wd:Q1330336, wd:Q17155032, wd:Q506883, wd:Q7397, wd:Q110509708, wd:Q1130645))
          
          SERVICE wikibase:label {{
            bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en".
            ?influencedBy rdfs:label ?influencedByLabel.
            ?programmedIn rdfs:label ?programmedInLabel.
          }}
        }}
        """
        
        headers = {
            'User-Agent': 'Locust Load Testing'
        }
        
        # Set the query as a parameter in the GET request
        params = {
            'query': query,
            'format': 'json'
        }

        # Send the GET request
        with self.client.get(url, params=params, headers=headers, name="Query Skill") as response:
            # Optional: Add assertions to verify the response
            if response.status_code == 200:
                print(f"Successfully queried skill: {skill_name}")
            else:
                print(f"Failed to query skill: {skill_name}, Status Code: {response.status_code}")
