from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
import spacy

nlp = spacy.load("en_core_web_sm")

class JobTitleNormalizer(Normalizer):
    """Normalization strategy for job titles."""

    def normalize(self, title):
        unnecessary_parts = ["remote","relocation","full-time","part-time", "hybrid", "contract"]
        doc = nlp(title)

        final_tokens = []

        # for ent in doc.ents:
        #     print(f"Entity: {ent.text}, Type: {ent.label_}")
        # for t in doc:
        #     print(f"Token: {t.text}, Type: {t.ent_type_}")

        
        # Filter out location-related and date entities (GPE, LOC, DATE, CARDINAL)
        pre_final_tokens = [ent.text for ent in doc.ents if ent.label_ not in ["GPE", "LOC", "DATE", "CARDINAL"] and 
                        ent.text.lower() not in unnecessary_parts]
        final_tokens = [token.text for token in nlp(" ".join(pre_final_tokens))
            if token.text.lower() not in unnecessary_parts]

        # Join the tokens to form the normalized title
        return " ".join(final_tokens)        
        