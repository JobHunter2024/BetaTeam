import pytest
from CiobanuAna.Processing.services.job_details_extractor.detail_extractor import JobDetailsExtractor

@pytest.fixture
def job_details_extractor():
    return JobDetailsExtractor()

def test_extract_degree_level_no_degree_available(job_details_extractor):
    job_description = "We are looking for a candidate with relevant experience. No specific degree is required."
    expected_result = []
    assert job_details_extractor.extract_degree_level(job_description) == expected_result

def test_extract_degree_level_multiple_degrees_available(job_details_extractor):
    job_description = "Candidates with a Bachelor's degree in Computer Science, Engineering, or a related field, or a Master's degree in Data Science are preferred."
    expected_result = ["Bachelor's Degree","Master's Degree"]
    assert job_details_extractor.extract_degree_level(job_description).sort() == expected_result.sort()    

def test_extract_education_field(job_details_extractor):
    job_description = "We require a degree in Computer Science, Electrical Engineering, or Mathematics."
    expected_result = ["Computer Science", "Electrical Engineering", "Mathematics"]
    assert job_details_extractor.extract_education_field(job_description).sort() == expected_result.sort()

def test_extract_employment_type(job_details_extractor):
    job_description = "This is a full-time position with flexible working hours. Part-time and contract opportunities are also available."
    expected_result = ["full-time", "part-time", "contract"]
    assert job_details_extractor.extract_employment_type(job_description).sort() == expected_result.sort()

def test_extract_experience_years(job_details_extractor):
    job_description = "Applicants must have at least 5 years of experience in software development. "
    expected_result = ["5 years"]
    assert job_details_extractor.extract_experience_years(job_description) == expected_result

def test_extract_experience_level_directly_specified(job_details_extractor):
    job_description = "This role is suitable for mid-level professionals. Candidates with strong leadership skills are also encouraged to apply."
    job_title = "Software developer"
    expected_result = "Mid-level"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result

def test_extract_experience_level_abrev(job_details_extractor):
    job_description = "This role is suitable for beginners. Candidates that want to start a career"
    job_title = "Jr Python Developer"
    expected_result = "Junior"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result 

def test_extract_experience_level_in_title(job_details_extractor):
    job_description = "Searching for a new collegue"
    job_title = "Data scientist associate"
    expected_result = "Associate"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result     

def test_extract_experience_level_no_data_available(job_details_extractor):
    job_description = "Searching for a new collegue"
    job_title = "Softwer engineer"
    expected_result = ""
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result        

def test_extract_experience_level_based_on_experience_years(job_details_extractor):
    job_description = "Looking for a software developer with more that 6 years experience in a similar position"
    job_title = "Software developer"
    expected_result = "Senior"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result   

def test_extract_experience_level_junior_based_on_experience_years(job_details_extractor):
    job_description = "Looking for a Software Developer with 1-2 years of experience in Python, Java, or JavaScript to build and maintain scalable applications. Must have strong problem-solving skills and familiarity with modern frameworks and tools."
    job_title = "Software developer"
    expected_result = "Junior"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result    

def test_extract_experience_level_midlevel_based_on_experience_years(job_details_extractor):
    job_description = "Seeking an IT Support Specialist with 3-5 years of experience in troubleshooting hardware and software issues, managing networks, and providing technical support to end-users. Excellent communication skills are a must."
    job_title = "Support Specialist"
    expected_result = "Mid-level"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result    

def test_extract_experience_level_senior(job_details_extractor):
    job_description = "We are looking for a Senior Software Engineer experience in designing, developing, and deploying scalable software solutions. Expertise in Python, cloud platforms, and system architecture is required. Leadership and mentoring experience are highly valued."
    job_title = "Software Engineer"
    expected_result = "Senior"
    assert job_details_extractor.extract_experience_level(job_title, job_description) == expected_result                      

def test_extract_job_location(job_details_extractor):
    job_description = "Our office is located in New York City. Remote work options are available for candidates based in the United States."
    expected_result = ["New York City", "the United States"]
    assert job_details_extractor.extract_job_location(job_description).split(", ").sort() == expected_result.sort()

def test_extract_location_type(job_details_extractor):
    job_description = "This is a hybrid position"
    expected_result = ["hybrid"]
    assert job_details_extractor.extract_location_type(job_description) == expected_result    
