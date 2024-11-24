from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
from datetime import datetime
from CiobanuAna.Processing.utils.aop_logging import log_aspect, exception_handling_aspect

class DateNormalizer(Normalizer):
    """Normalization strategy for dates."""

    @log_aspect
    @exception_handling_aspect
    def normalize(self, date_string):
        date_formats = [
            "%Y-%m-%d",
            "%d/%m/%Y",
            "%B %d, %Y"
        ]

        for date_format in date_formats:
            try:
                parsed_date = datetime.strptime(date_string, date_format)
                return parsed_date.strftime("%d/%m/%Y")
            except ValueError:
                continue

        raise ValueError(f"Invalid date format: {date_string}")
