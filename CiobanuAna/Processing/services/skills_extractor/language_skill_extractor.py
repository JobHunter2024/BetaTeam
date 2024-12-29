import spacy

nlp = spacy.load("en_core_web_sm")

class LanguageSkillExtractor():
    """Extracts language skills from the job description."""

    def extract_language_skills(self, job_description, soft_skills):
        doc = nlp(job_description)

        language_skills = []
        # Filter just language entities
        language_skills = [token.text for token in doc if token.ent_type_ in ["LANGUAGE", "NORP"] and token.text+" Language" in soft_skills]
        

        return language_skills   