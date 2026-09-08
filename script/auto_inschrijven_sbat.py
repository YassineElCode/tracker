import sys

from utils import get_required_field, get_user_data_from_api
from utils_sbat import make_session_sbat_login, enroll_in_exam


def auto_inschrijven_sbat(user_id: int, id_afspraak: int):
    user_data = get_user_data_from_api(user_id)
    if not user_data:
        raise ValueError("Failed to get user data from API.")

    personal_info = get_required_field(user_data, "personal_info")
    sbat_email = get_required_field(personal_info, "sbat_email")
    sbat_password = get_required_field(personal_info, "sbat_password")

    token = make_session_sbat_login(sbat_email, sbat_password)

    response = enroll_in_exam(token, id_afspraak, personal_info)
    if response.status_code != 200:
        raise ValueError(f"Failed to enroll in exam (status {response.status_code}).")

    print("ENROLLMENT_SUCCESS")


if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Usage: auto_inschrijven_sbat.py <user_id> <city_code> <id_afspraak>")
        sys.exit(1)

    try:
        user_id = int(sys.argv[1])
        id_afspraak = int(sys.argv[3])
    except ValueError:
        print("Error: all arguments must be integers.")
        sys.exit(1)

    try:
        auto_inschrijven_sbat(user_id, id_afspraak)
    except ValueError as e:
        print("ENROLLMENT_FAILED")
        print(f"Error for user {user_id}: {e}")
        sys.exit(1)
