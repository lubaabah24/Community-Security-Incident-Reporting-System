<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$reports = [];

$stmt = $conn->prepare('SELECT report_id, category, location, incident_date, description, emergency, status, created_at, updated_at FROM incident_reports WHERE user_id = ? ORDER BY created_at DESC');
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Report | Ojo Local Government Area Security Incident Reporting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="track-page">
    <header class="site-header dashboard-header">
        <div class="container header-content">
            <a href="../index.html" class="logo">Ojo LGA Security</a>
            <nav class="main-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="dashboard.html">Dashboard</a></li>
                    <li class="nav-item"><a href="report.html">Report Incident</a></li>
                    <li class="nav-item"><a href="track.html" class="active-nav">Track Reports</a></li>
                    <li class="nav-item"><a href="contact.html">Emergency Contacts</a></li>
                    <li class="nav-item"><a href="about.html">Profile</a></li>
                    <li class="nav-item"><a href="login.html">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard-shell track-shell">
        <section class="dashboard-hero track-hero" aria-labelledby="track-title">
            <div>
                <p class="eyebrow">Ojo LGA Case Monitoring</p>
                <h1 id="track-title">Track Your Report</h1>
                <p>Search for a submitted report by report ID or email address and follow its progress through the Ojo LGA response process.</p>
            </div>
            <div class="hero-badge">
                <i class="fa-solid fa-location-dot"></i>
                <span>Live case updates</span>
            </div>
        </section>

        <section class="track-search card" aria-labelledby="search-title">
            <div class="section-heading">
                <h2 id="search-title"><i class="fa-solid fa-magnifying-glass"></i> Search Report</h2>
            </div>
            <form class="track-form">
                <div class="form-group">
                    <label for="report-id">Report ID</label>
                    <input type="text" id="report-id" name="report-id" placeholder="e.g. IR-104">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="resident@example.com">
                </div>
                <button type="button" class="button primary-button">Track Report</button>
            </form>
        </section>

        <section class="track-details-grid">
            <?php if (empty($reports)): ?>
                <article class="card report-card-detail" aria-labelledby="details-title">
                    <div class="section-heading">
                        <h2 id="details-title"><i class="fa-solid fa-file-contract"></i> Report Details</h2>
                    </div>
                    <p>You have not submitted any report yet.</p>
                </article>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <article class="card report-card-detail" aria-labelledby="details-title-<?php echo (int) $report['report_id']; ?>">
                        <div class="section-heading">
                            <h2 id="details-title-<?php echo (int) $report['report_id']; ?>"><i class="fa-solid fa-file-contract"></i> Report Details</h2>
                            <span class="status-badge <?php echo strcasecmp($report['status'], 'Resolved') === 0 ? 'resolved' : (strcasecmp($report['status'], 'Pending') === 0 ? 'pending' : 'in-progress'); ?>"><?php echo htmlspecialchars($report['status']); ?></span>
                        </div>
                        <div class="details-grid">
                            <div>
                                <p class="detail-label">Report ID</p>
                                <p class="detail-value">#IR-<?php echo (int) $report['report_id']; ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Incident Category</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['category']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Date Submitted</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['created_at']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Incident Location</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['location']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Current Status</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['status']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Emergency Status</p>
                                <p class="detail-value"><?php echo htmlspecialchars(strcasecmp($report['emergency'], 'yes') === 0 ? 'Yes' : 'No'); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Last Updated</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['updated_at']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Incident Date</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['incident_date']); ?></p>
                            </div>
                            <div>
                                <p class="detail-label">Description</p>
                                <p class="detail-value"><?php echo htmlspecialchars($report['description']); ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($reports)): ?>
                <article class="card progress-card" aria-labelledby="progress-title">
                    <div class="section-heading">
                        <h2 id="progress-title"><i class="fa-solid fa-spinner"></i> Progress Tracker</h2>
                    </div>
                    <div class="progress-steps">
                        <div class="step-item completed">
                            <span class="step-icon"><i class="fa-solid fa-check"></i></span>
                            <div>
                                <strong>Report Submitted</strong>
                                <p>Received and logged</p>
                            </div>
                        </div>
                        <div class="step-item completed">
                            <span class="step-icon"><i class="fa-solid fa-check"></i></span>
                            <div>
                                <strong>Under Review</strong>
                                <p>Verified by the Ojo LGA response team</p>
                            </div>
                        </div>
                        <div class="step-item active">
                            <span class="step-icon"><i class="fa-solid fa-hourglass-half"></i></span>
                            <div>
                                <strong>Investigation Underway</strong>
                                <p>Officer assigned and reviewing evidence</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <span class="step-icon"><i class="fa-solid fa-flag-checkered"></i></span>
                            <div>
                                <strong>Resolved</strong>
                                <p>Case closure pending confirmation</p>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?>
        </section>

        <section class="card timeline-card" aria-labelledby="timeline-title">
            <div class="section-heading">
                <h2 id="timeline-title"><i class="fa-solid fa-clock-rotate-left"></i> Status Timeline</h2>
            </div>
            <ul class="timeline">
                <li>
                    <div class="timeline-dot"></div>
                    <div>
                        <strong>2026-07-03</strong>
                        <p>Investigation initiated and evidence review started by the assigned team.</p>
                    </div>
                </li>
                <li>
                    <div class="timeline-dot"></div>
                    <div>
                        <strong>2026-07-02</strong>
                        <p>Report moved to under review after initial validation by Ojo LGA authorities.</p>
                    </div>
                </li>
                <li>
                    <div class="timeline-dot"></div>
                    <div>
                        <strong>2026-07-01</strong>
                        <p>Report submitted successfully and acknowledged by the reporting portal.</p>
                    </div>
                </li>
            </ul>
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
