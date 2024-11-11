from normalizer_strategy import Normalizer
import re

class CompanyNameNormalizer(Normalizer):
    """Normalization strategy for company names."""

    def normalize(self, company_name):
        # Remove common suffixes
        company_name = re.sub(r"\b(inc|llc|ltd|corp|co|company)\b", "", company_name)
        company_name = company_name.title()
        return company_name
