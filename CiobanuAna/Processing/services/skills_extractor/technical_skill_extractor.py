from SPARQLWrapper import SPARQLWrapper, JSON
import re
from CiobanuAna.Processing.utils.fsm_monitor import FSMMonitor

class TechnicalSkillExtractor():

    """Classify technical skills"""

    def __init__(self):
        self.monitor = FSMMonitor()
        self.sparql = SPARQLWrapper("https://query.wikidata.org/sparql")
        # Map Wikidata IDs to categories
        self.category_map = {
            "Q9143": "Programming Language",
            "Q188860": "Library", # software library
            "Q271680": "Framework", # software framework
            "Q29642950": "Library", # Python package
            "Q783866": "Library", # JavaScript library
            "Q1330336": "Framework", # web framework
            "library": "Library",
            "package": "Library",
            "framework": "Framework",
            "programming language": "Programming Language"
        }

    def query_skill(self, skill_name):
        """
        Query Wikidata to check the type of the skill.
        """
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
  # Labels instead of wikidata IDs
    SERVICE wikibase:label {{
    bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en".
    ?influencedBy rdfs:label ?influencedByLabel.
    ?programmedIn rdfs:label ?programmedInLabel.
  }}
}}
        """
        classifier_keywords = ["library","package","framework","programming language"]
        self.sparql.setQuery(query)
        self.sparql.setReturnFormat(JSON)
        results = self.sparql.query().convert()
        
        # Extract the first result's type
        bindings = results.get("results", {}).get("bindings", [])
        if bindings:
            set_influenced_by = set()
            set_programmed_in = set()
            skill_type = ""
            official_website = ""
            for binding in bindings:
                if self.category_map.get(binding["type"]["value"].split("/")[-1], "Unclassified") == "Unclassified": # Extract the Wikidata ID
                    description = binding.get("description", {}).get("value", "No description available")
                    for keyword in classifier_keywords:
                        if keyword in description:
                            skill_type = self.category_map.get(keyword) 
                            break
                else:
                    skill_type = self.category_map.get(binding["type"]["value"].split("/")[-1])
                influenced_by = binding.get("influencedByLabel", {}).get("value", "")
                if influenced_by: 
                    set_influenced_by.add(influenced_by)
                programmed_in = binding.get("programmedInLabel", {}).get("value", "")
                if programmed_in:    
                    set_programmed_in.add(programmed_in)
                if not official_website:    
                    official_website = binding.get("officialWebsite", {}).get("value", "")

            return {"skill_name": skill_name,
                    "skill_type": skill_type, 
                    "official_website": official_website,
                    "influenced_by": list(set_influenced_by),
                    "programmed_in": list(set_programmed_in)}
        
        return {"skill_name": skill_name,
                "skill_type": "Unclassified"}
    
    def technical_skill_classifier(self, technical_skills):
        self.monitor.call_technical_skill_extractor()
        technical_skills_rev = []
        programming_languages = []
        frameworks = []
        libraries = []
        unclassified = []
        for skill in technical_skills:
            cleaned_skill_text = re.sub(r'\s?\(.*\)', '', skill)
            technical_skills_rev.append(cleaned_skill_text)

        for skill in technical_skills_rev:
            category = self.query_skill(skill)
            if category["skill_type"] == "Programming Language":
                programming_languages.append(category)
            elif category["skill_type"] == "Framework":
                frameworks.append(category)
            elif category["skill_type"] == "Library":
                libraries.append(category)
            else:
                unclassified.append(skill)

        return programming_languages, frameworks, libraries, unclassified
