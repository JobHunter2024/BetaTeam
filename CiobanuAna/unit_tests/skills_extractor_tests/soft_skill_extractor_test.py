import pytest
from CiobanuAna.Processing.services.skills_extractor.soft_skill_extractor import SoftSkillExtractor
from CiobanuAna.Processing.services.skills_extractor.skill_extractor_strategy import SkillExtractor
from unittest.mock import MagicMock

@pytest.fixture
def soft_skill_extractor(mocker):
    extractor = SoftSkillExtractor()
    # Mock the FSMMonitor to isolate its behavior
    mock_monitor = mocker.patch.object(extractor, 'monitor', autospec=True)
    return extractor

def test_soft_skill_extractor_no_lang_skills(soft_skill_extractor):
    language_skills_list = []
    soft_skills_list = ["Teamwork", "Communication", "Leadership"]
    expected_result = ["Teamwork", "Communication", "Leadership"]
    assert soft_skill_extractor.remove_language_skills_from_soft_skills(language_skills_list, soft_skills_list) == expected_result

def test_soft_skill_extractor_multiple_lang_skills(soft_skill_extractor):
    language_skills_list = ["French", "Russian", "Spanish"]
    soft_skills_list = ["Teamwork", "French Language", "Communication", "Russian Language", "Leadership", "Spanish Language"]
    expected_result = ["Teamwork", "Communication", "Leadership"]
    assert soft_skill_extractor.remove_language_skills_from_soft_skills(language_skills_list, soft_skills_list) == expected_result    

def test_soft_skill_extractor_no_skills(soft_skill_extractor):
    language_skills_list = []
    soft_skills_list = []
    expected_result = []
    assert soft_skill_extractor.remove_language_skills_from_soft_skills(language_skills_list, soft_skills_list) == expected_result    

def test_soft_skill_extractor_only_language_skills(soft_skill_extractor):
    language_skills_list = ["French", "Russian", "Spanish"]
    soft_skills_list = ["French Language", "Russian Language", "Spanish Language"]
    expected_result = []
    assert soft_skill_extractor.remove_language_skills_from_soft_skills(language_skills_list, soft_skills_list) == expected_result    

def test_soft_skill_extractor_before_all_skill(soft_skill_extractor):
    language_skills_list = ["French"]
    soft_skills_list = ["Teamwork", "French Language", "Communication"]
    soft_skill_extractor.monitor.call_soft_skill_extractor.side_effect = RuntimeError(
        "SoftSkillExtractor called before AllSkillExtractor")

    with pytest.raises(RuntimeError, match="SoftSkillExtractor called before AllSkillExtractor"):
        soft_skill_extractor.remove_language_skills_from_soft_skills(language_skills_list, soft_skills_list)
