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

$landlords = $conn->query("SELECT landlord_id, full_name, email, phone FROM landlords ORDER BY full_name");
$students  = $conn->query("SELECT student_id, full_name, email, phone, university FROM students ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users | Roomly</title>
  <link rel="stylesheet" href="../assets/css/Admin Dash.css">
  <link rel="stylesheet" href="../assets/css/manage_users.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="logo">
        <span>Roomly<span class="dot">.</span></span>
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
        <a class="active" href="manage_users.php">
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
          <h1>Identity Profile Registry</h1>
          <p>Monitor registered landlords and students.</p>
        </div>
        <div class="user-pill">
<span class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
<span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>

</div>
      </header>

      <section class="page">
        <div class="panel" style="margin-bottom: 24px;">
          <div class="section-title"><h2>Landlords (<?php echo $landlords->num_rows; ?>)</h2></div>
          <table class="table-wrap" style="width:100%;">
            <thead>
              <tr>
                <th>ID</th>
                <th>Full name</th>
                <th>Email</th>
                <th>Phone</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($landlords->num_rows === 0): ?>
                <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--text-secondary);">No landlords registered yet.</td></tr>
              <?php else: while ($row = $landlords->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['landlord_id']); ?></td>
                  <td style="font-weight:600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['phone']); ?></td>
                </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>

        <div class="panel">
          <div class="section-title"><h2>Students (<?php echo $students->num_rows; ?>)</h2></div>
          <table class="table-wrap" style="width:100%;">
            <thead>
              <tr>
                <th>ID</th>
                <th>Full name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>University</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($students->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--text-secondary);">No students registered yet.</td></tr>
              <?php else: while ($row = $students->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                  <td style="font-weight:600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['phone']); ?></td>
                  <td><?php echo htmlspecialchars($row['university']); ?></td>
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