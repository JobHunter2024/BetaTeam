from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
import spacy

nlp = spacy.load("en_core_web_sm")

class JobTitleNormalizer(Normalizer):
    """Normalization strategy for job titles."""

    def extract_job_location(self, text):
        locations_it_terms = ["Java", "Python", "Perl", "Scala", "Rust", "Swift", "Ruby", "Go", "Dart","Sage",
                              "Ajax","Kotlin","Shell", "Vim", "Node"]
        doc = nlp(text)
        location = []
        for ent in doc.ents:
            if ent.label_ in ["GPE","LOC"] and ent.text not in locations_it_terms:
                location.append(ent.text)  
        print(location)              
        return ", ".join(location)

    def extract_location_type(self, text):
        location_type_keywords = ["onsite", "online", "remote", "hybrid", "office"]
        return list(loc for loc in location_type_keywords if loc in text.lower()) 

    def normalize(self, title):
        basic_cleaner = BasicCleaner()
        location = self.extract_job_location(title)
        location_type = self.extract_location_type(title)
        unnecessary_parts = ["remote","relocation","full-time","part-time", "hybrid", "contract", "freelancer","internship","onsite","offline","online","office"]
        doc = nlp(title)


        # for ent in doc.ents:
        #     print(f"Entity: {ent.text}, Type: {ent.label_}")
        # for t in doc:
        #     print(f"Token: {t.text}, Type: {t.ent_type_}")


        title_tokens = [token.text for token in doc
                        if token.text.lower() not in unnecessary_parts]
        
        title_wth_unn = " ".join(title_tokens)
    
        for ent in nlp(title_wth_unn).ents:
            if ent.label_ in ["GPE", "LOC", "DATE", "CARDINAL"]:
                title_wth_unn = title_wth_unn.replace(ent.text, "")

        return (basic_cleaner.clean(title_wth_unn),location,location_type)           
             
        