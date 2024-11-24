class JobListing:
    def __init__(self, title, companyName, datePosted, language_skills, hard_skills, soft_skills, degree_level, education_field,
                 employment_type, experience_years, job_location, job_location_type):
        self.title = title
        self.companyName = companyName
        self.datePosted = datePosted
        self.language_skills = language_skills
        self.hard_skills = hard_skills
        self.soft_skills = soft_skills
        self.degree_level = degree_level
        self.education_field = education_field
        self.employment_type = employment_type
        self.experience_years = experience_years
        self.job_location = job_location
        self.job_location_type = job_location_type

    def to_dict(self):
        """Convert the object to a dictionary for JSON serialization."""
        return {
            "title": self.title,
            "companyName": self.companyName,
            "datePosted": self.datePosted,
            "language_skills": self.language_skills,
            "hard_skills": self.hard_skills,
            "soft_skills": self.soft_skills,
            "degree_level": self.degree_level,
            "education_field": self.education_field,
            "employment_type": self.employment_type,
            "experience_years": self.experience_years,
            "job_location": self.job_location,
            "job_location_type": self.job_location_type
        }
