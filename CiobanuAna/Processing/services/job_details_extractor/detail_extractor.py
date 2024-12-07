import re
import spacy
from spacy.matcher import PhraseMatcher
from rapidfuzz import process
from rapidfuzz import fuzz
from CiobanuAna.Processing.utils.aop_logging import log_aspect, exception_handling_aspect

@log_aspect
@exception_handling_aspect
class JobDetailsExtractor:

    def __init__(self):
        self.nlp = spacy.load("en_core_web_sm")
        self.fields_of_study = ["Computer Science", "Mathematics", "Engineering", "Physics", "Finance", "Business Administration", "Economics", "Information Systems",
        "Data Science","Statistics","Artificial Intelligence", "Computer Engineering", "Electrical Engineering", "Cybernetics",
        "Data Engineering","Information Technology", "Accounting", "Industrial Engineering", "Computational Linguistics"]
        self.matcher = PhraseMatcher(self.nlp.vocab, attr="LOWER")
        patterns = [self.nlp.make_doc(field) for field in self.fields_of_study]
        self.matcher.add("EDUCATION_FIELD", patterns)
        
        self.degree_mapping = {
            "b.sc": "Bachelor's Degree",
            "bsc": "Bachelor's Degree",
            "b.sc.": "Bachelor's Degree",
            "bachelor": "Bachelor's Degree",
            "bachelor's degree": "Bachelor's Degree",
            "undergraduate": "Bachelor's Degree",
            "master": "Master Degree",
            "master's degree": "Master's Degree",
            "msc": "Master's Degree",
            "m.sc": "Master's Degree",
            "m.sc.": "Master's Degree",
            "ph.d": "Doctorate",
            "phd": "Doctorate",
            "doctorate": "Doctorate",
            "associate's degree": "Associate's Degree",
            "high school diploma": "High School Diploma"
        }

        self.employment_type_keywords = ["full-time", "part-time", "contract", "freelance", "internship", "temporary"]
        self.location_type_keywords = ["onsite", "online", "remote", "hybrid", "office"]


    def extract_degree_level(self, text):
        return [value for key,value in self.degree_mapping.items() if key in text.lower()]

    def extract_education_field(self, text):
        doc = self.nlp(text)
        matches = self.matcher(doc)
        return [doc[start:end].text.title() for _, start, end in matches]

    def extract_employment_type(self, text):
        return [emp for emp in self.employment_type_keywords if emp in text.lower()]

    def extract_experience_years(self, text):
        experience_pattern = r"(\d+)\+?\s?(years?|yrs?)\s?(of)?(experience)?"
        matches = re.findall(experience_pattern, text, re.IGNORECASE)
        return [f"{match[0]} years" for match in matches]

    def extract_job_location(self, text):
        locations_it_terms = ["Java", "Python", "Perl", "Scala", "Rust", "Swift", "Ruby", "Go", "Dart","Sage",
                              "Ajax","Kotlin","Shell", "Vim", "Node"]
        doc = self.nlp(text)
        location = []
        for ent in doc.ents:
            if ent.label_ in ["GPE","LOC"] and ent.text not in locations_it_terms:
                location.append(ent.text)
        return ", ".join(location)

    def extract_location_type(self, text):
        return [loc for loc in self.location_type_keywords if loc in text.lower()]
