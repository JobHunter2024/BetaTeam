from CiobanuAna.Processing.services.normalizers.normalizer_strategy import Normalizer
from datetime import datetime
from CiobanuAna.Processing.utils.aop_logging import log_aspect, exception_handling_aspect

class DateNormalizer(Normalizer):
    """Normalization strategy for dates."""

    DATE_FORMATS = [
        "%Y-%m-%d",
        "%d/%m/%Y",
        "%B %d, %Y"
    ]

    @log_aspect
    @exception_handling_aspect
    def normalize(self, date_string):
        for date_format in self.DATE_FORMATS:
            try:
                parsed_date = datetime.strptime(date_string, date_format)
                return parsed_date.strftime("%d/%m/%Y")
            except ValueError:
                continue

        raise ValueError(f"Invalid date format: {date_string}")
