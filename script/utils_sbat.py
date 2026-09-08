import json
import os
import traceback
from datetime import datetime

import requests

from utils import get_required_field


SBAT_API_BASE = "https://api.rijbewijs.sbat.be/praktijk/api"
SBAT_ORIGIN = "https://rijbewijs.sbat.be"

ATTEMPTS_MAP = {
    "oneOrTwo": 1,
    "threeOrMore": 2,
}

TEMPORARY_TYPE_FIXED_DURATION = {
    "Model 3": 12,
    "Stageattest": 12,
}


def _sbat_headers(token=None):
    headers = {
        "User-Agent": "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:141.0) Gecko/20100101 Firefox/141.0",
        "Accept": "application/json, text/plain, */*",
        "Content-Type": "application/json",
        "Origin": SBAT_ORIGIN,
        "Referer": f"{SBAT_ORIGIN}/",
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"
    return headers


def make_session_sbat_login(email, password):
    response = requests.post(
        f"{SBAT_API_BASE}/user/authenticate",
        json={"username": email, "password": password},
        headers=_sbat_headers(),
    )
    if response.status_code != 200:
        raise ValueError(
            f"SBAT login failed (status {response.status_code}): {response.text[:200]}"
        )

    token = response.text.strip()
    if not token:
        raise ValueError("SBAT login returned empty token.")

    return token


def enroll_in_exam(token, id_afspraak, personal_info):
    theory_b = get_required_field(personal_info, "datum_slagen_theorieB")
    type_voorlopig = get_required_field(personal_info, "type_voorlopig_rijbewijs")
    temporary_b = get_required_field(personal_info, "afgiftedatum_voorlopig_rijbewijsB")
    hoeveelste_poging = get_required_field(personal_info, "hoeveelste_poging")

    selected_attempts = ATTEMPTS_MAP.get(hoeveelste_poging)
    if selected_attempts is None:
        raise ValueError(f"Unknown attempt type: '{hoeveelste_poging}'")

    if type_voorlopig.endswith(" maand"):
        duration = int(type_voorlopig.split()[0])
    elif type_voorlopig in TEMPORARY_TYPE_FIXED_DURATION:
        duration = TEMPORARY_TYPE_FIXED_DURATION[type_voorlopig]
    else:
        raise ValueError(f"Unknown temporary license type: '{type_voorlopig}'")

    theory_b_iso = datetime.strptime(theory_b, "%Y-%m-%d").strftime("%Y-%m-%dT%H:%M:%S.000Z")
    temporary_b_iso = datetime.strptime(temporary_b, "%Y-%m-%d").strftime("%Y-%m-%dT%H:%M:%S.000Z")

    additional_info = {
        "theoryB": theory_b_iso,
        "selectedTemporaryType": {
            "text": type_voorlopig,
            "canExpire": True,
            "duration": duration,
            "offset": 5,
            "theoryCanExpire": True,
            "warning": "Het geselecteerde type examen vereist een geldig voorlopig rijbewijs B afgegeven sinds meer dan 5 maanden.",
        },
        "temporaryB": temporary_b_iso,
        "selectedAttemptsOption": selected_attempts,
        "hasHadAdditionalLessons": True if selected_attempts == 2 else None,
    }

    payload = {
        "additionalInformation": json.dumps(additional_info),  # API expects stringified JSON
        "examId": id_afspraak,
        "examType": "E2",
        "licenseType": "B",
    }

    return requests.post(
        f"{SBAT_API_BASE}/exam/scheduleForExaminee",
        json=payload,
        headers=_sbat_headers(token),
    )


def get_available_exam_dates(token, exam_center_id, exam_center_name):
    proxy = os.environ.get("PROXY")
    proxies = {"http": proxy, "https": proxy} if proxy else None

    payload = {
        "examCenterId": exam_center_id,
        "licenseType": "B",
        "examType": "E2",
        "startDate": datetime.today().strftime("%Y-%m-%dT00:00"),
    }

    try:
        response = requests.post(
            f"{SBAT_API_BASE}/exam/available",
            json=payload,
            headers=_sbat_headers(token),
            proxies=proxies,
        )
        response.raise_for_status()

        data = response.json()
        if not isinstance(data, list):
            raise ValueError(f"Expected JSON list, got {type(data).__name__}: {data}")

        return {
            "newdatums": _transform_exam_slots(data),
            "city": exam_center_name,
        }
    except Exception as e:
        _log_error(e, exam_center_name, exam_center_id, payload,
                   response if "response" in dir() else None)
        raise


def _transform_exam_slots(raw_slots):
    if not raw_slots:
        return []

    grouped = {}
    for slot in raw_slots:
        dt = datetime.fromisoformat(slot["from"])
        date_key = dt.strftime("%d/%m/%Y")

        if date_key not in grouped:
            grouped[date_key] = {
                "date": date_key,
                "text": f"{dt.strftime('%a').lower()} {dt.strftime('%d/%m')}",
                "times": [dt.strftime("%H:%M")],
                "id_afspraak": slot["id"],
            }
        else:
            grouped[date_key]["times"].append(dt.strftime("%H:%M"))

    return list(grouped.values())


def _log_error(error, exam_center_name, exam_center_id, payload, response=None):
    error_dir = os.path.join(os.path.dirname(__file__), f"{exam_center_name}-errors")
    os.makedirs(error_dir, exist_ok=True)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    error_filepath = os.path.join(error_dir, f"error_{timestamp}.json")

    error_data = {
        "timestamp": datetime.now().isoformat(),
        "city": exam_center_name,
        "exam_center_id": exam_center_id,
        "error_type": type(error).__name__,
        "error_message": str(error),
        "traceback": traceback.format_exc(),
        "request_payload": payload,
    }

    if response is not None:
        error_data["response"] = {
            "status_code": response.status_code,
            "headers": dict(response.headers),
            "text": response.text[:1000],
        }

    try:
        with open(error_filepath, "w", encoding="utf-8") as f:
            json.dump(error_data, f, indent=4, ensure_ascii=False)
        print(f"Error logged to: {error_filepath}")
    except Exception as file_error:
        print(f"Failed to write error log: {file_error}")

    print(f"Failed to get available exam dates for {exam_center_name} (ID: {exam_center_id}): {error}")
