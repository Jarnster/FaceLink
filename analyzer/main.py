import cv2
import face_recognition
import numpy as np
import requests
import os
import time

## CONSTANTS/CONFIGURATION ##

# General
LOCATION_ID = "MAIN_ROOM"

# API URLs
API_HOST = (
    "http://10.10.1.38"  # Replace with your webserver URL, hosting the controller
)

API_GET_KNOWN_FACES = API_HOST + "/api/get_known_entities.php"  # Without trailing slash

API_GET_TASKS = API_HOST + "/api/get_analyzer_tasks.php"  # Without trailing slash
API_UPLOAD_RESULT = API_HOST + "/api/upload_task_result.php"  # Without trailing slash
API_IMG_CONTENTS = API_HOST + "/api/uploads"  # Without trailing slash
IMAGE_FOLDER = "analyzer_images"  # Local storage for downloaded images
ANALYZER_TASK_THREAD_INTERVAL = 1  # Base time interval in seconds
MAX_SLEEP_MULTIPLIER = 5  # Maximum sleep increase factor

# Ensure the directory exists
os.makedirs(IMAGE_FOLDER, exist_ok=True)


def load_known_faces():
    known_face_encodings = []
    known_face_names = []
    known_faces = {}

    try:
        response = requests.get(API_GET_KNOWN_FACES, timeout=5)
        response.raise_for_status()
        face_data = response.json()

        if not isinstance(face_data, list) or len(face_data) == 0:
            print("No known faces found. Retrying in next cycle...")
            return [], []

        for face in face_data:
            face_id = face.get("id")
            face_name = face.get("name")
            if face_id and face_name:
                known_faces[face_id] = f"face_{face_id}.jpg"

    except requests.RequestException as e:
        print(f"Error fetching known faces: {e}")
        return [], []

    print("Loaded known entities:", known_faces)

    for name, path in known_faces.items():
        image_url = f"{API_IMG_CONTENTS}/{path}"
        image_path = os.path.join(IMAGE_FOLDER, path)

        try:
            img_data = requests.get(image_url).content
            with open(image_path, "wb") as f:
                f.write(img_data)

            img = face_recognition.load_image_file(image_path)
            encoding = face_recognition.face_encodings(img)
            if encoding:
                known_face_encodings.append(encoding[0])
                known_face_names.append(name)
        except Exception as e:
            print(f"Error processing face image {path}: {e}")

    return known_face_encodings, known_face_names


def process_analyzer_tasks(known_face_encodings, known_face_names):
    new_tasks = False
    try:
        response = requests.get(API_GET_TASKS, timeout=5).json()
        for task in response:
            if task != "error" and task["is_done"] == 0 and task["in_progress"] == 0:
                new_tasks = True
                filename = task["filename"]
                image_url = f"{API_IMG_CONTENTS}/{filename}"
                image_path = os.path.join(IMAGE_FOLDER, filename)

                try:
                    img_data = requests.get(image_url).content
                    with open(image_path, "wb") as f:
                        f.write(img_data)

                    img = cv2.imread(image_path)
                    rgb_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

                    face_locations = face_recognition.face_locations(rgb_img)
                    face_encodings = face_recognition.face_encodings(
                        rgb_img, face_locations
                    )
                    recognized_name = 0  # Default = unknown = 0

                    for face_encoding in face_encodings:
                        matches = face_recognition.compare_faces(
                            known_face_encodings, face_encoding, tolerance=0.6
                        )
                        if True in matches:
                            first_match_index = matches.index(True)
                            recognized_name = known_face_names[first_match_index]
                            break  # Stop at first match

                    payload = {
                        "task_id": task["id"],
                        "recognized_known_entity_id": recognized_name,
                        "location_id": LOCATION_ID,
                    }
                    requests.post(API_UPLOAD_RESULT, json=payload)

                    print(f"Processed {filename}: {recognized_name}")
                except Exception as e:
                    print(f"Error processing task {task['id']}: {e}")
    except requests.RequestException as e:
        print(f"Error fetching analyzer tasks: {e}")

    return new_tasks


if __name__ == "__main__":
    sleep_time = ANALYZER_TASK_THREAD_INTERVAL
    while True:
        print("Refreshing known entities...")
        known_face_encodings, known_face_names = load_known_faces()

        print("Checking for new analyzer tasks...")
        new_task_found = process_analyzer_tasks(known_face_encodings, known_face_names)

        if new_task_found:
            sleep_time = ANALYZER_TASK_THREAD_INTERVAL  # Reset sleep time on new task
        else:
            sleep_time = min(
                sleep_time * 2, ANALYZER_TASK_THREAD_INTERVAL * MAX_SLEEP_MULTIPLIER
            )

        print(f"Sleeping for {sleep_time} seconds...")
        time.sleep(sleep_time)
