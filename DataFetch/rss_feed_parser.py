import requests
from bs4 import BeautifulSoup
import re
from datetime import datetime
import requests
import os
from CiobanuAna.Processing.services.skills_extractor.technical_skill_extractor import TechnicalSkillExtractor
from dotenv import load_dotenv

load_dotenv()

def extract_location(description):
    pattern = r'in\s([A-Za-z\s]+),\sRomania'
    match = re.search(pattern, description)
    
    if match:
        # Extract the city name from the match
        city = match.group(1).strip()
        return city, "Romania"
    else:
        return None

def extract_date(description):
    pattern = r'on\s([A-Za-z]+)\s(\d{1,2}),\s(\d{4})'
    
    match = re.search(pattern, description)
    
    if match:
        # Extract the month, day, and year from the match
        month_name = match.group(1)
        day = int(match.group(2))
        year = int(match.group(3))
        
        # Convert the month name to a number (e.g., "March" -> 3)
        try:
            month = datetime.strptime(month_name, "%B").month
        except ValueError:
            return None
        
        return month, day, year
    else:
        return None     

def parse_pub_date(pub_date_str):
    """ From <pubDate> format 'Mon, 06 Feb 2023 15:06:47 GMT' to YYYY-MM-DD """
    try:
        return datetime.strptime(pub_date_str, "%a, %d %b %Y %H:%M:%S %Z").date()
    except ValueError:
        return None   


def rss_fetch_and_store_events():
    API_STORE_EVENT_TRIPLE = os.getenv('API_STORE_EVENT_TRIPLE')
    RSS_FEED_URL = os.getenv('RSS_FEED_URL')
    rss_url = RSS_FEED_URL

    url = API_STORE_EVENT_TRIPLE
    technicalSkillExtractor = TechnicalSkillExtractor()
    # Fetch the raw RSS feed
    response = requests.get(rss_url)
    xml_content = response.text

    # Parse the XML content with BeautifulSoup
    soup = BeautifulSoup(xml_content, "lxml-xml")

    today = datetime.utcnow().date() 
    count = 0

    for item in soup.find_all("item"):
        pub_date_str = item.find("pubDate").text
        pub_date = parse_pub_date(pub_date_str)

        if pub_date != today:
            print(f"Event date is not today: {pub_date} {today}")
            continue  
        else:
            print(f"Event date is today: {pub_date} {today}")

        description = item.find("description").text

        if "online" in description.lower():
            isOnline = True
        else:
            isOnline = False
            if "Romania" in description:
                result = extract_location(description)
                if result:
                    city, country = result
                else:
                    continue
            else:
                continue

        count +=1
        title = item.find("title").text

        resultDate = extract_date(description)
        if resultDate:
            month, day, year = resultDate
        else:
            continue

        # Extract all <category> fields
        try:
            categories = [category.text for category in item.find_all("category")]
            topic_category_details = technicalSkillExtractor.query_skill(categories[0])
            if topic_category_details["skill_type"] == "Programming Language":
                topic_category = "Programming Language"
            elif topic_category_details["skill_type"] == "Framework":
                topic_category = "Framework"
            elif topic_category_details["skill_type"] == "Library":
                topic_category = "Library"
            else:
                topic_category = "Topic"

            event = {
            "eventTitle": title,
            "isOnline": str(isOnline),
            "city": city if not isOnline else None,
            "country": country if not isOnline else None,
            "eventDate": f"{day}-{month}-{year}",
            "eventURL": item.find("link").text, 
            "topic": categories[0],
            "eventType": categories[1],
            "topicCategory": topic_category,
            "topicCategoryDetails": topic_category_details
            }
            print(event)
            response = requests.post(url, json=event)  # Send JSON data
            print(response.json())
            
        except Exception as e:
            print(e)
