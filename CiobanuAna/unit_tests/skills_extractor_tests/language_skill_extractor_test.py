import pytest
from CiobanuAna.Processing.services.skills_extractor.language_skill_extractor import LanguageSkillExtractor

@pytest.fixture
def language_skill_extractor():
    return LanguageSkillExtractor()

def test_language_skill_extractor_multiple_lang_skills(language_skill_extractor):
    job_description = "We are looking for someone fluent in French and German, with excellent communication skills."
    soft_skills_list = ["French Language", "German Language", "Communication", "Teamwork", "Problem Solving"]
    expected_result = ["French", "German"]
    assert language_skill_extractor.extract_language_skills(job_description, soft_skills_list) == expected_result

def test_language_skill_extractor_no_lang_skills(language_skill_extractor):
    job_description = "We need a candidate with exceptional teamwork and problem-solving abilities."
    soft_skills_list = ["Teamwork", "Problem Solving"]
    expected_result = []
    assert language_skill_extractor.extract_language_skills(job_description, soft_skills_list) == expected_result    

def test_language_skill_extractor_no_soft_skills(language_skill_extractor):
    job_description = "We search for a softwer developer to complete our team"
    soft_skills_list = []
    expected_result = []
    assert language_skill_extractor.extract_language_skills(job_description, soft_skills_list) == expected_result    

  
