import re
import spacy
from spacy.matcher import PhraseMatcher

class JobDetailsExtractor:

    def __init__(self):
        self.nlp = spacy.load("en_core_web_sm")
        self.fields_of_study = ["Computer Science", "Mathematics", "Engineering", "Physics", "Finance", "Business Administration", "Economics", "Information Systems",
        "Data Science","Statistics","Artificial Intelligence", "Computer Engineering", "Electrical Engineering", "Cybernetics",
        "Data Engineering","Information Technology", "Accounting", "Industrial Engineering", "Computational Linguistics"]
        self.degree_keywords = ["bachelor's degree","bachelor degree", "bachelor","master's degree","master degree","master", "phd", "doctorate", "associate's degree", "high school diploma"]
        self.employment_type_keywords = ["full-time", "part-time", "contract", "freelance", "internship", "temporary"]
        self.location_type_keywords = ["onsite", "online", "remote", "hybrid"]
        self.matcher = PhraseMatcher(self.nlp.vocab)
        patterns = [self.nlp.make_doc(field) for field in self.fields_of_study]
        self.matcher.add("EDUCATION_FIELD", patterns)

    def extract_degree_level(self, text):
        return [degree for degree in self.degree_keywords if degree in text.lower()]

    def extract_education_field(self, text):
        doc = self.nlp(text)
        matches = self.matcher(doc)
        return [doc[start:end].text for _, start, end in matches]

    def extract_employment_type(self, text):
        return [emp for emp in self.employment_type_keywords if emp in text.lower()]

    def extract_experience_years(self, text):
        experience_pattern = r"(\d+)\+?\s?(years?|yrs?)\s?(of)?(experience)?"
        matches = re.findall(experience_pattern, text, re.IGNORECASE)
        return [f"{match[0]} years" for match in matches]

    def extract_job_location(self, text):
        doc = self.nlp(text)
        return [ent.text for ent in doc.ents if ent.label_ in ["GPE", "LOC"]]

    def extract_location_type(self, text):
        return [loc for loc in self.location_type_keywords if loc in text.lower()]

#     def extract_all_details(self, text):
#         """Extracts all job-related details."""
#         return {
#             "education_degree_level": self.extract_degree_level(text),
#             "education_field": self.extract_education_field(text),
#             "employment_type": self.extract_employment_type(text),
#             "experience_in_years": self.extract_experience_years(text),
#             "job_location": self.extract_job_location(text),
#             "job_location_type": self.extract_location_type(text)
#         }


# extractor = JobDetailsExtractor()
# job_description = """
# We are looking for a Software Engineer with 3+ years experience, 5 years of experience 
# The candidate must have a Bachelor in Computer Science, Information Technology or a related field. 
# This is a full-time, onsite position based in Bucharest, Romania.
# """
# print(extractor.extract_all_details(job_description))
