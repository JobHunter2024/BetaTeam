import json
from CiobanuAna.Processing.models.job_listing import JobListing
from CiobanuAna.Processing.services.normalizers.job_title_normalizer import JobTitleNormalizer
from CiobanuAna.Processing.services.normalizers.company_name_normalizer import CompanyNameNormalizer
from CiobanuAna.Processing.services.normalizers.date_normalizer import DateNormalizer
from CiobanuAna.Processing.services.skills_extractor.language_skill_extractor import LanguageSkillExtractor
from CiobanuAna.Processing.services.processors.job_listing_processor import JobListingProcessor
from CiobanuAna.Processing.services.cleaners.basic_cleaner import BasicCleaner
from CiobanuAna.Processing.services.skills_extractor.skill_extractor_strategy import SkillExtractor
from CiobanuAna.Processing.services.skills_extractor.soft_skill_extractor import SoftSkillExtractor
from CiobanuAna.Processing.services.job_details_extractor.detail_extractor import JobDetailsExtractor
from CiobanuAna.Processing.services.skills_extractor.technical_skill_extractor import TechnicalSkillExtractor
import os
import json
# Directory where the original files are stored
input_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\evaluation\\job_listings"
output_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\evaluation\\processed_job_listings"
gpt_output_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\evaluation\\chatGPT_job_listings"
compare_results_folder = "C:\\Users\\Ana\\Documents\\GitHub\\BetaTeam\\CiobanuAna\\Processing\\services\\processors\\evaluation\\compare_results"


def compare_json(json1, json2, path=""):
    differences = {}

    for key in json1.keys():
        if key not in json2:
            differences[f"{path}.{key}" if path else key] = f"Key '{key}' is missing in the second JSON"
        else:
            value1 = json1[key]
            value2 = json2[key]

            if isinstance(value1, dict) and isinstance(value2, dict):
                nested_diff = compare_json(value1, value2, path=f"{path}.{key}" if path else key)
                differences.update(nested_diff)
            elif isinstance(value1, list) and isinstance(value2, list):
                diff_in_list = compare_lists(value1, value2, key)
                if diff_in_list:
                    differences[f"{path}.{key}" if path else key] = diff_in_list
            elif value1.lower() != value2.lower():
                differences[f"{path}.{key}" if path else key] = {
                    "json1": value1,
                    "json2": value2
                }

    for key in json2.keys():
        if key not in json1:
            differences[f"{path}.{key}" if path else key] = f"Key '{key}' is missing in the first JSON"

    return differences


def compare_lists(list1, list2, key):
    if key in ["programming_languages", "frameworks", "libraries"]:
        set1 = {item["skill_name"] for item in list1 if isinstance(item, dict) and "skill_name" in item}
        set2 = {item["skill_name"] for item in list2 if isinstance(item, dict) and "skill_name" in item}
    else:
        set1 = set(json.dumps(item, sort_keys=True) if isinstance(item, dict) else item.lower() for item in list1)
        set2 = set(json.dumps(item, sort_keys=True) if isinstance(item, dict) else item.lower() for item in list2)

    only_in_list1 = set1 - set2
    only_in_list2 = set2 - set1

    result = {}
    if only_in_list1:
        result["only_in_json1"] = list(only_in_list1)
    if only_in_list2:
        result["only_in_json2"] = list(only_in_list2)

    return result if result else None


def write_compare_results():
    # Compare JSONs
    for i in range(1,31):
        gpt_json = f"job_{i}_gpt.json"
        processed_json = f"processed_job_{i}.json"
        gpt_json_path = os.path.join(gpt_output_folder, gpt_json)
        processed_json_path = os.path.join(output_folder, processed_json)

        if not os.path.exists(processed_json_path) or not os.path.exists(gpt_json_path):
            print(f"Warning: {processed_json_path} or {gpt_json_path} does not exist. Skipping comparison.")
            continue
        try:        
            with open(processed_json_path, "r",encoding="utf-8") as f1:
                processed_json_data = json.load(f1)
            with open(gpt_json_path, "r",encoding="utf-8") as f2:
                gpt_json_data = json.load(f2)
        except Exception as e:
            print(f"Error loading JSON files: {e}")        

        differences = compare_json(gpt_json_data, processed_json_data)
        output_file = os.path.join(compare_results_folder, f"job_{i}_differences.json")
        with open(output_file, 'w', encoding="utf-8") as out:
            json.dump(differences, out, indent=4)


def process_job_listings():
    # Initialize normalizers and skill extractor
    job_title_normalizer = JobTitleNormalizer()
    company_name_normalizer = CompanyNameNormalizer()
    date_normalizer = DateNormalizer()
    language_skill_extractor = LanguageSkillExtractor()
    basic_cleaner = BasicCleaner()
    skill_extractor = SkillExtractor()
    soft_skill_extractor = SoftSkillExtractor()
    job_details = JobDetailsExtractor()
    technical_skill_extractor = TechnicalSkillExtractor()

    # Create a processor
    processor = JobListingProcessor(job_title_normalizer, company_name_normalizer, date_normalizer, 
                                    language_skill_extractor, basic_cleaner, skill_extractor, soft_skill_extractor, job_details, technical_skill_extractor)
# encoding="utf-8"
    for filename in os.listdir(input_folder):
         if filename.endswith(".json"):
            # Read the input file
            input_file = os.path.join(input_folder, filename)
            print("Input file", input_file)
            with open(input_file, "r", encoding="utf-8") as file:
                input_json = file.read()  # Read file as string 
            
            # Process the data
            try:
                processed_data = processor.process_job_listing(input_json)
                # Write the processed data to a new file
                output_file = os.path.join(output_folder, f"processed_{filename}")
                with open(output_file, "w", encoding="utf-8") as file:
                    file.write(processed_data)
            except Exception as e:
                print(f"Error processing {filename}", e)    


if __name__ == "__main__":
    # write_compare_results()
    process_job_listings()
         