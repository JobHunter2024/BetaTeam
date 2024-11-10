from .cleaner_base import Cleaner

class AdvancedCleaner(Cleaner):
    def custom_clean(self, text: str) -> str:
        return text.lower()
