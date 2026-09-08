import random
import time
import os
import sys
from datetime import datetime
from camoufox import Camoufox
from utils import get_required_field, get_user_data_from_api
import sentry_sdk
from dotenv import load_dotenv

load_dotenv()

sentry_sdk.init(
    dsn=os.getenv("SENTRY_DSN"),
    # Add data like request headers and IP for users,
    # see https://docs.sentry.io/platforms/python/data-management/data-collected/ for more info
    send_default_pii=True,
   
)

addons = [os.getenv("ADDON_PATH", "")]

def auto_inschrijven(user_id: int, city_code: str):
    try:
        user_data = get_user_data_from_api(user_id)
        if not user_data:
            raise ValueError("Failed to get user data from API.")

        personal_info = get_required_field(user_data, "personal_info")
        achternaam = get_required_field(personal_info, "achternaam")
        voornaam = get_required_field(personal_info, "voornaam")
        rrn = get_required_field(personal_info, "rrn")
        gbdatum = get_required_field(personal_info, "gbdatum")
        tel = get_required_field(personal_info, "tel")
        email = get_required_field(personal_info, "email")
        adres = get_required_field(personal_info, "adres")
        postcode = get_required_field(personal_info, "postcode")

        license_info = get_required_field(user_data, "license_info")
        zeersteVRijbewijsDatum = get_required_field(license_info, "zeersteVRijbewijsDatum")
        zhuidigVRijbewijsDatum = get_required_field(license_info, "zhuidigVRijbewijsDatum")
        zhuidigVRijbewijsGeldigTot = get_required_field(license_info, "zhuidigVRijbewijsGeldigTot")

        preferences = user_data.get("preferences", {})
        start_date_pref = preferences.get("startDatum")
        end_date_pref = preferences.get("endDatum")
        start_time_pref = preferences.get("startUur")
        end_time_pref = preferences.get("endUur")
        with Camoufox(os=["macos", "windows"], humanize=True, headless=True, addons=addons) as browser:
            page = browser.new_page()

            page.goto("https://examencentrum-praktijk.autoveiligheid.be/Afspraak/nieuw")
            page.wait_for_timeout(1000)

            # Wait for the first input to be visible
            def human_type(selector, text):
                delay = random.choice([100, 150, 200])
                page.locator(selector).type(text, delay=delay)

            page.wait_for_selector("#naam")
            page.wait_for_selector("#submitButton")
            human_type("#naam", achternaam)
            human_type("#voornaam", voornaam)
            human_type("#rrn", rrn)
            human_type("#gbdatum", gbdatum)
            page.click('body')  # Click away to close the date picker
            human_type("#tel", tel)
            human_type("#email", email)
            human_type("#adres", adres)
            human_type("#postcode", postcode)
            page.wait_for_timeout(4000)
            page.click("#submitButton")

            # WIZARD STEP 2
            page.wait_for_selector("#catBE2")
            page.click("#catBE2")
            page.click("button.m-2:nth-child(4)")
            page.wait_for_timeout(4000)

            # WIZARD STEP 3
            page.wait_for_selector("#voorwaardenCheck")
            checkbox = page.locator("#voorwaardenCheck")
            modelrijbewijs = page.locator("#modelVRijbewijs")
            modelrijbewijs.select_option("M36")
            human_type("#zeersteVRijbewijsDatum", zeersteVRijbewijsDatum)
            page.click('body')
            human_type("#zhuidigVRijbewijsDatum", zhuidigVRijbewijsDatum)
            page.click('body')
            human_type("#zhuidigVRijbewijsGeldigTot", zhuidigVRijbewijsGeldigTot)
            page.click('body')
            checkbox.check()
            page.wait_for_timeout(2000)
            page.click("button.btn-phone-50")
            page.wait_for_url("**/TijdSelectie", timeout=60000)
            page.click(f"#ec{city_code}"); #click op deurne

            # Scrape the date range from the header to determine the correct year
            # The locator resolves to multiple spans, so we take the last one.
            page.wait_for_selector("div.card-header span.text-nowrap")
            date_range_text = page.locator("div.card-header span.text-nowrap").last.inner_text()
            clean_text = date_range_text.strip().strip('()')
            start_range_str, end_range_str = clean_text.split(' - ')
            range_start_date = datetime.strptime(start_range_str, "%d/%m/%Y")
            range_end_date = datetime.strptime(end_range_str, "%d/%m/%Y")

            # Loop through available dates to find a match
            date_list_items = page.locator("li.TimeSlotListItem")
            for item in date_list_items.all():
                date_str = item.locator("li.hover-pointer").inner_text().strip()  # "vr 01/08"

                # Check if date is in user's preferred range
                if is_date_in_range(date_str, start_date_pref, end_date_pref, range_start_date, range_end_date):
                    item.locator("li.hover-pointer").click()

                    # Loop through available times
                    time_buttons = item.locator("button.buttenAsLink")
                    for time_button in time_buttons.all():
                        time_str = time_button.inner_text().strip() # "HH:MM"

                        # Check if time is in user's preferred range
                        if is_time_in_range(time_str, start_time_pref, end_time_pref):
                            time_button.click()
                            page.wait_for_timeout(10000) # Wait for captcha
                            # TODO: Click confirmation button
                            page.click('#btnBevestigAfspraak')
                            print("ENROLLMENT_SUCCESS")
                            return

            print("ENROLLMENT_FAILED")
            return

    except Exception as e:
        print("ENROLLMENT_FAILED")
        print(f"An error occurred during automation for user {user_id}: {e}")
        # The absence of the success message will indicate failure to the Laravel job

def is_date_in_range(date_str, start_date_pref, end_date_pref, range_start_date, range_end_date):
    try:
        # date_str is like 'vr 01/08', so we split by space and take the date part
        date_part = date_str.split(' ')[-1] # "01/08"
        day, month = map(int, date_part.split('/'))

        # Determine correct year by checking against the scraped range
        slot_date = None
        date_with_start_year = datetime(range_start_date.year, month, day)

        if range_start_date <= date_with_start_year <= range_end_date:
            slot_date = date_with_start_year
        elif range_start_date.year != range_end_date.year:
            # If the years are different, check the end year as well
            date_with_end_year = datetime(range_end_date.year, month, day)
            if range_start_date <= date_with_end_year <= range_end_date:
                slot_date = date_with_end_year

        if not slot_date:
            return False # Could not determine a valid date in the range

        # Now check against user's preferences
        if not start_date_pref or not end_date_pref:
            return True # No preference set, so any valid date is fine

        start_pref = datetime.strptime(start_date_pref, "%d/%m/%Y")
        end_pref = datetime.strptime(end_date_pref, "%d/%m/%Y")

        return start_pref <= slot_date <= end_pref
    except (ValueError, IndexError):
        # Handle cases where the format is unexpected
        return False

def is_time_in_range(time_str, start_time_pref, end_time_pref):
    if not start_time_pref or not end_time_pref:
        return True # No preference set

    try:
        slot_time = datetime.strptime(time_str, "%H:%M").time()
        start_time = datetime.strptime(start_time_pref, "%H:%M").time()
        end_time = datetime.strptime(end_time_pref, "%H:%M").time()

        return start_time <= slot_time <= end_time
    except ValueError:
        return False # Invalid time format

if __name__ == "__main__":
    if len(sys.argv) < 3    :
        print("Error: No user ID or city name provided.")
        sys.exit(1)
    try:
        user_id = int(sys.argv[1])
        city_code = sys.argv[2]
        auto_inschrijven(user_id, city_code)
    except ValueError:
        print("user_id must be an integer, city_name must be a string")
        sys.exit(1)