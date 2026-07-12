<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Logged-in student's name/pic for the header widget
$stmt = mysqli_prepare($conn, "SELECT full_name, profile_pic FROM students WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$fullName   = $student['full_name'] ?? 'Student';
$profilePic = $student['profile_pic'] ?? '';

// This student's own budget, used to decide the "Budget match" chip below
$stmt = mysqli_prepare($conn, "SELECT budget FROM student_preferences WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$myPrefs = mysqli_stmt_get_result($stmt)->fetch_assoc();
$myBudget = $myPrefs['budget'] ?? null;

// This student's compatible roommates, joined to their preferences
$stmt = mysqli_prepare($conn, "
   SELECT
    rm.match_percentage,
    s.full_name,
    s.phone,
    sp.room_type,
    sp.study_style,
    sp.sleep_schedule,
    sp.budget
    FROM roommate_matches rm
    JOIN students s ON rm.matched_student_id = s.student_id
    LEFT JOIN student_preferences sp ON sp.student_id = s.student_id
    WHERE rm.student_id = ?
    ORDER BY rm.match_percentage DESC
");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$matches = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

// Turns "Quiet study environment" into just "Quiet study", etc.
function short_study_label($style) {
    $map = [
        'Quiet study environment'    => 'Quiet study',
        'Flexible study environment' => 'Flexible study',
        'Group study friendly'       => 'Group study',
    ];
    return $map[$style] ?? $style;
}

// Match% -> chip label + colour, same visual tiers as the original mockup
function match_chip($pct) {
    if ($pct >= 90) {
        return ['label' => 'High Match', 'style' => ''];
    } elseif ($pct >= 80) {
        return ['label' => round($pct) . '% Match', 'style' => 'color: #3b82f6; border-color: rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.1);'];
    } else {
        return ['label' => round($pct) . '% Match', 'style' => 'color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.1);'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compatible Students | Roomly</title>
  <link rel="stylesheet" href="../assets/css/matchresults.css">
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
                    <a href="Hostels.php">
                        <i data-lucide="search"></i>
                        Search Hostels
                    </a>
                    <a href="Roommate.php">
                        <i data-lucide="sliders"></i>
                        Preferences
                    </a>
                    <a href="matchresults.php" class="active">
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


    <main class="main">
      <header class="main-header">
        <div class="page-title">

            <h1>Compatible Students</h1>
            <p>Meet peers who share your lifestyle and study habits.</p>
          </div>

<!-- Clickable Interactive User Profile Widget (same pattern as dashboard.php) -->
                <div class="user-profile-widget" onclick="window.location.href='profile.php'" title="View Profile">
                    <div class="profile-container">
                        <img src="<?php echo htmlspecialchars($profilePic); ?>"
                             alt="<?php echo htmlspecialchars($fullName); ?>"
                             class="profile-pic"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-pic-fallback" style="display: none;"><?php echo strtoupper(substr($fullName, 0, 1) . substr(strrchr($fullName, ' '), 1, 1)); ?></div>
                    </div>
                    <span class="username"><?php echo htmlspecialchars($fullName); ?></span>
                </div>
      </header>

      <section class="page">

<div style="display: flex; justify-content: flex-end; margin-bottom: 24px;">
            <a class="button secondary" href="Roommate.php" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px;">
                <i data-lucide="settings-2" style="width: 16px; height: 16px;"></i> Edit preferences
            </a>
        </div>


        <div class="three-column">

          <?php if (count($matches) > 0): ?>
            <?php foreach ($matches as $m): ?>
              <?php $chip = match_chip($m['match_percentage']); ?>
              <article class="match-card panel">
                <div class="match-head" style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                  <div>
                    <h3><?php echo htmlspecialchars($m['full_name']); ?></h3>
                    <p class="muted">Seeking a <?php echo htmlspecialchars($m['room_type'] ?? 'room'); ?></p>
                  </div>
                  <span class="chip success" style="<?php echo $chip['style']; ?>"><?php echo $chip['label']; ?></span>
                </div>
                <div class="meta-list" style="margin-bottom: 15px;">
    <?php if (!empty($m['study_style'])): ?>
        <span class="chip"><?php echo htmlspecialchars(short_study_label($m['study_style'])); ?></span>
    <?php endif; ?>

    <?php if (!empty($m['sleep_schedule'])): ?>
        <span class="chip"><?php echo htmlspecialchars($m['sleep_schedule']); ?></span>
    <?php endif; ?>

    <?php if ($myBudget !== null && $m['budget'] !== null && abs($m['budget'] - $myBudget) <= 5000): ?>
        <span class="chip">Budget match</span>
    <?php endif; ?>
</div>

<div style="
    text-align:center;
    margin-top:18px;
    padding:14px;
    border-top:1px solid rgba(255,255,255,.08);
">
    <div style="
        font-size:13px;
        color:#94a3b8;
        margin-bottom:6px;
    ">
        Contact
    </div>

    <div style="
        font-size:18px;
        font-weight:600;
        color:#ffffff;
        letter-spacing:.5px;
    ">
        <?php echo htmlspecialchars($m['phone']); ?>
    </div>
</div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="muted">No compatible roommates found yet. Save your preferences to get matched.</p>
          <?php endif; ?>

        </div>
      </section>
    </main>
  </div>
  
  <script> lucide.createIcons() </script>

</body>
</html>