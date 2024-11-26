from CiobanuAna.Processing.utils.aop_logging import log_aspect, argument_validation_aspect


class SoftSkillExtractor():
    """Remove language skills from soft skills"""
    @log_aspect
    @argument_validation_aspect
    def remove_language_skills_from_soft_skills(self, language_skills, soft_skills):

        for lang_skill in language_skills:
            if lang_skill+" Language" in soft_skills:
                soft_skills.remove(lang_skill+" Language")

        return soft_skills        
