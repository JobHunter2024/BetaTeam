import pytest
from CiobanuAna.Processing.services.normalizers.job_title_normalizer import JobTitleNormalizer

@pytest.fixture
def job_title_normalizer():
    return JobTitleNormalizer()

def test_normalize_title_remove_location(job_title_normalizer):
    job_title = "Software developer Bucharest, Romania"
    expected_result = ("Software developer","Bucharest, Romania",[])
    assert job_title_normalizer.normalize(job_title) == expected_result

def test_normalize_title_remove_year(job_title_normalizer):
    job_title = "Frontend Developer 2024"
    expected_result = ("Frontend Developer","",[])
    assert job_title_normalizer.normalize(job_title) == expected_result

def test_normalize_title_remove_unnecessary_parts(job_title_normalizer):
    job_title = "Data Scientist Remote"
    expected_result = ("Data Scientist","", ["remote"])
    assert job_title_normalizer.normalize(job_title) == expected_result

def test_extract_job_location(job_title_normalizer):
    job_title = "Software developer Sibiu, Romania"
    expected_result = "Sibiu, Romania"
    assert job_title_normalizer.extract_job_location(job_title) == expected_result

def test_extract_location_type(job_title_normalizer):
    job_title = "Software developer hybrid"
    expected_result = ["hybrid"]
    assert job_title_normalizer.extract_location_type(job_title) == expected_result    
