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
from CiobanuAna.Processing.services.skills_extractor.technical_skill_extractor import TechnicalSkillExtractor

def process_data(input_json):
    try:
        # Load the input JSON string into a dictionary
        job_data = json.loads(input_json)
    except json.JSONDecodeError as e:
        raise ValueError(f"Invalid JSON input: {e}")

    # Initialize normalizers and skill extractor
    job_title_normalizer = JobTitleNormalizer()
    company_name_normalizer = CompanyNameNormalizer()
    date_normalizer = DateNormalizer()
    language_skill_extractor = LanguageSkillExtractor()
    basic_cleaner = BasicCleaner()
    skill_extractor = SkillExtractor()
    soft_skill_extractor = SoftSkillExtractor()
    job_details = JobDetailsExtractor()
    technical_skill_extractor = TechnicalSkillExtractor()

    # Create a processor
    processor = JobListingProcessor(
        job_title_normalizer,
        company_name_normalizer,
        date_normalizer,
        language_skill_extractor,
        basic_cleaner,
        skill_extractor,
        soft_skill_extractor,
        job_details,
        technical_skill_extractor
    )

  # Process the job listing
    try:
        output_json = processor.process_job_listing(json.dumps(job_data))
        # Ensure the result is parsed into a dictionary
        return json.loads(output_json) if isinstance(output_json, str) else output_json
    except Exception as e:
        raise RuntimeError(f"Failed to process job listing: {e}")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("No input JSON file provided.")
        sys.exit(1)

    input_file_path = sys.argv[1]

    try:
        with open(input_file_path, 'r') as file:
            input_data = file.read()

        result = process_data(input_data)
        print(json.dumps(result, indent=4))  # Pretty-print JSON output
 
    except ValueError as ve:
        print(ve)
        sys.exit(1)
    except RuntimeError as re:
        print(re)
        sys.exit(1)
    except FileNotFoundError:
        print(f"File not found: {input_file_path}")
        sys.exit(1)
