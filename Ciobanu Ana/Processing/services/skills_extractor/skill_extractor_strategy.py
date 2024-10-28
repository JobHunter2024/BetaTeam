from abc import ABC, abstractmethod

class SkillExtractor(ABC):
    """Abstract class for skill extraction strategies."""

    @abstractmethod
    def extract_skills(self, description):
        """Extract skills from the job description."""
        pass
