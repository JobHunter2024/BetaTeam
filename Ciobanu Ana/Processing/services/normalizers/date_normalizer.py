from normalizer_strategy import Normalizer

class DateNormalizer(Normalizer):
    """Normalization strategy for dates."""

    def normalize(self, date_string):
        return