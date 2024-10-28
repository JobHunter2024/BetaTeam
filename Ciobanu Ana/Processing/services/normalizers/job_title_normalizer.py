from normalizer_strategy import Normalizer

class JobTitleNormalizer(Normalizer):
    """Normalization strategy for job titles."""

    def normalize(self, title):
        return