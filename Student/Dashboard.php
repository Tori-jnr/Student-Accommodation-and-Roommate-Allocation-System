<?php
session_start();
require '../db_connect.php';

//check if user is logged or redirect
if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}
$student_id = $_SESSION['student_id'];

// Get the logged-in student's best roommate match
 $bestMatch = 0; $stmt = $conn->prepare(" SELECT MAX(match_percentage) AS best_match FROM roommate_matches WHERE student_id = ? "); 
 $stmt->bind_param("i", $_SESSION['student_id']);
  $stmt->execute();
   $result = $stmt->get_result(); 
   if ($row = $result->fetch_assoc()) { if ($row['best_match'] !== null) { $bestMatch = round($row['best_match']);
    } 
    }
    $stmt->close();



//profile fetching
$stmt = mysqli_prepare($conn,
    "SELECT full_name, profile_pic
     FROM students
     WHERE student_id = ?");

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

//fetch hostel suggestions details
$q = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM hostels
     WHERE verified = 1");

$verified = mysqli_fetch_assoc($q);

$q = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM rooms
     WHERE status='available'");

$rooms = mysqli_fetch_assoc($q);


$q = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM reviews
     WHERE student_id=$student_id");

$reviews = mysqli_fetch_assoc($q);


$sql = "SELECT h.*, r.room_type, r.price 
FROM hostels h JOIN rooms r ON h.hostel_id=r.hostel_id 
WHERE r.status='available' AND h.verified = 1";

$result = mysqli_query($conn, $sql);
$hostels = mysqli_fetch_all($result, MYSQLI_ASSOC);

//activity log query
$sql = "SELECT * 
     FROM activity_log
        WHERE student_id = ?
        ORDER BY activity_time DESC
        LIMIT 5";



$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$activity_log = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Roomly</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Lucide Icons for clean UI elements -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="dashboard-shell">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="logo">Roomly<span class="dot">.</span></h2>
            
            <div class="nav-container">
                <span class="nav-heading">Student Menu</span>
                <nav class="nav-links">
                    <a href="dashboard.php" class="active">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                    <a href="hostels.php">
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
                                 Reviews</a>

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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="welcome-text">
                    <h1>Your Next Home Awaits</h1>
                    <p>Browse trusted hostels and connect with compatible roommates today.</p>
                </div>
                
                <!-- Clickable Interactive User Profile Widget -->
                <div class="user-profile-widget" onclick="window.location.href='profile.php'" title="View Profile">
                    <div class="profile-container">
                        <img src="<?php echo htmlspecialchars($student['profile_pic']); ?>" 
                             alt="<?php echo htmlspecialchars($student['full_name']); ?>" 
                             class="profile-pic"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-pic-fallback" style="display: none;"><?php echo strtoupper(substr($student['full_name'], 0, 1) . substr(strrchr($student['full_name'], ' '), 1, 1)); ?></div>
                    </div>
                    <span class="username">
                        <?php echo htmlspecialchars($student['full_name']); ?>
                    </span>
                </div>
            </header>

            <!-- Stats Row -->
            <section class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon cyan-glow"><i data-lucide="shield-check"></i></div>
                    <div>
                        <h3>Verified Hostels</h3>
                        <p><?php echo htmlspecialchars($verified['total']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue-glow"><i data-lucide="home"></i></div>
                    <div>
                        <h3>Available Rooms</h3>
                        <p><?php echo htmlspecialchars($rooms['total']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple-glow"><i data-lucide="heart"></i></div>
                    <div>
                        <h3>Best Match</h3>
                        <p><?php echo $bestMatch; ?>%</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow-glow"><i data-lucide="star"></i></div>
                    <div>
                        <h3>Reviews Posted</h3>
                        <p><?php echo htmlspecialchars($reviews['total']); ?></p>
                    </div>
                </div>
            </section>

            <!-- Grid Layout -->
            <section class="content-grid">
                <!-- Recommended Hostels Section -->
                <section class="recommendations panel">
                    <div class="panel-header">
                        <h2>Recommended Hostels</h2>
                        <a href="hostels.php" class="view-all-btn">View All</a>
                    </div>
                    
                    <div class="hostel-list">
<?php foreach($hostels as $hostel): ?>

<article class="hostel-card">
   
    <div class="hostel-image"
         style="background-image:url('<?php echo htmlspecialchars($hostel['image_path']); ?>');">
        <span class="badge <?php echo $hostel['verified'] ? 'verified' : 'pending'; ?>">
            <?php echo $hostel['verified'] ? 'Verified' : 'Unverified'; ?>
        </span>

    </div>

    <div class="hostel-info">

        <h3><?php echo htmlspecialchars($hostel['name']); ?></h3>

        <p class="location">
            <i data-lucide="map-pin"></i>
            <?php echo htmlspecialchars($hostel['location']); ?>
        </p>

        <div class="tags">
            <span class="tag"><?php echo htmlspecialchars($hostel['room_type']); ?></span>
        </div>

        <div class="price-action">
            <span class="price">
                KES <?php echo number_format($hostel['price']); ?>
            </span>

            <a href="hosteldetails.php?id=<?php echo $hostel['hostel_id']; ?>"
               class="action-btn">
               Open
            </a>

        </div>

    </div>

</article>

<?php endforeach; ?>
</div>
                </section>

                <!-- Activity Log Section -->
                 
                <aside class="activity panel">
                    <div class="activity-header" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px;">
                        <h2 style="margin: 0;">Activity</h2>
                        <p class="section-desc" style="margin: 0;">Recent student actions</p>
                    </div>
                <div class="activity-list">
                    <?php if (count($activity_log) > 0): ?>

                    <?php foreach ($activity_log as $row): ?>

<?php
$type = $row['activity_type'] ?? '';
$icon = [
    'review' => 'star',
    'tour' => 'eye',
    'preference' => 'check-circle',
    'view' => 'home'
][$type] ?? 'clock';

$color = [
    'review' => 'yellow',
    'tour' => 'blue',
    'preference' => 'green',
    'view' => 'purple'
][$type] ?? 'gray';
?>

<div class="activity-item">
    <div class="activity-icon <?php echo $color; ?>">
        <i data-lucide="<?php echo $icon; ?>"></i>
    </div>
    <div class="activity-details">
        <h4><?php echo htmlspecialchars($row['activity_title']); ?></h4>
        <p><?php echo htmlspecialchars($row['activity_description']); ?></p>
        <span class="activity-time">
            <?php echo date("d M Y H:i", strtotime($row['activity_time'])); ?>
        </span>
    </div>
</div>

<?php endforeach; ?>

<?php else: ?>

<div class="activity-item">
    <div class="activity-details">
        <h4>No recent activity</h4>
        <p>Your recent actions will appear here.</p>
    </div>
</div>

<?php endif; ?>
                    </div>        

                </aside>
            </section>
        </main>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();

        // Sync Profile Data from Local Storage to Dashboard
        window.addEventListener('DOMContentLoaded', () => {

            // Update Name and Greeting
            if (savedName) {
                const greetingText = document.querySelector('.welcome-text p');
                if (greetingText) {
                    const firstName = savedName.split(' ')[0]; // Gets just the first name
                    greetingText.innerText = `Welcome back, ${firstName}`;
                }

                const userNameText = document.querySelector('.username');
                if (userNameText) userNameText.innerText = savedName;
            }

            // Update Profile Photo
            if (savedAvatar) {
                const avatarImg = document.querySelector('.profile-pic');
                const fallbackBadge = document.querySelector('.profile-pic-fallback');
                if (avatarImg) {
                    avatarImg.src = savedAvatar;
                    avatarImg.style.display = 'block';
                    if (fallbackBadge) fallbackBadge.style.display = 'none';
                }
            }
        });
        
    </script>
</body>
</html>