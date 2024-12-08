from CiobanuAna.Processing.utils.aop_logging import log_aspect, argument_validation_aspect
from CiobanuAna.Processing.utils.fsm_monitor import FSMMonitor


class SoftSkillExtractor():
    def __init__(self):
        self.monitor = FSMMonitor()

    """Remove language skills from soft skills"""
    @log_aspect
    @argument_validation_aspect
    def remove_language_skills_from_soft_skills(self, language_skills, soft_skills):
        self.monitor.call_soft_skill_extractor()
        for lang_skill in language_skills:
            if lang_skill+" Language" in soft_skills:
                soft_skills.remove(lang_skill+" Language")

        return soft_skills        
