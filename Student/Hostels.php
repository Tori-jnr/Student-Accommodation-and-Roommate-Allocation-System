<?php
session_start();
require '../db_connect.php';

// Must be logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../Login.php');
    exit;
}
$student_id = $_SESSION['student_id'];

// Logged-in student's name/pic for the header widget
$stmt = mysqli_prepare($conn, "SELECT full_name, profile_pic FROM students WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$fullName   = $student['full_name'] ?? 'Student';
$profilePic = $student['profile_pic'] ?? '';

// ---- Read the filters the student submitted (if any) ----
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$roomType = isset($_GET['room_type']) ? trim($_GET['room_type']) : '';
$maxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';

// ---- Build the query: every available room, joined to its hostel ----
$sql = "SELECT h.hostel_id, h.name, h.location, h.image_path, h.verified,
               r.room_type, r.price
        FROM hostels h
        JOIN rooms r ON h.hostel_id = r.hostel_id
        WHERE r.status = 'available'";

if ($search !== '') {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (h.name LIKE '%$searchSafe%' OR h.location LIKE '%$searchSafe%')";
}
if ($roomType !== '' && $roomType !== 'All Room Types') {
    $roomTypeSafe = mysqli_real_escape_string($conn, $roomType);
    $sql .= " AND r.room_type = '$roomTypeSafe'";
}
if ($maxPrice !== '' && $maxPrice !== '0') {
    $maxPriceSafe = (int) $maxPrice;
    $sql .= " AND r.price <= $maxPriceSafe";
}
$sql .= " ORDER BY h.name";

$result = mysqli_query($conn, $sql);
$count  = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hostels | Roomly</title>
    <link rel="stylesheet" href="../assets/css/hostels.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="app-shell">

        <!-- SIDEBAR (Synced with Dashboard) -->
        <aside class="sidebar">
            <h2 class="logo">Roomly<span class="dot">.</span></h2>
            <div class="nav-container">
                <span class="nav-heading">Student Menu</span>
                <nav class="nav-links">
                    <a href="dashboard.php">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                    <a href="Hostels.php" class="active">
                        <i data-lucide="search"></i> Search Hostels
                    </a>
                    <a href="Roommate.php">
                        <i data-lucide="sliders"></i> Preferences
                    </a>
                    <a href="matchresults.php">
                        <i data-lucide="sparkles"></i> Match Results
                    </a>
        <a href="reviews.php">
            <i data-lucide="star"></i> Reviews
        </a>

                </nav>
                <span class="nav-heading">Account</span>
                <nav class="nav-links">
                    <a href="../logout.php" class="logout-link">
                        <i data-lucide="log-out"></i> Logout
                    </a>
                </nav>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">

            <!-- TOP HEADER (Synced with Dashboard) -->
            <header class="top-header">
                <div class="page-title">
                    <h1>Available Hostels</h1>
                    <p>Browse and filter verified student accommodation.</p>
                </div>

                <!-- Interactive User Profile Widget (same pattern as dashboard.php) -->
                <div class="user-profile-widget" onclick="window.location.href='profile.php'" title="View Profile">
                    <div class="profile-container">
                        <img src="<?php echo htmlspecialchars($profilePic); ?>"
                             alt="<?php echo htmlspecialchars($fullName); ?>"
                             class="profile-pic"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-pic-fallback" style="display: none;"><?php echo strtoupper(substr($fullName, 0, 1) . substr(strrchr($fullName, ' '), 1, 1)); ?></div>
                    </div>
                    <span class="username" id="header-name"><?php echo htmlspecialchars($fullName); ?></span>
                </div>
            </header>

            <section class="page-content">

                <!-- SEARCH AND FILTER BAR (Glassmorphic) -->
             <form method="GET" action="Hostels.php" class="filters-bar panel">
                    <div class="search-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" class="filter-input" placeholder="Search by name or location..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                       <select class="filter-select" name="room_type">
                        <option value="">All Room Types</option>
                        <option value="Single Room"   <?php if ($roomType === 'Single Room')   echo 'selected'; ?>>Single Room</option>
                        <option value="Double Room"    <?php if ($roomType === 'Double Room')    echo 'selected'; ?>>Double Room</option>
                        <option value="Four-Sharing"   <?php if ($roomType === 'Four-Sharing')   echo 'selected'; ?>>Four-Sharing</option>
                        <option value="Premium Studio" <?php if ($roomType === 'Premium Studio') echo 'selected'; ?>>Premium Studio</option>
                    </select>

                    <select class="filter-select" name="max_price">
                        <option value="">Max Price</option>
                        <option value="20000" <?php if ($maxPrice === '20000') echo 'selected'; ?>>Under KES 20,000</option>
                        <option value="30000" <?php if ($maxPrice === '30000') echo 'selected'; ?>>Under KES 30,000</option>
                        <option value="50000" <?php if ($maxPrice === '50000') echo 'selected'; ?>>Under KES 50,000</option>
                        <option value="80000" <?php if ($maxPrice === '80000') echo 'selected'; ?>>Under KES 80,000</option>
                    </select>

                <button type="submit" class="filter-btn"><i data-lucide="filter"></i> Filter Results</button>
               </form>

                <!-- HOSTELS GRID  -->
                <div class="hostels-grid">
                 <?php if ($count > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <article class="hostel-card">
                        <div class="hostel-image" style="background-image: url('<?php echo htmlspecialchars($row['image_path']); ?>')">
                            <span class="badge <?php echo $row['verified'] ? 'verified' : 'pending'; ?>">
                                <?php echo $row['verified'] ? 'Verified' : 'Unverified'; ?>
                            </span>
                        </div>
                        <div class="hostel-info">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="location">
                                <i data-lucide="map-pin"></i>
                                <?php echo htmlspecialchars($row['location']); ?>
                            </p>
                            <div class="tags">
                                <span class="tag"><?php echo htmlspecialchars($row['room_type']); ?></span>
                            </div>
                            <div class="price-action">
                                <span class="price">
                                    KES <?php echo number_format($row['price']); ?>
                                    <small>/ month</small>
                                </span>
                                <a class="action-btn" href="hosteldetails.php?id=<?php echo $row['hostel_id']; ?>">View Details</a>
                            </div>
                        </div>
                    </article>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No available hostels found matching your filters.</p>
                    <?php endif; ?>

                </div>
            </section>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>