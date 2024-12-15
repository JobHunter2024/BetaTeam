import pytest
from CiobanuAna.Processing.services.skills_extractor.technical_skill_extractor import TechnicalSkillExtractor
from CiobanuAna.Processing.services.skills_extractor.skill_extractor_strategy import SkillExtractor

@pytest.fixture
def technical_skill_extractor():
    all_skill_extractor = SkillExtractor()
    hard_skills, soft_skills = all_skill_extractor.extract_skills("Require Python, Java, adaptability")
    return TechnicalSkillExtractor()

@pytest.fixture
def technical_skill_extractor_before_all_skill():
    return TechnicalSkillExtractor()

def test_technical_skill_extractor_before_all_skill(technical_skill_extractor_before_all_skill):
    with pytest.raises(RuntimeError, match="TechnicalSkillExtractor called before AllSkillExtractor"):
        technical_skill_extractor_before_all_skill.technical_skill_classifier(["SpaCy"])   

def test_technical_skill_extractor_query_can_be_classified_instance_of(technical_skill_extractor):
        expected_result = {"skill_name": "Python",
                    "skill_type": "Programming Language", 
                    "official_website": "https://www.python.org/",
                    "influenced_by": ["ABC",  
                                    "ALGOL 68",  
                                    "APL",  
                                    "C" , 
                                    "C++", 
                                    "CLU" , 
                                    "Dylan",  
                                    "Haskell" , 
                                    "Icon",  
                                    "Java",  
                                    "Lisp",  
                                    "Modula-3",  
                                    "Perl",  
                                    "Standard ML",],
                    "programmed_in": ["C","Python"]}
        result = technical_skill_extractor.query_skill("Python")
        result["influenced_by"].sort()
        result["programmed_in"].sort()
        assert result == expected_result   

def test_technical_skill_extractor_query_can_be_classified_description(technical_skill_extractor):
    expected_result = {"skill_name": "SpaCy",
                    "skill_type": "Library", 
                    "official_website": "https://spacy.io",
                    "influenced_by": [],
                    "programmed_in": ["Python"]}
    result = technical_skill_extractor.query_skill("SpaCy")
    assert result == expected_result      

def test_technical_skill_extractor_query_unclassified(technical_skill_extractor):
    expected_result = {"skill_name": "System Testing",
                "skill_type": "Unclassified"}
    assert technical_skill_extractor.query_skill("System Testing") == expected_result    
