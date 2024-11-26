import json
import sys
from CiobanuAna.Processing.models.job_listing import JobListing
from CiobanuAna.Processing.services.normalizers.job_title_normalizer import JobTitleNormalizer
from CiobanuAna.Processing.services.normalizers.company_name_normalizer import CompanyNameNormalizer
from CiobanuAna.Processing.services.normalizers.date_normalizer import DateNormalizer
from CiobanuAna.Processing.services.skills_extractor.language_skill_extractor import LanguageSkillExtractor
from CiobanuAna.Processing.services.processors.job_listing_processor import JobListingProcessor
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
from CiobanuAna.Processing.services.skills_extractor.skill_extractor_strategy import SkillExtractor
from CiobanuAna.Processing.services.skills_extractor.soft_skill_extractor import SoftSkillExtractor
from CiobanuAna.Processing.services.job_details_extractor.detail_extractor import JobDetailsExtractor

def process_data(input_json):
    # Initialize normalizers and skill extractor
    job_title_normalizer = JobTitleNormalizer()
    company_name_normalizer = CompanyNameNormalizer()
    date_normalizer = DateNormalizer()
    language_skill_extractor = LanguageSkillExtractor()
    basic_cleaner = BasicCleaner()
    skill_extractor = SkillExtractor()
    soft_skill_extractor = SoftSkillExtractor()
    job_details = JobDetailsExtractor()

    # Create a processor
    processor = JobListingProcessor(job_title_normalizer, company_name_normalizer, date_normalizer, language_skill_extractor, basic_cleaner, skill_extractor, soft_skill_extractor, job_details)

    # Process the job listing
    output_json = processor.process_job_listing(input_json)
    
    return output_json

if __name__ == "__main__":
    input_data ='''
        {
            "jobtitle": "Software Engineer - Remote, Romania",
            "company": "TechCorp Inc.",
            "date": "2024-11-23",
            "job_description": "We are looking for a Software Engineer with 3+ years experience. The candidate must have a Bachelor in Computer Science, Information Technology or a related field. This is a full-time, onsite position based in Bucharest, Romania."
        }
        '''
    print(input_data)
    result = process_data(input_data)
    print(result)