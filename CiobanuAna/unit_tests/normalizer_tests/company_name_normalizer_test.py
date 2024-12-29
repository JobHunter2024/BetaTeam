import pytest
from CiobanuAna.Processing.services.normalizers.company_name_normalizer import CompanyNameNormalizer

@pytest.fixture
def company_name_normalizer():
    return CompanyNameNormalizer()

def test_normalize_company_name_special_characters(company_name_normalizer):
    company_name = "Amazon Development Center (Romania) S.R.L."
    expected_result = "Amazon Development Center Romania"
    assert company_name_normalizer.normalize(company_name) == expected_result

def test_normalize_company_name(company_name_normalizer):
    company_name = "Google LLC"
    expected_result = "Google"
    assert company_name_normalizer.normalize(company_name) == expected_result    

  
