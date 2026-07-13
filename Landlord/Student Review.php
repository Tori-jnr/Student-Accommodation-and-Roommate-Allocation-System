<?php
session_start();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'landlord') {
    header("Location: ../Login.html");
    exit();
}

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$landlord_id = $_SESSION['student_id'];

$landlordRow = $conn->query("SELECT name FROM landlords WHERE landlord_id = '$landlord_id'")->fetch_assoc();
$landlordName = $landlordRow['name'] ?? 'Property Manager';

// ---- Fetch all reviews left on this landlord's hostels ----
$reviews = $conn->query("
    SELECT rv.rating, rv.comment, rv.created_at,
           s.full_name AS student_name,
           h.name AS hostel_name
    FROM reviews rv
    JOIN students s ON rv.student_id = s.student_id
    JOIN hostels h ON rv.hostel_id = h.hostel_id
    WHERE h.landlord_id = '$landlord_id'
    ORDER BY rv.created_at DESC
");
$reviewRows = [];
while ($row = $reviews->fetch_assoc()) {
    $reviewRows[] = $row;
}

$totalReviews = count($reviewRows);
$avgRating = 0;
if ($totalReviews > 0) {
    $sum = array_sum(array_column($reviewRows, 'rating'));
    $avgRating = round($sum / $totalReviews, 1);
}

if ($totalReviews === 0) {
    $grade = "No Reviews Yet";
} elseif ($avgRating >= 4.5) {
    $grade = "Elite Provider";
} elseif ($avgRating >= 3.5) {
    $grade = "Trusted Provider";
} elseif ($avgRating >= 2.5) {
    $grade = "Standard Provider";
} else {
    $grade = "Needs Improvement";
}

function renderStars($rating) {
    $rating = intval($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 3600) return floor($diff / 60) . " minutes ago";
    if ($diff < 86400) return floor($diff / 3600) . " hours ago";
    if ($diff < 172800) return "Yesterday";
    return date('d M Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Operations Center - Roomly.</title>
    <link rel="stylesheet" href="../assets/css/Student Review.css">
</head>
<body>

    <div class="dashboard-shell">

<aside class="sidebar">
    <div class="logo">
        <span class="logo-text">Roomly<span class="dot" style="color: var(--neon-cyan); font-weight: 900;">.</span></span>
    </div>

    <div class="my-text">
        <div>
            <span class="nav-heading">Operations Hub</span>
            <nav class="nav-links">
                <a href="Dashboard.php">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Hostel</span>
                </a>
                <a href="Student Review.php" class="active">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>Student Reviews</span>
                </a>
                <a href="Profile.php">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
                    <span>Profile</span>
                </a>
            </nav>
        </div>

        <div>
            <span class="nav-heading">System Parameters</span>
            <nav class="nav-links">
                <a href="../logout.php" class="logout-link">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </div>
</aside>

        <main class="main">
            <header class="main-header" style="background: transparent; border-bottom: 1px solid var(--glass-border);">
                <div class="page-title">
                    <h1 style="font-size: 1.8rem; font-weight: 700;">Student Reviews</h1>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Feedback from students who've stayed at your hostels.</p>
                </div>
                <a class="user-pill" href="Profile.php" style="border-color: var(--glass-border); cursor: pointer; text-decoration: none;">
                    <div class="avatar" style="background: var(--neon-blue); color: #ffffff;"><?php echo strtoupper(substr($landlordName,0,1)); ?></div>
                    <span class="username" style="font-size: 0.9rem; font-weight: 600;"><?php echo htmlspecialchars($landlordName); ?></span>
                </a>
            </header>

            <section class="page">
                <div class="three-column" style="margin-bottom: 30px;">
                    <div class="panel" style="background: var(--glass-panel); border-color: var(--glass-border);">
                        <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.05em; margin-bottom: 6px;">Aggregate Rating</h3>
                        <p style="font-size: 1.8rem; font-weight: 700; color: var(--neon-cyan);"><?php echo $totalReviews > 0 ? $avgRating : '—'; ?><span style="font-size: 1rem; color: var(--text-secondary); font-weight: 400;"> / 5.0</span></p>
                    </div>
                    <div class="panel" style="background: var(--glass-panel); border-color: var(--glass-border);">
                        <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.05em; margin-bottom: 6px;">Total Reviews</h3>
                        <p style="font-size: 1.8rem; font-weight: 700; color: var(--neon-blue);"><?php echo $totalReviews; ?></p>
                    </div>
                    <div class="panel" style="background: var(--glass-panel); border-color: var(--glass-border);">
                        <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.05em; margin-bottom: 6px;">Reputation Grade</h3>
                        <p style="font-size: 1.4rem; font-weight: 700; color: #a855f7; margin-top: 4px;"><?php echo htmlspecialchars($grade); ?></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div class="panel" style="background: var(--glass-panel); border-color: var(--glass-border); padding: 24px;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px;">Reviews</h2>

                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <?php if (empty($reviewRows)): ?>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; padding: 10px;">No student reviews yet for your hostels.</p>
                            <?php else: foreach ($reviewRows as $rv): ?>
                                <div class="match-card" style="background: rgba(0, 0, 0, 0.2); border-color: var(--glass-border); padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                        <div>
                                            <h4 style="font-size: 1rem; font-weight: 600; color: var(--text-pure);"><?php echo htmlspecialchars($rv['student_name']); ?></h4>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">Resident: <?php echo htmlspecialchars($rv['hostel_name']); ?></span>
                                        </div>
                                        <div style="text-align: right;">
                                            <span style="color: var(--neon-cyan); font-weight: 700;"><?php echo renderStars($rv['rating']); ?></span>
                                            <small style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;"><?php echo timeAgo($rv['created_at']); ?></small>
                                        </div>
                                    </div>
                                    <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;">"<?php echo htmlspecialchars($rv['comment']); ?>"</p>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 30px;">
                        <div class="panel" style="background: var(--glass-panel); border-color: var(--glass-border);">
                            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px;">Reputation Policy</h2>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                                Your reputation grade is based on your average rating across all reviews from students who've stayed at your hostels.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>