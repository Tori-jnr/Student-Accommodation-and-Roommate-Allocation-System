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

$hostel_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : 0);
if ($hostel_id <= 0) {
    header('Location: verify_listings.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $code = 'GR-' . rand(100, 999);
        $conn->query("UPDATE hostels SET verified = 1, verified_date = CURDATE(), verification_code = '$code' WHERE hostel_id = $hostel_id");

        $nameRow = $conn->query("SELECT name FROM hostels WHERE hostel_id = $hostel_id")->fetch_assoc();
        $safeName = $conn->real_escape_string($nameRow['name'] ?? '');
        $conn->query("INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description) VALUES (1, 'approval', 'Listing Approved', 'Hostel \'$safeName\' was approved and is now visible to students.')");

        header('Location: verify_listings.php?status=approved');
        exit();
    }

    if ($action === 'reject') {
        $imgResult = $conn->query("SELECT image_path FROM hostel_photos WHERE hostel_id = $hostel_id");
        while ($img = $imgResult->fetch_assoc()) {
            $filePath = '../' . $img['image_path'];
            if (is_file($filePath)) @unlink($filePath);
        }
        $coverRow = $conn->query("SELECT name, image_path FROM hostels WHERE hostel_id = $hostel_id")->fetch_assoc();
        if (!empty($coverRow['image_path']) && is_file('../' . $coverRow['image_path'])) {
            @unlink('../' . $coverRow['image_path']);
        }

        $safeName = $conn->real_escape_string($coverRow['name'] ?? '');
        $conn->query("INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description) VALUES (1, 'rejection', 'Listing Rejected', 'Hostel \'$safeName\' was rejected and removed from the system.')");

        $conn->query("DELETE FROM hostels WHERE hostel_id = $hostel_id");

        header('Location: verify_listings.php?status=rejected');
        exit();
    }
}

$result = $conn->query("
    SELECT h.*, l.name AS landlord_name, l.email AS landlord_email, l.phone_number AS landlord_phone
    FROM hostels h
    LEFT JOIN landlords l ON h.landlord_id = l.landlord_id
    WHERE h.hostel_id = $hostel_id
");
$hostel = $result->fetch_assoc();

if (!$hostel) {
    header('Location: verify_listings.php');
    exit();
}

$rooms = $conn->query("SELECT room_type, price, status FROM rooms WHERE hostel_id = $hostel_id");
$photos = $conn->query("SELECT image_path FROM hostel_photos WHERE hostel_id = $hostel_id ORDER BY photo_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($hostel['name']); ?> | Review Listing</title>
  <link rel="stylesheet" href="../assets/css/Admin Dash.css">
  <link rel="stylesheet" href="../assets/css/Verify_listings.css">
  <style>
    .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; margin-top: 14px; }
    .photo-grid img { width: 100%; height: 130px; object-fit: cover; border-radius: 8px; border: 1px solid var(--glass-border); }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--glass-border); }
    .info-row:last-child { border-bottom: none; }
  </style>
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
          <h1><?php echo htmlspecialchars($hostel['name']); ?></h1>
          <p><?php echo htmlspecialchars($hostel['location']); ?></p>
        </div>
        <a class="button secondary" href="verify_listings.php">Back to queue</a>

<div class="user-pill">
    <span class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
    <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
</div>
      </header>

      <section class="page">
        <div class="panel" style="margin-bottom: 20px;">
          <div class="section-title"><h2>Photos</h2></div>
          <?php if ($photos->num_rows === 0 && empty($hostel['image_path'])): ?>
            <p style="color: var(--text-secondary);">No photos were uploaded for this listing.</p>
          <?php else: ?>
            <div class="photo-grid">
              <?php if (!empty($hostel['image_path'])): ?>
                <img src="../<?php echo htmlspecialchars($hostel['image_path']); ?>" alt="Cover photo">
              <?php endif; ?>
              <?php while ($photo = $photos->fetch_assoc()): ?>
                <img src="../<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Hostel photo">
              <?php endwhile; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="panel" style="margin-bottom: 20px;">
          <div class="section-title"><h2>Listing details</h2></div>
          <div class="info-row"><span>Landlord</span><strong><?php echo htmlspecialchars($hostel['landlord_name'] ?? $hostel['landlord']); ?></strong></div>
          <div class="info-row"><span>Landlord contact</span><strong><?php echo htmlspecialchars(($hostel['landlord_email'] ?? '') . ' · ' . ($hostel['landlord_phone'] ?? '')); ?></strong></div>
          <div class="info-row"><span>Location</span><strong><?php echo htmlspecialchars($hostel['location']); ?></strong></div>
          <div class="info-row"><span>Amenities</span><strong><?php echo htmlspecialchars($hostel['amenities'] ?: 'Not specified'); ?></strong></div>
          <div class="info-row"><span>Description</span><strong><?php echo htmlspecialchars($hostel['description'] ?: 'Not provided'); ?></strong></div>
          <?php if (!empty($hostel['panorama_link'])): ?>
          <div class="info-row"><span>Virtual tour</span><strong><a href="<?php echo htmlspecialchars($hostel['panorama_link']); ?>" target="_blank" style="color: var(--neon-blue);">Open link</a></strong></div>
          <?php endif; ?>
        </div>

        <div class="panel" style="margin-bottom: 20px;">
          <div class="section-title"><h2>Rooms</h2></div>
          <table class="table-wrap" style="width:100%;">
            <thead><tr><th>Room type</th><th>Price</th><th>Status</th></tr></thead>
            <tbody>
              <?php while ($room = $rooms->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                  <td style="color:var(--neon-cyan); font-weight:600;">KES <?php echo number_format($room['price']); ?></td>
                  <td><?php echo htmlspecialchars(ucfirst($room['status'])); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="panel" style="display:flex; gap:14px;">
          <form method="POST" onsubmit="return confirm('Approve this listing? It will become visible to students.');">
            <input type="hidden" name="hostel_id" value="<?php echo $hostel_id; ?>">
            <input type="hidden" name="action" value="approve">
            <button class="button" type="submit" style="padding:12px 24px;">Approve listing</button>
          </form>
          <form method="POST" onsubmit="return confirm('Reject and permanently remove this listing? This cannot be undone.');">
            <input type="hidden" name="hostel_id" value="<?php echo $hostel_id; ?>">
            <input type="hidden" name="action" value="reject">
            <button class="button secondary" type="submit" style="padding:12px 24px; background:rgba(239,68,68,0.1); color:var(--danger);">Reject &amp; remove</button>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>