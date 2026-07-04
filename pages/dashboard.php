<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['full_name'] ?? 'Resident';
$reports = [];
$totalReports = 0;
$pendingReports = 0;
$resolvedReports = 0;

$stmt = $conn->prepare('SELECT report_id, category, location, incident_date, status, created_at FROM incident_reports WHERE user_id = ? ORDER BY created_at DESC');
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    $stmt->close();
}

foreach ($reports as $report) {
    $totalReports++;
    if (strcasecmp($report['status'], 'Pending') === 0) {
        $pendingReports++;
    }
    if (strcasecmp($report['status'], 'Resolved') === 0) {
        $resolvedReports++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Ojo Local Government Area Security Incident Reporting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-page">
    <header class="site-header dashboard-header">
        <div class="container header-content">
            <a href="../index.html" class="logo">Ojo LGA Security</a>
            <nav class="main-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="dashboard.php" class="active-nav">Dashboard</a></li>
                    <li class="nav-item"><a href="report.php">Report Incident</a></li>
                    <li class="nav-item"><a href="track.php">Track Reports</a></li>
                    <li class="nav-item"><a href="contact.html">Emergency Contacts</a></li>
                    <li class="nav-item"><a href="about.html">Profile</a></li>
                    <li class="nav-item"><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard-shell">
        <section class="dashboard-hero" aria-labelledby="dashboard-title">
            <div>
                <p class="eyebrow">Ojo LGA Public Safety Command Center</p>
                <h1 id="dashboard-title">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
                <p>Monitor local security reports, review recent updates, and support safer communities across Ojo LGA.</p>
            </div>
            <div class="hero-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Live monitoring</span>
            </div>
        </section>

        <section class="dashboard-grid">
            <aside class="sidebar" aria-label="Quick actions and alerts">
                <div class="sidebar-card">
                    <h2><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
                    <ul class="quick-actions">
                        <li><a href="report.html"><i class="fa-solid fa-plus"></i> Report New Incident</a></li>
                        <li><a href="track.html"><i class="fa-solid fa-magnifying-glass"></i> Track My Reports</a></li>
                        <li><a href="contact.html"><i class="fa-solid fa-phone"></i> Emergency Contacts</a></li>
                        <li><a href="feedback.html"><i class="fa-solid fa-comment-dots"></i> Feedback</a></li>
                        <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
                    </ul>
                </div>

                <div class="sidebar-card">
                    <h2><i class="fa-solid fa-bell"></i> Recent Updates</h2>
                    <ul class="notification-list">
                        <li>
                            <span class="notification-dot"></span>
                            <div>
                                <strong>New report received</strong>
                                <p>Suspicious activity near Olojo Drive was logged 12 minutes ago.</p>
                            </div>
                        </li>
                        <li>
                            <span class="notification-dot"></span>
                            <div>
                                <strong>Status changed</strong>
                                <p>Case #IR-104 is now under review by the Ojo LGA response team.</p>
                            </div>
                        </li>
                        <li>
                            <span class="notification-dot"></span>
                            <div>
                                <strong>Reminder</strong>
                                <p>Please review the latest public safety advisory for your area.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </aside>

            <div class="main-content">
                <section class="summary-grid" aria-label="Incident summary">
                    <article class="summary-card">
                        <div class="summary-icon blue">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <h3><?php echo $totalReports; ?></h3>
                        <p>Total Reports Submitted</p>
                    </article>

                    <article class="summary-card">
                        <div class="summary-icon gold">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3><?php echo $pendingReports; ?></h3>
                        <p>Reports Under Review</p>
                    </article>

                    <article class="summary-card">
                        <div class="summary-icon green">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <h3><?php echo $resolvedReports; ?></h3>
                        <p>Resolved Incidents</p>
                    </article>

                    <article class="summary-card">
                        <div class="summary-icon purple">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>
                        <h3>6</h3>
                        <p>Emergency Contacts</p>
                    </article>
                </section>

                <section class="card" aria-labelledby="reports-title">
                    <div class="section-heading">
                        <h2 id="reports-title"><i class="fa-solid fa-table-list"></i> Recent Ojo LGA Reports</h2>
                        <a href="track.html" class="text-link">View all</a>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Report ID</th>
                                    <th>Incident Category</th>
                                    <th>Location</th>
                                    <th>Date Reported</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="5">You have not submitted any report yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                        $statusClass = 'pending';
                                        if (strcasecmp($report['status'], 'Resolved') === 0) {
                                            $statusClass = 'resolved';
                                        } elseif (strcasecmp($report['status'], 'Pending') !== 0) {
                                            $statusClass = 'in-progress';
                                        }
                                        ?>
                                        <tr>
                                            <td>#IR-<?php echo (int) $report['report_id']; ?></td>
                                            <td><?php echo htmlspecialchars($report['category']); ?></td>
                                            <td><?php echo htmlspecialchars($report['location']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($report['incident_date']); ?><br>
                                                <small>Submitted: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($report['created_at']))); ?></small>
                                            </td>
                                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
