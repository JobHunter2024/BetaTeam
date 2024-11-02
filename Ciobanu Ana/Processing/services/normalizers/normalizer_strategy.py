from abc import ABC, abstractmethod


class Normalizer(ABC):
    """Abstract base class for normalization strategies."""

    @abstractmethod
    def normalize(self, value):
        """Normalize a given value."""
        pass