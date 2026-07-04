<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = trim($_POST['rating'] ?? '');
    $category = trim($_POST['feedback-category'] ?? $_POST['category'] ?? '');
    $messageText = trim($_POST['feedback-message'] ?? $_POST['message'] ?? '');

    if ($rating === '' || $category === '' || $messageText === '') {
        $message = 'Please complete all feedback fields.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare('INSERT INTO feedback (user_id, rating, category, message, created_at) VALUES (?, ?, ?, ?, NOW())');
        if ($stmt) {
            $stmt->bind_param('isss', $_SESSION['user_id'], $rating, $category, $messageText);
            if ($stmt->execute()) {
                $message = 'Thank you! Your feedback has been submitted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Unable to submit your feedback. Please try again.';
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Unable to submit your feedback. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback | Ojo Local Government Area Security Incident Reporting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="feedback-page">
    <header class="site-header dashboard-header">
        <div class="container header-content">
            <a href="../index.html" class="logo">Ojo LGA Security</a>
            <nav class="main-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="dashboard.html">Dashboard</a></li>
                    <li class="nav-item"><a href="report.html">Report Incident</a></li>
                    <li class="nav-item"><a href="track.html">Track Reports</a></li>
                    <li class="nav-item"><a href="contact.html">Emergency Contacts</a></li>
                    <li class="nav-item"><a href="about.html">Profile</a></li>
                    <li class="nav-item"><a href="login.html">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard-shell feedback-shell">
        <section class="dashboard-hero feedback-hero" aria-labelledby="feedback-title">
            <div>
                <p class="eyebrow">Ojo LGA Community Voice</p>
                <h1 id="feedback-title">Share Your Feedback</h1>
                <p>Your experience helps us improve safety services, strengthen public trust, and better support residents across Ojo Local Government Area.</p>
            </div>
            <div class="hero-badge">
                <i class="fa-solid fa-comments"></i>
                <span>We value your input</span>
            </div>
        </section>

        <section class="feedback-grid">
            <form class="card feedback-form-card" action="#" method="post" aria-labelledby="feedback-form-title">
                <?php if ($message !== ''): ?>
                    <div class="form-error" style="margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <div class="section-heading">
                    <h2 id="feedback-form-title"><i class="fa-solid fa-pen-to-square"></i> Submit Feedback</h2>
                </div>

                <div class="form-group">
                    <label for="full-name">Full Name</label>
                    <input type="text" id="full-name" name="full-name" placeholder="Jane Doe" required>
                </div>

                <div class="form-group">
                    <label for="feedback-email">Email Address</label>
                    <input type="email" id="feedback-email" name="feedback-email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="report-id">Report ID (optional)</label>
                    <input type="text" id="report-id" name="report-id" placeholder="e.g. IR-104">
                </div>

                <div class="form-group">
                    <label>How would you rate your experience?</label>
                    <div class="star-rating" role="radiogroup" aria-label="Overall rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 stars"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars"><i class="fa-solid fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star"><i class="fa-solid fa-star"></i></label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="feedback-category">Feedback Category</label>
                    <select id="feedback-category" name="feedback-category" required>
                        <option value="">Select a category</option>
                        <option value="service-quality">Service Quality</option>
                        <option value="response-time">Response Time</option>
                        <option value="officer-conduct">Officer Conduct</option>
                        <option value="system-experience">System Experience</option>
                        <option value="suggestion">Suggestion</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="feedback-message">Feedback Message</label>
                    <textarea id="feedback-message" name="feedback-message" placeholder="Share your experience with the reporting platform, response team, or public safety service in Ojo LGA." required></textarea>
                </div>

                <div class="form-group">
                    <label>Would you recommend this service to other residents?</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="recommend-yes" name="recommend" value="yes" required>
                            <label for="recommend-yes">Yes</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="recommend-no" name="recommend" value="no">
                            <label for="recommend-no">No</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button primary-button">Submit Feedback</button>
                    <button type="reset" class="button secondary-button">Reset</button>
                </div>
            </form>

            <div class="feedback-side-panel">
                <section class="card testimonial-card" aria-labelledby="testimonials-title">
                    <div class="section-heading">
                        <h2 id="testimonials-title"><i class="fa-solid fa-quote-left"></i> Recent Testimonials</h2>
                    </div>
                    <div class="testimonial-list">
                        <article class="testimonial-item">
                            <p>“The reporting process was simple, and the response team kept me informed about the next steps.”</p>
                            <strong>— Maria L., Ojo Town</strong>
                        </article>
                        <article class="testimonial-item">
                            <p>“I felt supported throughout the process and appreciated the timely updates from the safety office.”</p>
                            <strong>— David R., Ajangbadi</strong>
                        </article>
                        <article class="testimonial-item">
                            <p>“The portal is clear, secure, and easy to use when I need to report a serious concern quickly.”</p>
                            <strong>— Sarah N., Okokomaiko</strong>
                        </article>
                    </div>
                </section>

                <section class="card faq-card" aria-labelledby="faq-title">
                    <div class="section-heading">
                        <h2 id="faq-title"><i class="fa-solid fa-circle-question"></i> FAQ</h2>
                    </div>
                    <div class="faq-list">
                        <details open>
                            <summary>How quickly will I receive a response?</summary>
                            <p>Most reports receive an acknowledgment within 24 hours, depending on urgency and the availability of response teams.</p>
                        </details>
                        <details>
                            <summary>Is my report kept confidential?</summary>
                            <p>Yes. Reports are only shared with authorized personnel involved in resolving the matter.</p>
                        </details>
                        <details>
                            <summary>Can I update my report later?</summary>
                            <p>Yes. You can track your report and provide additional details through the Ojo LGA tracking portal.</p>
                        </details>
                    </div>
                </section>
            </div>
        </section>
    </main>

    <footer class="site-footer dashboard-footer">
        <div class="container footer-content">
            <p>Ojo Local Government Area Security Incident Reporting System</p>
            <p>© 2026 Ojo LGA Public Safety Office</p>
        </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
