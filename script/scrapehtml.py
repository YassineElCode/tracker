from bs4 import BeautifulSoup
import re

def extract_dates_from_html(html_content):
    soup = BeautifulSoup(html_content, "html.parser")

    results = []
    main_div = soup.find("div", id="timeSelectCollapse")
    if not main_div:
        return {"newdatums": results}

    # Find all list items that represent a single day's entry
    for day_li in main_div.find_all("li", class_="TimeSlotListItem"):
        # Extract the date from the first child `li`
        date_info_li = day_li.find("li", class_="hover-pointer")
        if not date_info_li:
            continue

        data_target = date_info_li.get("data-target")
        if not data_target:
            continue
        
        # Extract date from data-target, e.g. "#TimeSlotCard19012026"
        m = re.search(r"TimeSlotCard(\d{2})(\d{2})(\d{4})", data_target)
        if not m:
            continue
        day, month, year = m.groups()
        formatted_date = f"{day}/{month}/{year}"

        # Get the text inside the <span>
        span = date_info_li.find("span", class_="TimeSlotListCardChildText")
        text_value = span.get_text(strip=True) if span else ""

        # Extract the times from the second child `li`
        times = []
        time_slot_group = day_li.find("li", class_="TimeSlotListGroup")
        if time_slot_group:
            buttons = time_slot_group.find_all("button", class_="buttenAsLink")
            for button in buttons:
                times.append(button.get_text(strip=True))

        results.append({"date": formatted_date, "text": text_value, "times": times})

    return {"newdatums": results}
