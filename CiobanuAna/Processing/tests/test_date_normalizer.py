import pytest
from CiobanuAna.Processing.services.normalizers.date_normalizer import DateNormalizer

@pytest.fixture
def date_normalizer():
    return DateNormalizer()

def test_normalize_date_us_format(date_normalizer):
    date = "2023-11-03"
    expected_result = "03/11/2023"
    assert date_normalizer.normalize(date) == expected_result

def test_normalize_date_standard_format(date_normalizer):
    date = "03/11/2023"
    expected_result = "03/11/2023"
    assert date_normalizer.normalize(date) == expected_result

def test_normalize_date_text_format(date_normalizer):
    date = "November 22, 2021"
    expected_result = "22/11/2021"
    assert date_normalizer.normalize(date) == expected_result

def test_normalize_date_invalid_format(date_normalizer):
    date = "invalid date"
    with pytest.raises(ValueError):
        date_normalizer.normalize(date)