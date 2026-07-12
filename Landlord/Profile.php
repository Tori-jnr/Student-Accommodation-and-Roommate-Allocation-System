<?php
session_start();

// 1. SECURITY: If no one is logged in as a landlord, send them back to login
if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'landlord') {
    header("Location: ../Login.html");
    exit();
}

// Connect to database
$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the REAL logged-in landlord's ID from the session
// (login_process.php stores it in $_SESSION['student_id'] for both roles)
$landlord_id = $_SESSION['student_id'];

// 1. IF THE USER CLICKS "SAVE CHANGES" (process the form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name  = $conn->real_escape_string($_POST['landlord_name']);
    $new_email = $conn->real_escape_string($_POST['landlord_email']);
    $new_phone = $conn->real_escape_string($_POST['landlord_phone']);

    $update_sql = "UPDATE landlords SET full_name='$new_name', email='$new_email', phone='$new_phone' WHERE landlord_id='$landlord_id'";
    $conn->query($update_sql);

    // Handle an optional new avatar photo
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $safeName = 'landlord_' . preg_replace('/[^a-zA-Z0-9]/', '', $landlord_id) . '_' . time() . '.' . strtolower($ext);
        $destination = $uploadDir . $safeName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $picPath = 'uploads/avatars/' . $safeName;
            $conn->query("UPDATE landlords SET profile_pic='$picPath' WHERE landlord_id='$landlord_id'");
        }
    }
}

// 2. FETCH LATEST DATA TO DISPLAY ON SCREEN
$result = $conn->query("SELECT * FROM landlords WHERE landlord_id = '$landlord_id'");
$landlord = $result->fetch_assoc();

$full_name  = htmlspecialchars($landlord['full_name'] ?? '');
$email      = htmlspecialchars($landlord['email'] ?? '');
$phone      = htmlspecialchars($landlord['phone'] ?? '');
$profilePic = htmlspecialchars($landlord['profile_pic'] ?? '');

// Fetch this landlord's hostel names for the read-only "Hostels" field
$hostelsResult = $conn->query("SELECT name FROM hostels WHERE landlord_id = '$landlord_id' ORDER BY created_at DESC");
$hostelNames = [];
while ($row = $hostelsResult->fetch_assoc()) {
    $hostelNames[] = $row['name'];
}
$hostelsDisplay = $hostelNames ? implode(', ', $hostelNames) : 'No hostels listed yet';

// Initials for the avatar fallback
$initials = "L";
if (!empty($full_name)) {
    $words = explode(" ", trim($full_name));
    $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts - Roomly.</title>
    <link rel="stylesheet" href="../assets/css/Landlord Profile.css">
    <script src="https://unpkg.com/lucide@latest"></script>
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
                    <span>Dashboard</span>
                </a>
                <a href="Student Review.html">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>Student Reviews</span>
                </a>
                <a href="Profile.php" class="active">
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

        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text">
                    <h1>My Profile</h1>
                    <p>Your Listed Hostels and Ratings</p>
                </div>
                <a href="Dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left"></i> Back to Dashboard
                </a>
            </header>

            <div class="profile-layout">
                <section class="panel profile-card">
                    <form id="avatar-form" method="POST" enctype="multipart/form-data" style="display: contents;">
                    <div class="avatar-uploader">
                        <div class="avatar-preview-container">
                            <?php if (!empty($profilePic)): ?>
                                <img id="avatar-preview" src="../<?php echo $profilePic; ?>" alt="Profile Picture">
                            <?php else: ?>
                                <div id="avatar-fallback" class="avatar-fallback"><?php echo $initials; ?></div>
                            <?php endif; ?>
                        </div>
                        <label for="avatar-input" class="upload-label">
                            <i data-lucide="camera"></i> Change Photo
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                    </div>
                    </form>

                    <h2 id="preview-name" class="profile-name"><?php echo $full_name; ?></h2>
                    <p class="profile-role">Verified Landlord</p>
                </section>

                <section class="panel form-panel">
                    <h2>Profile Information</h2>
                    <form id="profile-form" method="POST" action="Profile.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="landlord-name">Full Name</label>
                                <input type="text" id="landlord-name" name="landlord_name" value="<?php echo $full_name; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="landlord-email">Email Address</label>
                                <input type="email" id="landlord-email" name="landlord_email" value="<?php echo $email; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="landlord-phone">Phone Number</label>
                                <input type="tel" id="landlord-phone" name="landlord_phone" value="<?php echo $phone; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="landlord-hostels">Hostels</label>
                                <input type="text" id="landlord-hostels" value="<?php echo htmlspecialchars($hostelsDisplay); ?>" readonly style="opacity: 0.7; cursor: not-allowed;">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="action-btn">
                                <i data-lucide="save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>