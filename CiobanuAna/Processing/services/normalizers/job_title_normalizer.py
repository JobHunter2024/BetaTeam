from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
from CiobanuAna.Processing.services.job_details_extractor.detail_extractor import JobDetailsExtractor
import spacy

nlp = spacy.load("en_core_web_sm")

class JobTitleNormalizer(Normalizer):
    """Normalization strategy for job titles."""

    def normalize(self, title):
        basic_cleaner = BasicCleaner()
        detail_extractor = JobDetailsExtractor()
        location = detail_extractor.extract_job_location(title)
        location_type = detail_extractor.extract_location_type(title)
        unnecessary_parts = ["remote","relocation","full-time","part-time", "hybrid", "contract", "freelancer","internship","onsite","offline","online","office"]
        doc = nlp(title)

        title_tokens = [token.text for token in doc
                        if token.text.lower() not in unnecessary_parts]
        
        title_wth_unn = " ".join(title_tokens)
    
        for ent in nlp(title_wth_unn).ents:
            if ent.label_ in ["GPE", "LOC", "DATE", "CARDINAL"]:
                title_wth_unn = title_wth_unn.replace(ent.text, "")

        return (basic_cleaner.clean(title_wth_unn),location,location_type)           
             
        