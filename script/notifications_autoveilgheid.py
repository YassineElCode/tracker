import time
import os
import json
import time
import random
import json
from datetime import datetime
from urllib.parse import urlparse
from camoufox.sync_api import Camoufox
import requests
import urllib3
from dotenv import load_dotenv
from scrapehtml import extract_dates_from_html
from utils import post_dates_to_api, get_cities_from_api

import sentry_sdk
load_dotenv()

sentry_sdk.init(
    dsn=os.getenv("SENTRY_DSN"),
    # Add data like request headers and IP for users,
    # see https://docs.sentry.io/platforms/python/data-management/data-collected/ for more info
    send_default_pii=True,
)


def make_post_request(payload, cookie_string):
    """Makes a POST request to the specified URL with the given payload and cookies."""
    post_url = "https://examencentrum-praktijk.autoveiligheid.be/TimeSelect/AjaxPartialTimeSelectNew"
    
    proxy = os.getenv("PROXY", "")
    proxies = {
        'http': proxy,
        'https': proxy
    }
    
    headers = {
        'Cookie': cookie_string
    }

    try:
        response = requests.post(post_url, data=payload, headers=headers, verify=False, proxies=proxies)
        response.raise_for_status()
        return response.text
    except requests.exceptions.RequestException as e:
        if 'response' in locals():
            print(f"Server returned status {response.status_code} with body:\n{response.text}")
        print(f"Failed to make POST request: {e}")
        raise


def create_session_and_get_cookies():
    """
    Opens browser, goes through the wizard steps, and returns session data.
    Returns a tuple of (data_id, cookie_string) or (None, None) if failed.
    """
    load_dotenv()
    naam = os.getenv("NAAM", "")
    voornaam = os.getenv("VOORNAAM", "")
    rrn = os.getenv("RRN", "")
    gbdatum = os.getenv("GBDATUM", "")
    tel = os.getenv("TEL", "")
    email = os.getenv("EMAIL", "")
    adres = os.getenv("ADRES", "")
    postcode = os.getenv("POSTCODE", "")

    # WIZARD STEP 3
    zeersteVRijbewijsDatum = os.getenv("ZEERSTE_V_RIJBEWIJS_DATUM", "")
    zhuidigVRijbewijsDatum = os.getenv("ZHUIDIG_V_RIJBEWIJS_DATUM", "")
    zhuidigVRijbewijsGeldigTot = os.getenv("ZHUIDIG_V_RIJBEWIJS_GELDIG_TOT", "")

    # Camoufox wil server, gebruiker en wachtwoord apart; in .env staat de
    # proxy als één url, dus die splitsen we hier uit.
    proxy_url = urlparse(os.getenv("PROXY", ""))
    proxy_config = {
        'server': f"{proxy_url.scheme}://{proxy_url.hostname}:{proxy_url.port}",
        'username': proxy_url.username or "",
        'password': proxy_url.password or "",
    } if proxy_url.hostname else None

    with Camoufox(os=["macos", "windows"], geoip=True, humanize=True, headless=True,
        proxy=proxy_config) as browser:
        page = browser.new_page()
        page.goto("https://examencentrum-praktijk.autoveiligheid.be/Afspraak/nieuw")

        # Wait for the first input to be visible
        def human_type(selector, text):
            delay = random.choice([111, 144, 187])
            page.locator(selector).type(text, delay=delay)

        # WIZARD STEP 1
        page.wait_for_selector("#naam")
        page.wait_for_selector("#submitButton")
        human_type("#naam", naam)
        human_type("#voornaam", voornaam)
        human_type("#rrn", rrn)
        human_type("#gbdatum", gbdatum)
        page.click('body')  # Click away to close the date picker
        human_type("#tel", tel)
        human_type("#email", email)
        human_type("#adres", adres)
        human_type("#postcode", postcode)
        page.wait_for_timeout(4000)
        page.click('body')  # Click away to close the date picker
        page.wait_for_timeout(1000)
        page.click("#submitButton")

        # WIZARD STEP 2
        page.wait_for_selector("#catBE2")
        print("wij zijn bij stap 2")
        page.click("#catBE2")
        page.click("button.m-2:nth-child(4)")
        page.wait_for_timeout(4000)

        # WIZARD STEP 3
        page.wait_for_selector("#voorwaardenCheck")
        print("wij zijn bij stap 3")
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
        page.wait_for_timeout(4000)
        page.click("button.btn-phone-50")
        
        page.wait_for_url("**/TijdSelectie", timeout=60000)
        print("wij zijn bij stap 4")
        current_url = page.url
        print("current_url: " + current_url)

        try:
            data_id = current_url.split('/')[-2]
        except IndexError:
            print(f"Could not extract dataId from URL: {current_url}")
            return None, None

        cookies = page.context.cookies()
        cookie_string = "; ".join([f"{cookie['name']}={cookie['value']}" for cookie in cookies])
        
        # Browser will be closed automatically when exiting the 'with' block
        return data_id, cookie_string


def process_cities_data(data_id, cookie_string):
    """
    Processes data extraction for all cities using the provided session data.
    """
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    try:
        cities = get_cities_from_api()
    except Exception as e:
        print(f"Error fetching cities from API: {e}")
        return
    
    while True:
        for city in cities:
            payload = {
                'selectedEcId': city['code'],
                'dataId': data_id
            }
            try:
                response_text = make_post_request(payload, cookie_string)
            except Exception as e:
                print(f"Error making POST request for city {city['name']}: {e}")
                continue  # Continue with next city instead of returning
                
            if response_text:
                extracted_dates = extract_dates_from_html(response_text)
                # Create logs directory and save data
                city_log_dir = os.path.join(os.path.dirname(__file__), 'logs', city['name'].lower())
                os.makedirs(city_log_dir, exist_ok=True)
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                file_path = os.path.join(city_log_dir, f'extracted_dates_{timestamp}.json')
                with open(file_path, 'w', encoding='utf-8') as f:
                    json.dump(extracted_dates, f, indent=4)
                post_dates_to_api(extracted_dates, city['name'])
        
        # Sleep based on current time
        current_hour = datetime.now().hour
        if 0 <= current_hour < 6:
            sleep_seconds = 300  # Sleep 5 minutes between midnight and 6 AM
        else:
            sleep_seconds = 60   # Sleep 1 minute otherwise
        time.sleep(sleep_seconds)
        return


def session_maker():
    """
    Main function that coordinates session creation and data processing.
    """
    # Create session and get cookies (browser will be closed after this)
    data_id, cookie_string = create_session_and_get_cookies()
    
    if data_id is None or cookie_string is None:
        print("Failed to create session or get cookies")
        return
    
    print(f"Successfully created session with data_id: {data_id}")
    
    # Process cities data using the session
    process_cities_data(data_id, cookie_string)


if __name__ == "__main__":
    a = 0
    while True:
        print("Starting session...")
        if a == 5:
            print("5 failed sessions, exiting...")
            break
        try:
            session_maker()
        except Exception as e:
            a += 1
            print(f"Session failed with error: {e} waiting 180s")
            time.sleep(180)
            continue
        print("Process ended. Restarting in 60 seconds...")
        time.sleep(60)