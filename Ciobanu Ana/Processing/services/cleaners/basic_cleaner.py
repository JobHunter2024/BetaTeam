from .cleaner_base import Cleaner

class BasicCleaner(Cleaner):
    def custom_clean(self, text: str) -> str:
        return text
