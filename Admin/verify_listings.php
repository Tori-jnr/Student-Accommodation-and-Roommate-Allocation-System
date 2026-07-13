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

$pending = $conn->query("
    SELECT h.hostel_id, h.name, h.location, h.created_at, h.landlord,
           (SELECT MIN(price) FROM rooms r WHERE r.hostel_id = h.hostel_id) AS min_price
    FROM hostels h
    WHERE h.verified = 0
    ORDER BY h.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Listings | Roomly</title>
  <link rel="stylesheet" href="../assets/css/Admin Dash.css">
  <link rel="stylesheet" href="../assets/css/Verify_listings.css">
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
        <a class="active" href="verify_listings.php">
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
        <a href="../logout.php">Logout</a>
      </nav>
    </aside>

    <main class="main">
      <header class="main-header">
        <div class="page-title">
          <h1>Accommodation Verification Queue</h1>
          <p>Open a listing to review its photos and details before approving or rejecting it.</p>
        </div>
      
  <div class="user-pill">
    <span class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
    <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
</div>

      </header>

      <?php if (isset($_GET['status'])): ?>
        <div class="alert" style="margin: 16px 32px 0;">
          <?php
            if ($_GET['status'] === 'approved') echo 'Listing approved. It is now visible to students.';
            elseif ($_GET['status'] === 'rejected') echo 'Listing rejected and removed from the system.';
          ?>
        </div>
      <?php endif; ?>

      <section class="page">
        <div class="panel">
          <div class="section-title"><h2>Active Verification Requests</h2></div>
          <table class="table-wrap" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th>Hostel / Title</th>
                <th>Landlord</th>
                <th>Location</th>
                <th>From Price</th>
                <th style="text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($pending->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center; padding:32px; color:var(--text-secondary);">✨ No listings currently waiting in the verification queue.</td></tr>
              <?php else: while ($row = $pending->fetch_assoc()): ?>
                <tr>
                  <td style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['landlord']); ?></td>
                  <td><?php echo htmlspecialchars($row['location']); ?></td>
                  <td style="color:var(--neon-cyan); font-weight:600;">
                      <?php echo $row['min_price'] ? 'KES ' . number_format($row['min_price']) : '—'; ?>
                  </td>
                  <td style="text-align:center;">
                    <a class="button" style="padding:6px 12px; font-size:0.8rem;" href="listing_details.php?id=<?php echo $row['hostel_id']; ?>">Review listing</a>
                  </td>
                </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>