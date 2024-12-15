from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
import re


class CompanyNameNormalizer(Normalizer):
    """Normalization strategy for company names."""

    def normalize(self, company_name):
        basic_cleaner = BasicCleaner()
        # Remove common suffixes
        company_name = re.sub(r"\b(inc\.?|llc\.?|ltd\.?|corp\.?|co\.?|company|s\.?r\.?l|,)\b", "", company_name.lower())
        company_name = basic_cleaner.clean(company_name).title()
        return company_name
