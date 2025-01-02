import json
from CiobanuAna.Processing.models.job_listing import JobListing


class JobListingProcessor:
    def __init__(self, job_title_normalizer, company_name_normalizer, date_normalizer, language_skill_extractor, basic_cleaner, 
                 skill_extractor, soft_skill_extractor, detail_extractor, technical_skill_extractor):
        self.job_title_normalizer = job_title_normalizer
        self.company_name_normalizer = company_name_normalizer
        self.date_normalizer = date_normalizer
        self.language_skill_extractor = language_skill_extractor
        self.basic_cleaner = basic_cleaner
        self.skill_extractor = skill_extractor
        self.soft_skill_extractor = soft_skill_extractor
        self.detail_extractor = detail_extractor
        self.technical_skill_extractor = technical_skill_extractor

    def process_job_listing(self, input_json):
        print("Input JSON:", repr(input_json))
        # Parse input JSON
        job_data = json.loads(input_json, strict=False)

        # Extract fields from JSON
        date_posted = job_data.get("date", "")
        job_title = job_data.get("jobTitle", "")
        company_name = job_data.get("company", "")
        location_from_scraping = job_data.get("location","")
        job_description = job_data.get("jobDescription", "")

        # experience level Junior, M
        experience_level = self.detail_extractor.extract_experience_level(job_title, job_description)

        # Normalize fields
        normalized_title, job_location_from_title, job_location_type_from_title = self.job_title_normalizer.normalize(job_title)
        normalized_company = self.company_name_normalizer.normalize(company_name)
        # normalized_date = self.date_normalizer.normalize(date_posted)     # keep for now the date from scraping

        # Extract skills from the job description
        hard_skills,soft_skills = self.skill_extractor.extract_skills(job_description)
        extracted_language_skills = self.language_skill_extractor.extract_language_skills(job_description, soft_skills)
        soft_skills = self.soft_skill_extractor.remove_language_skills_from_soft_skills(extracted_language_skills, soft_skills)
        education_degree_level = self.detail_extractor.extract_degree_level(job_description)
        education_field = self.detail_extractor.extract_education_field(job_description)
        employment_type = self.detail_extractor.extract_employment_type(job_description)
        experience_in_years = self.detail_extractor.extract_experience_years(job_description)
        if not location_from_scraping:
            if not job_location_from_title:
                job_location = self.detail_extractor.extract_job_location(job_description)
            else:
                job_location = job_location_from_title
        else:
            job_location = location_from_scraping        

        if not job_location_type_from_title:        
            job_location_type = self.detail_extractor.extract_location_type(job_description)
        else:
            job_location_type = job_location_type_from_title    
        programming_languages, frameworks, libraries, unclassified_skills = self.technical_skill_extractor.technical_skill_classifier(hard_skills)

        # Create a JobListing object
        processed_job_listing = JobListing(
            normalized_title,
            normalized_company,
            # normalized_date,
            date_posted,
            extracted_language_skills,
            soft_skills,
            education_degree_level,
            education_field,
            employment_type,
            experience_in_years,
            experience_level,
            job_location,job_location_type,
            programming_languages,
            frameworks,
            libraries,
            unclassified_skills
            
        )

        # Convert back to JSON
        return json.dumps(processed_job_listing.to_dict(), indent=4)