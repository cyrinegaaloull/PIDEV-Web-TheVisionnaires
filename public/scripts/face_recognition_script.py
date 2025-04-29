import os
import cv2
import face_recognition
import numpy as np
from PIL import Image

def load_known_faces(folder_path):
    known_faces = []
    known_names = []
    print(f"PYTHON: Loading known faces from: {folder_path}") # Added logging
    for file_name in os.listdir(folder_path):
        if file_name.lower().endswith((".jpg", ".jpeg", ".png")):
            name = os.path.splitext(file_name)[0]
            image_path = os.path.join(folder_path, file_name)
            try:
                face_image = Image.open(image_path)
                if face_image.mode == 'RGBA':
                    background = Image.new('RGB', face_image.size, (255, 255, 255))
                    background.paste(face_image, mask=face_image.split()[3])
                    face_image = background
                elif face_image.mode != 'RGB':
                    face_image = face_image.convert('RGB')
                face_image_np = np.array(face_image)
                if face_image_np.dtype != np.uint8:
                    face_image_np = face_image_np.astype(np.uint8)
                face_encodings = face_recognition.face_encodings(face_image_np)
                if len(face_encodings) == 0:
                    print(f"PYTHON: No face detected in {file_name}") # Added logging
                    continue
                known_faces.append(face_encodings[0])
                known_names.append(name)
                print(f"PYTHON: Face loaded: {name}") # Added logging
            except Exception as e:
                print(f"PYTHON: Error with {file_name}: {str(e)}") # Added logging
                continue
    return known_faces, known_names

def recognize_faces(known_faces, known_names):
    print("PYTHON: Starting face recognition...") # Added logging
    video_capture = cv2.VideoCapture(0)
    recognized_name = "Inconnu"

    if not video_capture.isOpened():
        print("PYTHON: Impossible d'accéder à la caméra") # Added logging
        return "CAMERA_ERROR"

    ret, frame = video_capture.read()
    video_capture.release()

    if not ret:
        print("PYTHON: Erreur lors de la capture de la frame") # Added logging
        return "CAPTURE_ERROR"

    rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    face_locations = face_recognition.face_locations(rgb_frame)
    face_encodings = face_recognition.face_encodings(rgb_frame, face_locations)

    if not face_encodings:
        print("PYTHON: No faces detected in the captured frame.") # Added logging
        print("NO_FACE_DETECTED")
        return "NO_FACE_DETECTED"

    for face_encoding in face_encodings:
        matches = face_recognition.compare_faces(known_faces, face_encoding, tolerance=0.6)
        name = "Inconnu"
        face_distances = face_recognition.face_distance(known_faces, face_encoding)
        best_match_index = np.argmin(face_distances)

        if matches[best_match_index]:
            name = known_names[best_match_index]
            recognized_name = name
            print(f"PYTHON: USERNAME_FOUND: {name}") # Ensure this is printed on success
            return recognized_name

    print("PYTHON: No matching face found.") # Added logging
    print("NO_FACE_DETECTED")
    return "NO_FACE_DETECTED"

if __name__ == "__main__":
    known_folder = os.path.dirname(os.path.abspath(__file__))
    known_faces, known_names = load_known_faces(known_folder)

    if len(known_faces) > 0:
        print(f"PYTHON: {len(known_faces)} faces loaded successfully") # Added logging
        recognize_faces(known_faces, known_names)
    else:
        print("PYTHON: Aucun visage valide trouvé - Vérifiez vos images") # Added logging
        print("NO_FACE_DETECTED")