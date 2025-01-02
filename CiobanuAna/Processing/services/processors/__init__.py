import json
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
import os

# Directory where the original files are stored
input_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\job_listings"
output_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\processed_job_listings"

if __name__ == "__main__":
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
    processor = JobListingProcessor(job_title_normalizer, company_name_normalizer, date_normalizer, 
                                    language_skill_extractor, basic_cleaner, skill_extractor, soft_skill_extractor, job_details, technical_skill_extractor)

    # # Example input JSON
    # input_json = '''
    #     {
    #         "jobtitle": "Software Engineer - Remote, Romania",
    #         "company": "TechCorp Inc.",
    #         "date": "2024-11-23",
    #         "job_description": "We are looking for a Software Engineer with 3+ years experience. The candidate must have a Masters degree in computer science, Information Technology or a related field. This is a full-time, onsite position based in Bucharest, Romania. Good speaking capability in English and German. Experience with the Linux operating system and scripting. Good knowledge of one of the following languages: Python, Java, Go, JavaScript, React.js, Nodejs,MongoDB,C++, .NET, C#, Firebase, Tensorflow, Machine Learning, Regression, Telecomunication, Windows, Netwroking, Cybersecurity, Web development, Blockchain, robotics, ruby,Dart, Go,Java, php, kotlin, css, html, sql, typescript, flask, django, spring boot, Ajax, laravel. gin, flatter, matplotlib Oracle experience using numpy, nltk or spacy is a advantage. Be open-minded and prepared to adapt to an international team."
    #     }
    #     '''
    for filename in os.listdir(input_folder):
         if filename.endswith(".json"):
            # Read the input file
            input_file = os.path.join(input_folder, filename)
            with open(input_file, "r") as file:
                input_json = file.read()  # Read file as string
            
            # Process the data
            processed_data = processor.process_job_listing(input_json)
            
            # Write the processed data to a new file
            output_file = os.path.join(output_folder, f"processed_{filename}")
            with open(output_file, "w") as file:
                 file.write(processed_data)

    # # Process the job listing
    # output_json = processor.process_job_listing(input_json)
    # print(output_json)