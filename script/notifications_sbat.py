import json
import os
import random
import time
from datetime import datetime

from dotenv import load_dotenv

from utils import post_dates_to_api_sbat
from utils_sbat import make_session_sbat_login, get_available_exam_dates

EXAM_CENTERS = {
    "Sint-Denijs-Westrem": 1,
    "Brakel": 7,
    "Eeklo": 8,
    "Erembodegem": 9,
    "Sint-Niklaas": 10,
}

LOGS_DIR = os.path.join(os.path.dirname(__file__), "logs")


def get_poll_interval():
    now = datetime.now()
    hour, minute = now.hour, now.minute

    if 0 <= hour < 7:
        return 10 * 60
    if (hour == 7 and minute < 30) or (16 <= hour < 17):
        return 30
    return 60


def log_response(city, data):
    city_log_dir = os.path.join(LOGS_DIR, city.lower())
    os.makedirs(city_log_dir, exist_ok=True)

    now = datetime.now()
    date_str = now.strftime("%Y-%m-%d")
    timestamp = now.strftime("%Y-%m-%d %H:%M:%S")
    filepath = os.path.join(city_log_dir, f"{date_str}.json")

    if os.path.exists(filepath):
        with open(filepath, "r", encoding="utf-8") as f:
            daily_log = json.load(f)
    else:
        daily_log = []

    daily_log.append({"timestamp": timestamp, "data": data})

    with open(filepath, "w", encoding="utf-8") as f:
        json.dump(daily_log, f, indent=4)


def poll_exam_centers(token):
    for city, center_id in EXAM_CENTERS.items():
        available_dates = get_available_exam_dates(token, center_id, city)
        log_response(city, available_dates)
        post_dates_to_api_sbat(available_dates, city)


def main():
    load_dotenv()
    email = os.getenv("SBAT_EMAIL", "")
    password = os.getenv("SBAT_PSSWD", "")

    token = make_session_sbat_login(email, password)

    while True:
        poll_exam_centers(token)
        time.sleep(get_poll_interval())


if __name__ == "__main__":
    while True:
        try:
            main()
        except Exception as e:
            print(f"Process crashed: {e}")
        print("Restarting in 60 seconds...")
        time.sleep(60)
