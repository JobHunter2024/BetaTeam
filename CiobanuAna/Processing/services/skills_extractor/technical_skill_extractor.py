# from skill_extractor_strategy import SkillExtractor

# class TechnicalSkillExtractor(SkillExtractor):
#     """Extracts technical skills like programming languages."""

#     def extract_skills_custom(self, skills):
#         pass
from SPARQLWrapper import SPARQLWrapper, JSON
import re

class TechnicalSkillExtractor():
    """Classify technical skills"""

    def __init__(self):
        self.sparql = SPARQLWrapper("https://query.wikidata.org/sparql")
        # Map Wikidata IDs to categories
        self.category_map = {
            "Q9143": "Programming Language",
            "Q188860": "Library", # software library
            "Q271680": "Framework",
            "Q29642950": "Library", #Python package
            "library": "Library",
            "package": "Library",
            "framework": "Framework"
        }

    def query_skill(self, skill_name):
        """
        Query Wikidata to check the type of the skill.
        """
        query = f"""
        SELECT ?item ?type ?description WHERE {{
            ?item (rdfs:label|skos:altLabel) "{skill_name}"@en;
                 wdt:P31/wdt:P279* ?type.
            OPTIONAL {{
                ?item schema:description ?description.
                FILTER(LANG(?description) = "en")  # Restrict to English description
            }}   
        }}
        """
        classifier_keywords = ["library","package","framework"]
        self.sparql.setQuery(query)
        self.sparql.setReturnFormat(JSON)
        results = self.sparql.query().convert()
        
        # Extract the first result's type
        bindings = results.get("results", {}).get("bindings", [])
        if bindings:
            for i in range(len(bindings)):
                if self.category_map.get(bindings[i]["type"]["value"].split("/")[-1], "Unclassified") == "Unclassified": # Extract the Wikidata ID
                    description = bindings[i].get("description", {}).get("value", "No description available")
                    for keyword in classifier_keywords:
                        if keyword in description:
                            return self.category_map.get(keyword) 
                else:
                    return self.category_map.get(bindings[i]["type"]["value"].split("/")[-1]) 
        return "Unclassified"
    
    def technical_skill_classifier(self, technical_skills):
        technical_skills_rev = []
        programming_languages = []
        frameworks = []
        libraries = []
        unclassified = []
        for skill in technical_skills:
            if "(Programming Language)" in skill:
                programming_languages.append(skill)
            else:
                cleaned_skill_text = re.sub(r'\s?\(.*\)', '', skill)
                technical_skills_rev.append(cleaned_skill_text)

        for skill in technical_skills_rev:
            category = self.query_skill(skill)
            if category == "Programming Language":
                programming_languages.append(skill)
            elif category == "Framework":
                frameworks.append(skill)
            elif category == "Library":
                libraries.append(skill)
            else:
                unclassified.append(skill)

        return programming_languages, frameworks, libraries, unclassified
