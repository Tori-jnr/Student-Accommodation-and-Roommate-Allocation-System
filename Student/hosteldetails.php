<?php
session_start();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header("Location: ../Login.html");
    exit();
}

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hostel_id = intval($_GET['id'] ?? 0);

$result = $conn->query("
    SELECT h.*, r.room_type, r.price, r.status
    FROM hostels h
    JOIN rooms r ON h.hostel_id = r.hostel_id
    WHERE h.hostel_id = $hostel_id AND h.verified = 1
    LIMIT 1
");

if ($result->num_rows === 0) {
    die("Hostel not found or not yet approved.");
}
$row = $result->fetch_assoc();

$photos = $conn->query("SELECT image_path FROM hostel_photos WHERE hostel_id = $hostel_id ORDER BY photo_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['name']); ?> | Roomly</title>
    <link rel="stylesheet" href="../assets/css/hosteldetails.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="sidebar">
            <h2 class="logo">Roomly<span class="dot">.</span></h2>

            <div class="nav-container">
                <span class="nav-heading">Student Menu</span>
                <nav class="nav-links">
                    <a href="dashboard.php">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                    <a href="Hostels.php" class="active">
                        <i data-lucide="search"></i>
                        Search Hostels
                    </a>
                    <a href="Roommate.php">
                        <i data-lucide="sliders"></i>
                        Preferences
                    </a>
                    <a href="matchresults.php">
                        <i data-lucide="sparkles"></i>
                        Match Results
                    </a>
                    <a href="reviews.php">
                        <i data-lucide="star"></i>
                        Reviews
                    </a>
                </nav>

                <span class="nav-heading">Account</span>
                <nav class="nav-links">
                    <a href="../logout.php" class="logout-link">
                        <i data-lucide="log-out"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <a href="Hostels.php" class="back-btn">&larr; Back to listings</a>

            <div class="detail-hero" style="background-image:url('../<?php echo htmlspecialchars($row['image_path']); ?>'); height: 320px; background-size: cover; background-position: center; border-radius: 16px; margin: 16px 0;"></div>

            <?php if ($photos->num_rows > 0): ?>
            <div class="panel" style="margin-bottom: 20px;">
                <h2>Photos</h2>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-top: 12px;">
                    <?php while ($photo = $photos->fetch_assoc()): ?>
                        <img src="../<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Hostel photo" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="panel">
                <h1><?php echo htmlspecialchars($row['name']); ?></h1>
                <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['location']); ?></p>
                <p style="margin-top: 12px;"><?php echo nl2br(htmlspecialchars($row['description'] ?? '')); ?></p>

                <div style="margin-top: 16px;">
                    <strong>Amenities:</strong> <?php echo htmlspecialchars($row['amenities'] ?: 'Not specified'); ?>
                </div>
                <div style="margin-top: 8px;">
                    <strong>Room type:</strong> <?php echo htmlspecialchars($row['room_type']); ?> —
                    <span style="color: var(--neon-cyan); font-weight:700;">KES <?php echo number_format($row['price']); ?></span>
                </div>
                <div style="margin-top: 8px;">
                    <strong>Managed by:</strong> <?php echo htmlspecialchars($row['landlord']); ?>
                </div>
                <?php if (!empty($row['panorama_link'])): ?>
                <div style="margin-top: 8px;">
                    <a href="<?php echo htmlspecialchars($row['panorama_link']); ?>" target="_blank" style="color: var(--neon-blue);">View virtual tour →</a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>