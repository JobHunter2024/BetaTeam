class JobListingProcessor:
    """Processes job listings with cleaning, normalization, and extraction."""

    def __init__(self, cleaner, normalizer, skill_extractor):
        self.cleaner = cleaner
        self.normalizer = normalizer
        self.skill_extractor = skill_extractor

    def process(self, raw_listing):
        """Processes a raw job listing JSON and returns a JobListing object."""
        pass