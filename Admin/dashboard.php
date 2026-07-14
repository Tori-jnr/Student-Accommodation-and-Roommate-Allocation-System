<?php
session_start();
if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}


$conn = new mysqli('localhost', 'root', '', 'roomly_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pending_count  = $conn->query("SELECT COUNT(*) AS total FROM hostels WHERE verified = 0")->fetch_assoc()['total'];
$verified_count = $conn->query("SELECT COUNT(*) AS total FROM hostels WHERE verified = 1")->fetch_assoc()['total'];
$student_count  = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$landlord_count = $conn->query("SELECT COUNT(*) AS total FROM landlords")->fetch_assoc()['total'];

$pendingListings = $conn->query("
    SELECT h.hostel_id, h.name, h.location, h.created_at, l.full_name AS landlord
    FROM hostels h
    LEFT JOIN landlords l ON h.landlord_id = l.landlord_id
    WHERE h.verified = 0
    ORDER BY h.created_at DESC
    LIMIT 5
");

$logs = $conn->query("SELECT activity_title, activity_description FROM activity_log ORDER BY activity_time DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/Admin Dash.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="logo">
        <span>Roomly<span class="dot">.</span></span>
      </div>

      <div class="side-section">Admin Operations</div>
      <nav class="side-nav" aria-label="Admin navigation">
        <a class="active" href="dashboard.php">
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
        <a href="reports.php">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          <span>Reports</span>
        </a>
      </nav>
      <div class="side-section">Account Control</div>
      <nav class="side-nav" aria-label="Account navigation">
        <a href="../logout.php" class="logout-link">
          <svg viewBox="0 0 24 24" class="sidebar-icon"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span>Logout</span>
        </a>
      </nav>
    </aside>

    <main class="main">
      <header class="main-header">
        <div class="page-title">
          <div>
            <h1>Admin dashboard</h1>
            <p>Monitor users, listings, and system activity.</p>
          </div>
        </div>

        <div class="user-pill">
    <span class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
    <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
</div>

      </header>

      <section class="page">
        <div class="stats-grid">
          <article class="stat-card">
            <span class="stat-label">Pending listings</span>
            <div class="stat-value"><?php echo intval($pending_count); ?></div>
          </article>
          <article class="stat-card">
            <span class="stat-label">Verified listings</span>
            <div class="stat-value"><?php echo intval($verified_count); ?></div>
          </article>
          <article class="stat-card">
            <span class="stat-label">Students</span>
            <div class="stat-value"><?php echo intval($student_count); ?></div>
          </article>
          <article class="stat-card">
            <span class="stat-label">Landlords</span>
            <div class="stat-value"><?php echo intval($landlord_count); ?></div>
          </article>
        </div>

        <div class="dashboard-grid" style="margin-top: 18px;">
          <section class="panel">
            <div class="section-title">
              <h2>Pending verification</h2>
              <a class="button secondary" href="verify_listings.php">Open queue</a>
            </div>
            <div class="table-wrap">
              <table style="width:100%;">
                <thead>
                  <tr>
                    <th>Listing</th>
                    <th>Landlord</th>
                    <th>Location</th>
                    <th>Submitted</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($pendingListings->num_rows === 0): ?>
                    <tr><td colspan="5" style="text-align:center; padding:16px; color:var(--text-secondary);">✨ No listings currently waiting.</td></tr>
                  <?php else: while ($row = $pendingListings->fetch_assoc()): ?>
                    <tr>
                      <td style="font-weight:600;"><a href="listing_details.php?id=<?php echo $row['hostel_id']; ?>" style="color:var(--neon-blue);"><?php echo htmlspecialchars($row['name']); ?></a></td>
                      <td><?php echo htmlspecialchars($row['landlord']); ?></td>
                      <td><?php echo htmlspecialchars($row['location']); ?></td>
                      <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                      <td><span class="chip warning">Pending</span></td>
                    </tr>
                  <?php endwhile; endif; ?>
                </tbody>
              </table>
            </div>
          </section>

          <aside class="panel">
            <div class="section-title"><h2>System activity</h2></div>
            <div class="activity-list">
              <?php if ($logs->num_rows === 0): ?>
                <div class="activity-item"><span class="muted">No system activity recorded yet.</span></div>
              <?php else: while ($log = $logs->fetch_assoc()): ?>
                <div class="activity-item">
                  <strong><?php echo htmlspecialchars($log['activity_title']); ?></strong>
                  <span class="muted"><?php echo htmlspecialchars($log['activity_description']); ?></span>
                </div>
              <?php endwhile; endif; ?>
            </div>
          </aside>
        </div>
      </section>
    </main>
  </div>
</body>
</html>