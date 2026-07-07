<?php
session_start();
require '../db_connect.php';
require 'matching.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$message = '';

// Logged-in student's name/pic for the header widget
$stmt = mysqli_prepare($conn, "SELECT full_name, profile_pic FROM students WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$fullName   = $student['full_name'] ?? 'Student';
$profilePic = $student['profile_pic'] ?? '';

// ---- If the form was submitted, save it ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $roomType   = $_POST['room_type'];
    $gender     = $_POST['gender'];
    $budget     = $_POST['budget'];
    $studyStyle = $_POST['study'];
    $sleep      = $_POST['sleep'];
    $lifestyle  = $_POST['lifestyle'];

    // Does this student already have a saved preferences row?
    $check = mysqli_prepare($conn, "SELECT pref_id FROM student_preferences WHERE student_id = ?");
    mysqli_stmt_bind_param($check, "i", $student_id);
    mysqli_stmt_execute($check);
    $existing = mysqli_stmt_get_result($check)->fetch_assoc();

    if ($existing) {
        // Update the existing row
        $stmt = mysqli_prepare($conn, "
            UPDATE student_preferences
            SET room_type = ?, preferred_gender = ?, budget = ?, study_style = ?, sleep_schedule = ?, lifestyle_notes = ?
            WHERE student_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ssdsssi", $roomType, $gender, $budget, $studyStyle, $sleep, $lifestyle, $student_id);
    } else {
        // Insert a new row
        $stmt = mysqli_prepare($conn, "
            INSERT INTO student_preferences (student_id, room_type, preferred_gender, budget, study_style, sleep_schedule, lifestyle_notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "issdsss", $student_id, $roomType, $gender, $budget, $studyStyle, $sleep, $lifestyle);
    }
    mysqli_stmt_execute($stmt);

    // Log this action for the dashboard's Activity feed
    $logStmt = mysqli_prepare($conn, "
        INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description)
        VALUES (?, 'preference', 'Preferences updated', 'Updated roommate preferences')
    ");
    mysqli_stmt_bind_param($logStmt, "i", $student_id);
    mysqli_stmt_execute($logStmt);

    $message = 'Preferences saved!';
    calculate_matches($conn, $student_id);
}

// ---- Load current preferences (whether just saved, or from before) to pre-fill the form ----
$stmt = mysqli_prepare($conn, "SELECT * FROM student_preferences WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$prefs = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Helper: prints selected="selected" if this option matches the saved value
function sel($savedValue, $optionValue) {
    return ($savedValue === $optionValue) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roommate Preferences | Roomly</title>
  <link rel="stylesheet" href="../assets/css/roommate.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <div class="dashboard-shell">
    <aside class="sidebar">
      <h2 class="logo">Roomly<span class="dot">.</span></h2>

      <div class="nav-container">
        <span class="nav-heading">Student Menu</span>
        <nav class="nav-links" aria-label="Student navigation">
          <a href="dashboard.php">
            <i data-lucide="layout-dashboard"></i>
            Dashboard
          </a>
          <a href="Hostels.php">
            <i data-lucide="search"></i>
            Search Hostels
          </a>
          <a class="active" href="Roommate.php">
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
        <nav class="nav-links" aria-label="Account navigation">
          <a href="../logout.php" class="logout-link">
            <i data-lucide="log-out"></i>
            Logout
          </a>
        </nav>
      </div>
    </aside>

    <main class="main">
      <header class="main-header">
        <div class="page-title">
          <div>
            <h1>Roommate preferences</h1>
            <p>Find your perfect living companion.</p>
          </div>
        </div>

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

      <section class="page">

        <?php if ($message): ?>
          <p style="color:#4ade80; margin-bottom: 15px;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form class="panel" method="POST" action="Roommate.php">
          <div class="form-grid">
            <div class="field">
              <label for="room-type">Preferred Room Type</label>
              <select id="room-type" name="room_type">
                
                <option <?php echo sel($prefs['room_type'] ?? '', 'Double'); ?>>Double</option>
                <option <?php echo sel($prefs['room_type'] ?? '', 'Triple'); ?>>Triple</option>
                <option <?php echo sel($prefs['room_type'] ?? '', '4-sharing'); ?>>4-sharing</option>
              </select>
            </div>
            <div class="field">
              <label for="gender">Preferred roommate gender</label>
              <select id="gender" name="gender">
                <option <?php echo sel($prefs['preferred_gender'] ?? '', 'Female'); ?>>Female</option>
                <option <?php echo sel($prefs['preferred_gender'] ?? '', 'Male'); ?>>Male</option>
                <option <?php echo sel($prefs['preferred_gender'] ?? '', 'No preference'); ?>>No preference</option>
              </select>
            </div>
            <div class="field">
              <label for="budget">Individual budget per month (KES)</label>
              <input id="budget" name="budget" type="number" placeholder="e.g. 45000"
                     value="<?php echo htmlspecialchars($prefs['budget'] ?? ''); ?>">
            </div>
            <div class="field">
              <label for="study">Study style</label>
              <select id="study" name="study">
                <option <?php echo sel($prefs['study_style'] ?? '', 'Quiet study environment'); ?>>Quiet study environment</option>
                <option <?php echo sel($prefs['study_style'] ?? '', 'Flexible study environment'); ?>>Flexible study environment</option>
                <option <?php echo sel($prefs['study_style'] ?? '', 'Group study friendly'); ?>>Group study friendly</option>
              </select>
            </div>
            <div class="field">
              <label for="sleep">Sleep schedule</label>
              <select id="sleep" name="sleep">
                <option <?php echo sel($prefs['sleep_schedule'] ?? '', 'Early sleeper'); ?>>Early sleeper</option>
                <option <?php echo sel($prefs['sleep_schedule'] ?? '', 'Late sleeper'); ?>>Late sleeper</option>
                <option <?php echo sel($prefs['sleep_schedule'] ?? '', 'Flexible'); ?>>Flexible</option>
              </select>
            </div>
            <div class="field-full">
              <label for="lifestyle">Lifestyle preference</label>
              <textarea id="lifestyle" name="lifestyle" placeholder="Cleanliness, visitors, noise tolerance, study habits..."><?php echo htmlspecialchars($prefs['lifestyle_notes'] ?? ''); ?></textarea>
            </div>
          </div>
          <div class="button-row" style="margin-top: 20px;">
            <button class="button" type="submit">Save preferences</button>
            <a class="button secondary" href="dashboard.php">Cancel</a>
          </div>
        </form>
      </section>
    </main>
  </div>

  <script src="../assets/js/roommate.js"></script>
  <script> lucide.createIcons(); </script>

</body>
</html>