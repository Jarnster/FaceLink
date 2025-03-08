# FaceLink

This is one of my first open-source releases. Please add a ⭐ if you like the idea of this project or if you like to use it.

FaceLink is a powerful face recognition system designed with a modular approach, separating its functionality into three distinct components: **Analyzer, Controller, and Frontend**. This architecture makes it highly scalable, allowing resource-intensive backend processing while enabling lightweight frontend devices like Raspberry Pi to handle face capture and transmission.

## Features
- **Three-Part System**: Dedicated components for face analysis, control, and data capture.
- **Multi-Location Deployment**: Use multiple frontend devices for user position tracking.
- **Advanced Face Analysis**: Backend processing for facial recognition and classification.
- **Admin Control Panel**: Manage face detection tasks, review entities, and analyze statistics.
- **REST API**: Seamless integration with other applications and systems.

## System Components
### `/Analyzer`
- Handles computationally heavy face analysis.
- Processes incoming face data from frontend devices.
- Communicates with the Controller for identification and analysis.

### `/Controller`
- Central backend for managing face recognition tasks.
- Hosts the Admin UI panel and API for monitoring and configuration.
- Designed to handle multiple lightweight frontend devices efficiently.

### `/Frontend`
- Runs on face detection devices to capture and send images to the Controller API.
- Can be deployed on lightweight devices such as Raspberry Pi, making it an ideal choice for scalable and cost-effective setups.

## Installation & Setup

### 1. Database Setup
- Host a MySQL/MariaDB database.
- Import `facelink.sql` into the database.
- *(Optional)* Change the admin password hash in the database using `password_hash()`.

### 2. Configure the Controller
- Open `/controller/includes/config.php` and update the settings accordingly.
- Deploy `/controller/` on a PHP-capable web server (ensure a recent PHP version is used).

### 3. Configure the Analyzer
- Open `/analyzer/main.py` and set the webserver URL.
- Assign a unique `LOCATION_ID`.
- Run `/analyzer/main.py` to start face analysis.

### 4. Deploy the Frontend
- Set the API endpoint to your webserver in `/frontend/index.html`
- Host or open `/frontend` on your detection devices (such as Raspberry Pi).

## Getting Started

1. **Admin Login:**
   - Access the Controller’s admin panel via a web browser.
   - Log in with admin credentials.

2. **Create Entities:**
   - Register known entities in the system.

3. **Start Face Detection:**
   - Open the `/frontend` page, activate the camera, and capture an image.

4. **View Results:**
   - Navigate to the admin panel and check:
     - **Registered Entities**: Verified individuals.
     - **Unidentified Entities**: Unknown detections.
     - **Locations**: Statistics per device.

## Contributing
We welcome all contributions! Feel free to submit issues, feature requests, or pull requests to improve FaceLink.

## Contact & Support
For questions or support, open an issue or reach out via GitHub.
