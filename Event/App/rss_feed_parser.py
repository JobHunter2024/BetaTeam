import requests
from bs4 import BeautifulSoup
import re
from datetime import datetime
import requests
import os

API_STORE_EVENT_TRIPLE = os.getenv('API_STORE_EVENT_TRIPLE')
RSS_FEED_URL = os.getenv('RSS_FEED_URL')

url = API_STORE_EVENT_TRIPLE

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

rss_url = RSS_FEED_URL

# Fetch the raw RSS feed
response = requests.get(rss_url)
xml_content = response.text

# Parse the XML content with BeautifulSoup
soup = BeautifulSoup(xml_content, "lxml-xml")
count = 0

for item in soup.find_all("item"):
    description = item.find("description").text

    if "online" in description.lower():
        isOnline = True
    else:
        isOnline = False
        if "Romania" in description:
            result = extract_location(description)
            if result:
                city, country = result
                print(f"City: {city}, Country: {country}")
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
    categories = [category.text for category in item.find_all("category")]

    event = {
    "eventTitle": title,
    "isOnline": str(isOnline),
    "city": city if not isOnline else None,
    "country": country if not isOnline else None,
    "eventDate": f"{day}-{month}-{year}",
    "topic": categories[0],
    "eventType": categories[1],
    }
    print(event)
    response = requests.post(url, json=event)  # Send JSON data

    print(response.json())