from abc import ABC, abstractmethod
import spacy
from spacy.matcher import PhraseMatcher
from skillNer.general_params import SKILL_DB
from skillNer.skill_extractor_class import SkillExtractor
from CiobanuAna.Processing.utils.aop_logging import log_aspect, execution_time_aspect

nlp = spacy.load("en_core_web_lg")
skill_extractor = SkillExtractor(nlp, SKILL_DB, PhraseMatcher)

class SkillExtractor(ABC):
    @log_aspect
    @execution_time_aspect
    def extract_skills(self, job_description):
        """Extract skills from the job description."""       
        annotations = skill_extractor.annotate(job_description)
        results = annotations['results']

        hard_skills = []
        soft_skills = []
        f_matches = results['full_matches']
        for match in f_matches:
            id_ = match['skill_id']
            full_name = SKILL_DB[id_]['skill_name']
            type_ = SKILL_DB[id_]['skill_type']
            if type_ == 'Hard Skill':
                hard_skills.append(full_name)
            else:
                soft_skills.append(full_name)

        s_matches = results['ngram_scored']
        for match in s_matches:
            id_ = match['skill_id']
            full_name = SKILL_DB[id_]['skill_name']
            type_ = SKILL_DB[id_]['skill_type']
            if type_ == 'Hard Skill':
                hard_skills.append(full_name)
            else:
                soft_skills.append(full_name)
        return hard_skills, soft_skills



    # @abstractmethod
    # def extract_skills_custom():
    #     pass
