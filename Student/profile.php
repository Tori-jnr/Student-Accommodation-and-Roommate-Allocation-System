<?php session_start();

// 1. SECURITY: If no one is logged in, redirect them back to the login page
if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.html");
    exit();
}

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'roomly_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the REAL logged-in student's ID from the session!
$student_id = $_SESSION['student_id']; 

// 1. IF THE USER CLICKS "SAVE CHANGES" (Process the form)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $conn->real_escape_string($_POST['student_name']);
    $new_email = $conn->real_escape_string($_POST['student_email']);
    $new_phone = $conn->real_escape_string($_POST['student_phone']);
    $new_univ = $conn->real_escape_string($_POST['student_university']);
    $new_bio = $conn->real_escape_string($_POST['student_bio']);

    $update_sql = "UPDATE students SET full_name='$new_name', email='$new_email', phone='$new_phone', university='$new_univ', bio='$new_bio' WHERE student_id='$student_id'";
    $conn->query($update_sql);
}

// 2. FETCH LATEST DATA TO DISPLAY ON SCREEN
$result = $conn->query("SELECT * FROM students WHERE student_id = '$student_id'");
$student = $result->fetch_assoc();

// Set PHP variables to inject into the HTML below
$full_name = htmlspecialchars($student['full_name'] ?? '');
$email = htmlspecialchars($student['email'] ?? '');
$phone = htmlspecialchars($student['phone'] ?? '');
$university = htmlspecialchars($student['university'] ?? '');
$bio = htmlspecialchars($student['bio'] ?? '');

// Calculate initials for the avatar fallback (e.g. "AO")
$initials = "U"; // Default to 'U' if their name is somehow blank
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
    <title>Edit Profile | Roomly</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
    <!-- Lucide Icons for consistent design language -->
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
                    <a href="dashboard.php">
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

        <!-- Main Workspace -->
        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text">
                    <h1>My Profile</h1>
                    <p>Your accommodation preference details at a glance.</p>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i data-lucide="arrow-left"></i> Back to Dashboard
                </a>
            </header>

            <div class="profile-layout">
                <!-- Left Column: Avatar & Card Preview -->
                <section class="panel profile-card">
                    <div class="avatar-uploader">
                        <div class="avatar-preview-container">
                            <img id="avatar-preview" src="" alt="Profile Picture" style="display: none;">
                            <div id="avatar-fallback"><?php echo $initials; ?></div>
                        </div>
                        <label for="avatar-input" class="upload-label">
                            <i data-lucide="camera"></i> Change Photo
                        </label>
                        <input type="file" id="avatar-input" accept="image/*" style="display: none;">
                    </div>

                    <h2 id="preview-name" class="profile-name"><?php echo $full_name; ?></h2>
                    <p class="profile-role">Verified Student</p>
                    <p id="preview-bio" class="profile-bio"><?php echo $bio; ?></p>
                </section>

                <!-- Right Column: Settings Form -->
                <section class="panel form-panel">
                    <h2>Profile Information</h2>
                     <!-- Form now uses POST to send data back to itself -->
                    <form id="profile-form" method="POST" action="profile.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="student-name">Full Name</label>
                                <input type="text" id="student-name" name="student_name" value="<?php echo $full_name; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="student-email">Email Address</label>
                                <input type="email" id="student-email" name="student_email" value="<?php echo $email; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="student-phone">Phone Number</label>
                                <input type="tel" id="student-phone" name="student_phone" value="<?php echo $phone; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="student-university">University/Campus</label>
                                <input type="text" id="student-university" name="student_university" value="<?php echo $university; ?>" required>
                            </div>

                            <div class="form-group full-width">
                                <label for="student-bio">Short Bio</label>
                                <textarea id="student-bio" name="student_bio" rows="4"><?php echo $bio; ?></textarea>
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

        // Dynamically update the visual preview as you type
        document.getElementById('student-name').addEventListener('input', function() {
            const currentName = this.value.trim() || "Your Name";
            document.getElementById('preview-name').innerText = currentName;
            
            // Update initials fallback dynamically based on typed name
            const words = currentName.split(" ");
            let initials = "";
            if(words.length > 0 && words[0].length > 0) {
                initials = words[0][0].toUpperCase();
                if(words.length > 1 && words[1].length > 0) {
                    initials += words[1][0].toUpperCase();
                }
            }
            // If they completely erase the name, it defaults to "U" for User instead of "AO"
            document.getElementById('avatar-fallback').innerText = initials || "U";
        });

        document.getElementById('student-bio').addEventListener('input', function() {
            document.getElementById('preview-bio').innerText = this.value || "Write a short bio about yourself...";
        });

        // Basic Profile Picture Preview (Currently visual only, won't save to DB yet)
        document.getElementById('avatar-input').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                    document.getElementById('avatar-preview').style.display = 'block';
                    document.getElementById('avatar-fallback').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>