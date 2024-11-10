from .normalizer_strategy import Normalizer
from datetime import datetime

class DateNormalizer(Normalizer):
    """Normalization strategy for dates."""

    def normalize(self, date_string):
        # Date formats that can occur
        date_formats = [
            "%Y-%m-%d",
            "%d/%m/%Y",
            "%B %d, %Y"
        ]

        for date_format in date_formats:
            try:
                # Try to parse the date with the current format
                parsed_date = datetime.strptime(date_string, date_format)
                # Format the date as dd/mm/yyyy
                return parsed_date.strftime("%d/%m/%Y")
            except ValueError:
                continue  # Try the next format if the current one fails

        # None of the formats worked, raise a ValueError
        raise ValueError(f"Invalid date format: {date_string}")