class JobListing:
    def __init__(self, title, companyName, datePosted, language_skills, soft_skills, degree_level, education_field,
                 employment_type, experience_years, experience_level, job_location, job_location_type, job_city, data_removed, programming_languages, frameworks, 
                 libraries, unclassified_skills, already_added_technical_skills):
        self.title = title
        self.companyName = companyName
        self.datePosted = datePosted
        self.language_skills = language_skills
        self.soft_skills = soft_skills
        self.degree_level = degree_level
        self.education_field = education_field
        self.employment_type = employment_type
        self.experience_years = experience_years
        self.experience_level = experience_level
        self.job_location = job_location
        self.job_location_type = job_location_type
        self.job_city = job_city
        self.data_removed = data_removed
        self.programming_languages = programming_languages
        self.frameworks = frameworks
        self.libraries = libraries
        self.unclassified_skills = unclassified_skills
        self.already_added_technical_skills = already_added_technical_skills

    def to_dict(self):
        """Convert the object to a dictionary for JSON serialization."""
        return {
            "title": self.title,
            "companyName": self.companyName,
            "datePosted": self.datePosted,
            "language_skills": self.language_skills,
            "soft_skills": self.soft_skills,
            "degree_level": self.degree_level,
            "education_field": self.education_field,
            "employment_type": self.employment_type,
            "experience_years": self.experience_years,
            "experience_level": self.experience_level,
            "job_location": self.job_location,
            "job_location_type": self.job_location_type,
            "job_city": self.job_city,
            "data_removed": self.data_removed,
            "programming_languages": self.programming_languages,
            "frameworks": self.frameworks,
            "libraries":self.libraries,
            "unclassified_skills": self.unclassified_skills,
            "already_added_technical_skills": self.already_added_technical_skills
        }
