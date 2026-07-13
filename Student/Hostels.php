<?php
session_start();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header("Location: ../Login.php");
    exit();
}

require '../db_connect.php';

$sql = "SELECT h.hostel_id, h.name, h.location, h.image_path, h.verified,
               r.room_type, r.price
        FROM hostels h
        JOIN rooms r ON h.hostel_id = r.hostel_id
        WHERE r.status = 'available' AND h.verified = 1
        ORDER BY h.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hostels | Roomly</title>
    <link rel="stylesheet" href="../assets/css/hostels.css">
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
            <header class="top-header">
                <div class="welcome-text">
                    <h1>Browse Hostels</h1>
                    <p>Verified accommodations available near you.</p>
                </div>
            </header>

            <div class="hostel-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                <?php if ($result->num_rows === 0): ?>
                    <p style="color: var(--text-secondary);">No verified hostels available right now. Check back soon.</p>
                <?php else: while ($row = $result->fetch_assoc()): ?>
                    <a href="hosteldetails.php?id=<?php echo $row['hostel_id']; ?>" class="hostel-card" style="text-decoration: none; color: inherit;">
                        <div class="hostel-image" style="background-image: url('../<?php echo htmlspecialchars($row['image_path']); ?>'); height: 160px; background-size: cover; background-position: center; border-radius: 12px 12px 0 0;"></div>
                        <div class="hostel-card-body" style="padding: 16px;">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['location']); ?></p>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($row['room_type']); ?></p>
                            <p style="color: var(--neon-cyan); font-weight: 700;">KES <?php echo number_format($row['price']); ?></p>
                        </div>
                    </a>
                <?php endwhile; endif; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>