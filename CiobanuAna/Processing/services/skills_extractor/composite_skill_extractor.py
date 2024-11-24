from skill_extractor_strategy import SkillExtractor

class CompositeSkillExtractor(SkillExtractor):
    """Combines multiple skill extraction strategies."""

    def __init__(self, extractors):
        self.extractors = extractors

    def extract_skills(self, description):
        pass