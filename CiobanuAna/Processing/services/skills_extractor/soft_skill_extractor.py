class SoftSkillExtractor():
    """Remove language skills from soft skills"""

    def remove_language_skills_from_soft_skills(self, language_skills, soft_skills):

        for lang_skill in language_skills:
            if lang_skill+" Language" in soft_skills:
                soft_skills.remove(lang_skill+" Language")

        return soft_skills        
