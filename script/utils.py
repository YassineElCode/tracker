import os

import requests
from dotenv import load_dotenv

load_dotenv()

API_BASE_URL = os.getenv("API_BASE_URL", "http://127.0.0.1:8000")


def get_required_field(data, key):
    """Gets a field from a dictionary or raises ValueError if missing or empty."""
    value = data.get(key)
    if not value:
        raise ValueError(f"Missing or empty required field: '{key}'")
    return value


def _post_dates(endpoint, dates, city_name, silent=False):
    url = f"{API_BASE_URL}/api/{endpoint}"
    payload = {"newdatums": dates["newdatums"], "city": city_name}
    try:
        response = requests.post(url, json=payload, headers={"Accept": "application/json"})
        response.raise_for_status()
        print(f"Successfully posted dates for {city_name}.")
    except requests.exceptions.RequestException as e:
        if not silent:
            print(f"Failed to post dates to {url} for {city_name}: {e}")


def post_dates_to_api(dates, city_name):
    _post_dates("compare-datums", dates, city_name)


def post_dates_to_api_sbat(dates, city_name):
    _post_dates("compare-datums-sbat", dates, city_name, silent=True)


def get_user_data_from_api(user_id: int) -> dict:
    url = f"{API_BASE_URL}/api/user/{user_id}"
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching user data from API: {e}")
        return {}


def get_cities_from_api():
    url = f"{API_BASE_URL}/api/cities-sbat"
    try:
        response = requests.get(url, timeout=10)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching cities from API: {e}")
        return []
