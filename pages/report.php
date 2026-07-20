<?php
session_start();
include '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Please log in to submit a report.';
    } else {
        $category = trim($_POST['category'] ?? $_POST['incident-category'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $incidentDate = trim($_POST['incident_date'] ?? $_POST['incident-time'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $emergency = trim($_POST['emergency'] ?? '');
        $evidencePath = '';

        if ($category === '' || $location === '' || $incidentDate === '' || $description === '' || $emergency === '') {
            $error = 'Please fill in all required fields before submitting your report.';
        } else {
            $uploadDir = dirname(__DIR__) . '/uploads';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                $error = 'Unable to create the uploads folder.';
            } else {
                if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['evidence']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'The uploaded evidence file could not be processed.';
                    } else {
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'mp4', 'mov', 'avi'];
                        $tmpName = $_FILES['evidence']['tmp_name'];
                        $originalName = basename($_FILES['evidence']['name']);
                        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                        if (!in_array($extension, $allowedExtensions, true)) {
                            $error = 'Unsupported file type. Please upload an image, video, PDF, or document.';
                        } else {
                            $safeFileName = uniqid('evidence_', true) . '.' . $extension;
                            $destination = $uploadDir . '/' . $safeFileName;

                            if (!move_uploaded_file($tmpName, $destination)) {
                                $error = 'Failed to save the uploaded evidence file.';
                            } else {
                                $evidencePath = 'uploads/' . $safeFileName;
                            }
                        }
                    }
                }
            }

            if ($error === '') {
                $stmt = $conn->prepare('INSERT INTO incident_reports (user_id, category, location, incident_date, description, evidence, emergency, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                if ($stmt) {
                    $status = 'Pending';
                    $stmt->bind_param('isssssss', $_SESSION['user_id'], $category, $location, $incidentDate, $description, $evidencePath, $emergency, $status);
                    if ($stmt->execute()) {
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error = 'Unable to save the report right now. Please try again.';
                    }
                    $stmt->close();
                } else {
                    $error = 'Unable to save the report right now. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Security Incident | Ojo Local Government Area Security Incident Reporting System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="../index.html" class="logo">Ojo LGA Security</a>
            <nav class="main-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="../index.html#features">Features</a></li>
                    <li class="nav-item"><a href="../index.html#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a href="../index.html#emergency">Emergency</a></li>
                    <li class="nav-item"><a href="../index.html#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="auth-page">
        <section class="auth-card report-card">
            <header class="auth-header">
                <a href="../index.html" class="auth-logo">Ojo LGA Security</a>
                <h1>Report a Security Incident</h1>
                <p>Use this form to report public safety concerns in Ojo Local Government Area so the appropriate responders can act quickly and responsibly.</p>
            </header>

            <form class="auth-form report-form" action="#" method="post" enctype="multipart/form-data" autocomplete="on">
                <?php if ($error !== ''): ?>
                    <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="report-form-grid">
            
<div class="form-info">
    <p><strong>Logged in as:</strong> Your registered account information will automatically be attached to this report. You only need to provide the incident details.</p>
</div>

                    <div class="form-group">
                        <label for="incident-category">Incident Category</label>
                        <select id="incident-category" name="incident-category" required>
                            <option value="">Select a category</option>
                            <option value="theft">Theft</option>
                            <option value="robbery">Robbery</option>
                            <option value="assault">Assault</option>
                            <option value="domestic-violence">Domestic Violence</option>
                            <option value="fire">Fire Outbreak</option>
                            <option value="cult-related-activity">Cult-related Activity</option>
                            <option value="kidnapping">Kidnapping</option>
                            <option value="missing-person">Missing Person</option>
                            <option value="suspicious">Suspicious Activity</option>
                            <option value="traffic-accident">Traffic Accident</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Incident Location</label>
                        <input type="text" id="location" name="location" placeholder="e.g. 24 Olojo Drive, Ojo Town" required>
                    </div>

                    <div class="form-group">
                        <label for="incident-time">Date and Time of Incident</label>
                        <input type="datetime-local" id="incident-time" name="incident-time" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Incident Description</label>
                        <textarea id="description" name="description" placeholder="Describe what happened in as much detail as possible, including nearby landmarks such as Iyana Iba Road, Ajangbadi, or Okokomaiko." required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="evidence">Upload Evidence</label>
                        <input type="file" id="evidence" name="evidence" accept="image/*,video/*,.pdf,.doc,.docx" multiple>
                    </div>

                    <div class="form-group full-width">
                        <label>Emergency?</label>
                        <div class="radio-group" role="radiogroup" aria-label="Emergency status">
                            <div class="radio-option">
                                <input type="radio" id="emergency-yes" name="emergency" value="yes" required>
                                <label for="emergency-yes">Yes</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="emergency-no" name="emergency" value="no">
                                <label for="emergency-no">No</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button primary-button auth-submit">Submit Report</button>
                    <button type="reset" class="button secondary-button">Reset</button>
                </div>
            </form>

            <p class="report-note">Confidentiality Notice: Your report will be handled with care and shared only with authorized responders where necessary to address the incident. Please provide only the information required for effective public safety support.</p>
        </section>
    </main>

    <script src="../js/script.js"></script>
</body>
</html>
