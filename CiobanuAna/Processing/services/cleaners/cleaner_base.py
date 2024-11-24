from abc import ABC, abstractmethod
import re

class Cleaner(ABC):
    def clean(self, text: str) -> str:
        text = self.remove_special_characters(text)
        text = self.remove_extra_spaces(text)
        text = self.custom_clean(text)
        return text

    def remove_extra_spaces(self, text: str) -> str:
        return ' '.join(text.split())

    def remove_special_characters(self, text: str) -> str:
        return re.sub(r'[^\w\s]', '', text)

    @abstractmethod
    def custom_clean(self, text: str) -> str:
        pass
