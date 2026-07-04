<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['status'])) {
    $reportId = (int) $_POST['report_id'];
    $status = trim($_POST['status']);
    $allowedStatuses = ['Pending', 'Under Review', 'Resolved'];

    if ($reportId > 0 && in_array($status, $allowedStatuses, true)) {
        $stmt = $conn->prepare('UPDATE incident_reports SET status = ?, updated_at = NOW() WHERE report_id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $status, $reportId);
            if ($stmt->execute()) {
                $message = 'Report status updated successfully.';
            } else {
                $message = 'Unable to update report status.';
            }
            $stmt->close();
        } else {
            $message = 'Unable to update report status.';
        }
    } else {
        $message = 'Invalid report status update request.';
    }
}

$reports = [];
$totalReports = 0;
$pendingReports = 0;
$underReviewReports = 0;
$resolvedReports = 0;

$stmt = $conn->prepare('SELECT ir.report_id, u.full_name, ir.category, ir.location, ir.incident_date, ir.emergency, ir.status, ir.created_at FROM incident_reports ir LEFT JOIN users u ON ir.user_id = u.id ORDER BY ir.created_at DESC');
if ($stmt) {
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
    } elseif (strcasecmp($report['status'], 'Under Review') === 0) {
        $underReviewReports++;
    } elseif (strcasecmp($report['status'], 'Resolved') === 0) {
        $resolvedReports++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Community Security Incident Reporting</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <a href="../index.html" class="logo">Community Security</a>
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav" aria-label="Admin navigation">
                <a class="nav-link active" href="#"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-file-shield"></i><span>Manage Incident Reports</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-users"></i><span>Manage Users</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-images"></i><span>Evidence Uploads</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-phone-volume"></i><span>Emergency Contacts</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-comments"></i><span>Feedback Management</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-chart-line"></i><span>Reports & Analytics</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-gear"></i><span>System Settings</span></a>
                <a class="nav-link" href="#"><i class="fa-solid fa-user"></i><span>Profile</span></a>
                <a class="nav-link" href="login.html"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-title">
                    <h1>Dashboard</h1>
                    <p>Administration Control Center</p>
                </div>
                <div class="topbar-tools">
                    <label class="search-box" aria-label="Search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" placeholder="Search reports, users...">
                    </label>
                    <button class="icon-button" aria-label="Notifications"><i class="fa-solid fa-bell"></i></button>
                    <button class="icon-button" aria-label="Messages"><i class="fa-solid fa-envelope"></i></button>
                    <div class="topbar-profile">
                        <div class="avatar">LB</div>
                        <div>
                            <strong>Lubaabah</strong>
                            <span>Security Operations Administrator</span>
                            <span>Ojo Local Government Security Department</span>
                            <span>daudlubaabah@gmail.com</span>
                            <span>09138428622</span>
                        </div>
                    </div>
                    <a href="login.html" class="button primary-button small">Logout</a>
                </div>
            </header>

            <main class="admin-content">
                <?php if ($message !== ''): ?>
                    <div class="form-error" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <section class="stats-grid" aria-label="Dashboard statistics">
                    <article class="card stat-card">
                        <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                        <h3>1,248</h3>
                        <p>Total Registered Users</p>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-icon sky"><i class="fa-solid fa-file-lines"></i></div>
                        <h3><?php echo $totalReports; ?></h3>
                        <p>Total Incident Reports</p>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
                        <h3><?php echo $pendingReports; ?></h3>
                        <p>Pending Reports</p>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-icon gold"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h3><?php echo $underReviewReports; ?></h3>
                        <p>Reports Under Investigation</p>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-icon green"><i class="fa-solid fa-check-circle"></i></div>
                        <h3><?php echo $resolvedReports; ?></h3>
                        <p>Resolved Reports</p>
                    </article>
                    <article class="card stat-card">
                        <div class="stat-icon red"><i class="fa-solid fa-bell"></i></div>
                        <h3>9</h3>
                        <p>Emergency Alerts</p>
                    </article>
                </section>

                <section class="dashboard-grid">
                    <div class="content-column">
                        <section class="card table-card" aria-labelledby="incidents-title">
                            <div class="section-heading">
                                <h2 id="incidents-title"><i class="fa-solid fa-table-list"></i> Incident Management</h2>
                                <a href="#" class="text-link">View all</a>
                            </div>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Report ID</th>
                                            <th>Citizen Name</th>
                                            <th>Incident Category</th>
                                            <th>Location</th>
                                            <th>Date Submitted</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($reports)): ?>
                                            <tr>
                                                <td colspan="8">No incident reports found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($reports as $report): ?>
                                                <?php
                                                $statusClass = 'pending';
                                                if (strcasecmp($report['status'], 'Resolved') === 0) {
                                                    $statusClass = 'resolved';
                                                } elseif (strcasecmp($report['status'], 'Under Review') === 0) {
                                                    $statusClass = 'in-progress';
                                                }
                                                ?>
                                                <tr>
                                                    <td>#IR-<?php echo (int) $report['report_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($report['full_name'] ?? 'Unknown'); ?></td>
                                                    <td><?php echo htmlspecialchars($report['category']); ?></td>
                                                    <td><?php echo htmlspecialchars($report['location']); ?></td>
                                                    <td><?php echo htmlspecialchars($report['incident_date']); ?></td>
                                                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                                    <td><?php echo htmlspecialchars(strcasecmp($report['emergency'], 'yes') === 0 ? 'Yes' : 'No'); ?></td>
                                                    <td>
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="report_id" value="<?php echo (int) $report['report_id']; ?>">
                                                            <select name="status" onchange="this.form.submit()">
                                                                <option value="Pending" <?php echo strcasecmp($report['status'], 'Pending') === 0 ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="Under Review" <?php echo strcasecmp($report['status'], 'Under Review') === 0 ? 'selected' : ''; ?>>Under Review</option>
                                                                <option value="Resolved" <?php echo strcasecmp($report['status'], 'Resolved') === 0 ? 'selected' : ''; ?>>Resolved</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="card analytics-card" aria-labelledby="analytics-title">
                            <div class="section-heading">
                                <h2 id="analytics-title"><i class="fa-solid fa-chart-pie"></i> Analytics</h2>
                            </div>
                            <div class="analytics-grid">
                                <article class="mini-card">
                                    <h3>Incident Categories</h3>
                                    <p>54% Theft<br>21% Vandalism<br>25% Other</p>
                                </article>
                                <article class="mini-card">
                                    <h3>Monthly Reports</h3>
                                    <p>+18% vs last month</p>
                                </article>
                                <article class="mini-card">
                                    <h3>Resolution Rate</h3>
                                    <p>66% within 7 days</p>
                                </article>
                                <article class="mini-card">
                                    <h3>Response Time</h3>
                                    <p>Avg. 2.4 hours</p>
                                </article>
                            </div>
                        </section>

                        <section class="card evidence-card" aria-labelledby="evidence-title">
                            <div class="section-heading">
                                <h2 id="evidence-title"><i class="fa-solid fa-images"></i> Evidence Uploads</h2>
                            </div>
                            <div class="evidence-grid">
                                <article class="evidence-item">
                                    <div class="evidence-preview"><i class="fa-solid fa-image"></i></div>
                                    <h3>Scene Photo 1</h3>
                                    <p>Uploaded 2h ago</p>
                                </article>
                                <article class="evidence-item">
                                    <div class="evidence-preview"><i class="fa-solid fa-file-pdf"></i></div>
                                    <h3>Incident Report PDF</h3>
                                    <p>Uploaded 4h ago</p>
                                </article>
                                <article class="evidence-item">
                                    <div class="evidence-preview"><i class="fa-solid fa-video"></i></div>
                                    <h3>Surveillance Clip</h3>
                                    <p>Uploaded 6h ago</p>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside class="side-column">
                        <section class="card activity-card" aria-labelledby="activity-title">
                            <div class="section-heading">
                                <h2 id="activity-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent Activities</h2>
                            </div>
                            <ul class="activity-list">
                                <li><strong>Admin updated</strong><span>Report #IR-104 status</span></li>
                                <li><strong>New evidence uploaded</strong><span>For report #IR-118</span></li>
                                <li><strong>Feedback reviewed</strong><span>System experience complaint</span></li>
                                <li><strong>Emergency alert acknowledged</strong><span>District 02</span></li>
                            </ul>
                        </section>

                        <section class="card notification-card" aria-labelledby="notifications-title">
                            <div class="section-heading">
                                <h2 id="notifications-title"><i class="fa-solid fa-bell"></i> Notifications</h2>
                            </div>
                            <ul class="activity-list">
                                <li><strong>New incident submitted</strong><span>Suspicious activity in Oak Street</span></li>
                                <li><strong>High priority update</strong><span>Immediate review required</span></li>
                                <li><strong>Verification pending</strong><span>Evidence for #IR-121</span></li>
                            </ul>
                        </section>

                        <section class="card user-card" aria-labelledby="users-title">
                            <div class="section-heading">
                                <h2 id="users-title"><i class="fa-solid fa-user-gear"></i> User Management</h2>
                            </div>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jane Doe</td>
                                            <td>jane@example.com</td>
                                            <td>Citizen</td>
                                            <td><span class="status-badge resolved">Active</span></td>
                                            <td><a href="#" class="text-link">Edit</a></td>
                                        </tr>
                                        <tr>
                                            <td>Officer A. Brooks</td>
                                            <td>officer@example.com</td>
                                            <td>Investigator</td>
                                            <td><span class="status-badge in-progress">Active</span></td>
                                            <td><a href="#" class="text-link">Edit</a></td>
                                        </tr>
                                        <tr>
                                            <td>Admin User</td>
                                            <td>admin@example.com</td>
                                            <td>Admin</td>
                                            <td><span class="status-badge resolved">Active</span></td>
                                            <td><a href="#" class="text-link">Edit</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </aside>
                </section>
            </main>

            <footer class="admin-footer">
                <p>&copy; 2026 Ojo Local Government Area Security Incident Reporting System.</p>
                <p>System Administrator Dashboard.</p>
            </footer>
        </div>
    </div>

    <script src="../js/script.js"></script>
</body>
</html>
