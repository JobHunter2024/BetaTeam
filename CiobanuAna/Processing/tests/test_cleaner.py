import pytest
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
from CiobanuAna.Processing.services.cleaners.advanced_cleaner import AdvancedCleaner

@pytest.fixture
def basic_cleaner():
    return BasicCleaner()

@pytest.fixture
def advanced_cleaner():
    return AdvancedCleaner()

def test_remove_extra_spaces(basic_cleaner):
    text = "This    is    a   test."
    expected = "This is a test."
    assert basic_cleaner.remove_extra_spaces(text) == expected

def test_remove_special_characters(basic_cleaner):
    text = "Searching candidates for the following positions #java developer && #python developer !!!!URGENT"
    expected = "Searching candidates for the following positions java developer  python developer URGENT"
    assert basic_cleaner.remove_special_characters(text) == expected

def test_basic_cleaner_clean(basic_cleaner):
    text = "Test!  eliminate extra spaces && special #characters"
    expected = "Test eliminate extra spaces special characters"
    assert basic_cleaner.clean(text) == expected

def test_advanced_cleaner_lowercase(advanced_cleaner):
    text = "ALL Words TO Lower case"
    expected = "all words to lower case"
    assert advanced_cleaner.custom_clean(text) == expected


