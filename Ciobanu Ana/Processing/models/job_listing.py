class JobListing:
    """Data model for a job listing with preprocessed fields."""

    def __init__(self, title, date, company, skills, location=None):
        self.title = title
        self.date = date
        self.company = company
        self.skills = skills  # List of extracted skills
        self.location = location

    def __repr__(self):
        return f"JobListing(title={self.title}, company={self.company}, date={self.date}, skills={self.skills})"
