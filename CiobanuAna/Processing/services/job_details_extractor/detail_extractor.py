import re
import spacy
import nltk
import locationtagger
import stanza
from spacy.matcher import PhraseMatcher
from CiobanuAna.Processing.utils.aop_logging import log_aspect, exception_handling_aspect
import warnings
warnings.filterwarnings("ignore", category=FutureWarning)
 
# nltk.download('maxent_ne_chunker', force=True)
# nltk.download('words', force=True)
# nltk.download('treebank', force=True)
# nltk.download('maxent_treebank_pos_tagger', force=True)
# nltk.download('punkt',force=True)
# nltk.download('averaged_perceptron_tagger', force=True)

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
        return list(set(value for key,value in self.degree_mapping.items() if key in text.lower()))

    def extract_education_field(self, text):
        doc = self.nlp(text)
        matches = self.matcher(doc)
        return list(set(doc[start:end].text.title() for _, start, end in matches))

    def extract_employment_type(self, text):
        return list(set(emp for emp in self.employment_type_keywords if emp in text.lower()))

    def extract_experience_years(self, text):
        experience_pattern = r"(\d+)\+?\s?(years?|yrs?)\s?(of)?(experience)?"
        matches = re.findall(experience_pattern, text, re.IGNORECASE)
        return [f"{match[0]} years" for match in matches]
    
    def extract_experience_level(self, title, text):
        if "junior" in title.lower() or "jr" in title.lower() or "junior" in text.lower() or "jr" in text.lower():
            return "Junior"
        elif "mid-level" in title.lower() or "middle" in title.lower() or "mid-level" in text.lower() or "middle" in text.lower():
            return "Mid-level"
        elif "associate" in title.lower() or "associate" in text.lower():
            return "Associate"
        elif "senior" in title.lower() or "sr" in title.lower() or "senior" in text.lower() or "sr" in text.lower():
            return "Senior"
        experience_in_years = self.extract_experience_years(text)
        for exp in experience_in_years:
            level = int(exp.split(" ")[0])
            if level < 3:
                return "Junior"
            elif 3 <=level <= 5:
                return "Mid-level"
            else:
                return "Senior"
        return ""

    def extract_job_location(self, text):
        locations_it_terms = ["Java", "Python", "Perl", "Scala", "Rust", "Swift", "Ruby", "Go", "Dart","Sage",
                              "Ajax","Kotlin","Shell", "Vim", "Node", "Amazon"]
        # place_entity = locationtagger.find_locations(text = text)
        # location_tagger = place_entity.countries + place_entity.regions + place_entity.cities
        locations_found = []
        nlp = stanza.Pipeline(lang='en', processors='tokenize,ner', download_method=None, verbose=False)
        doc = nlp(text)
        for sent in doc.sentences:
            for ent in sent.ents:
                if ent.type == 'GPE' and ent.text not in locations_it_terms:
                    locations_found.append(ent.text)
        # doc = self.nlp(text)
        # location = []
        # for ent in doc.ents:
        #     if ent.label_ in ["GPE","LOC"] and ent.text not in locations_it_terms:
        #         location.append(ent.text)        
        return ", ".join(set(locations_found))

    def extract_location_type(self, text):
        return list(set(loc for loc in self.location_type_keywords if loc in text.lower()))