<?php
session_start();
if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$landlord_count = $conn->query("SELECT COUNT(*) AS total FROM landlords")->fetch_assoc()['total'];
$hostel_count   = $conn->query("SELECT COUNT(*) AS total FROM hostels")->fetch_assoc()['total'];
$student_count  = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$match_count    = $conn->query("SELECT COUNT(*) AS total FROM roommate_matches")->fetch_assoc()['total'];
$pref_count     = $conn->query("SELECT COUNT(*) AS total FROM student_preferences")->fetch_assoc()['total'];

$logs = $conn->query("SELECT activity_title, activity_description, activity_time FROM activity_log ORDER BY activity_time DESC LIMIT 10");
$compiledAt = date('d M Y, h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Reports | Roomly</title>
  <link rel="stylesheet" href="../assets/css/Admin Dash.css">
  <link rel="stylesheet" href="../assets/css/reports.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="logo">
        <span class="logo-text">Roomly<span class="dot" style="color: var(--neon-cyan); font-weight: 900;">.</span></span>
      </div>

      <div class="side-section">Admin Operations</div>
      <nav class="side-nav" aria-label="Admin navigation">
        <a href="dashboard.php">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
          <span>Dashboard</span>
        </a>
        <a href="verify_listings.php">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <span>Verify listings</span>
        </a>
        <a href="manage_users.php">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Manage users</span>
        </a>
        <a class="active" href="reports.php">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          <span>Reports</span>
        </a>
      </nav>
      <div class="side-section">Account Control</div>
      <nav class="side-nav" aria-label="Account navigation">
        <a href="../logout.php">Logout</a>
      </nav>
    </aside>

    <main class="main">
      <header class="main-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="page-title">
          <h1>System Reports</h1>
          <p>Audit registrations, listings, and system activity.</p>
        </div>
        <button class="button" id="print-report-btn" style="padding: 10px 18px; font-size: 0.85rem;">Print summary</button>

<div class="user-pill">
    <span class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
    <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
</div>
      </header>

      <section class="page">
        <div class="dashboard-grid">
          <div class="panel">
            <div class="section-title"><h2>System summary — compiled <?php echo $compiledAt; ?></h2></div>
            <div class="metric-line"><strong>Total registered landlords</strong><span class="metric-pill"><?php echo intval($landlord_count); ?> Hosts</span></div>
            <div class="metric-line"><strong>Total hostel listings</strong><span class="metric-pill"><?php echo intval($hostel_count); ?> Listings</span></div>
            <div class="metric-line"><strong>Registered student profiles</strong><span class="metric-pill"><?php echo intval($student_count); ?> Students</span></div>
            <div class="metric-line"><strong>Roommate matches</strong><span class="metric-pill"><?php echo intval($match_count); ?> Pairs</span></div>
            <div class="metric-line"><strong>Preference records</strong><span class="metric-pill"><?php echo intval($pref_count); ?> Records</span></div>
          </div>

          <div class="panel">
            <div class="section-title"><h2>Recent system activity</h2></div>
            <div class="activity-list">
              <?php if ($logs->num_rows === 0): ?>
                <div class="activity-item"><span class="muted">No activity recorded yet.</span></div>
              <?php else: while ($log = $logs->fetch_assoc()): ?>
                <div class="activity-item">
                  <strong><?php echo htmlspecialchars($log['activity_title']); ?></strong>
                  <span class="muted"><?php echo htmlspecialchars($log['activity_description']); ?></span>
                  <small style="color: var(--text-secondary);"><?php echo date('d M Y, h:i A', strtotime($log['activity_time'])); ?></small>
                </div>
              <?php endwhile; endif; ?>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('print-report-btn').addEventListener('click', () => window.print());
  </script>
</body>
</html>