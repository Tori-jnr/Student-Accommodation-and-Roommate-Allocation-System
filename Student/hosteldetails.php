<?php
//confirm student login and get student id for activity capture
session_start();
require '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$id = (int)$_GET['id'];



if (!isset($_GET['id'])) {
    die("Hostel not found.");
}

$hostel_id = (int)$_GET['id'];

$sql = "SELECT h.*, r.room_type, r.price, r.status
        FROM hostels h
        JOIN rooms r ON h.hostel_id = r.hostel_id
        WHERE h.hostel_id = $hostel_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0){
    die("Hostel not found.");
}

$row = mysqli_fetch_assoc($result);

//activity capture for hostel view
$description = "Viewed " . $row['name'];
mysqli_query($conn,"
INSERT INTO activity_log
(student_id,activity_type,activity_title,activity_description)
VALUES
(
$student_id,
'view',
'Viewed hostel',
'Viewed ".$row['name']."'
)");


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hostel Details | Student Housing Portal</title>
  <link rel="stylesheet" href="../assets/css/hosteldetails.css">
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
          <a class="active" href="Hostels.php">
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
            <h1 data-hostel-name><?php echo htmlspecialchars($row['name']); ?></h1>
            <p data-hostel-location><?php echo htmlspecialchars($row['location']); ?></p>
          </div>
        </div>
        <a class="button secondary" href="hostels.php">Back to listings</a>
      </header>

      <section class="page">
        <div class="detail-layout">
          <section class="detail-main">
            <div class="detail-hero"
              style="background-image:url('<?php echo htmlspecialchars($row['image_path']); ?>');">
            </div>
            <div class="panel">
              <div class="section-title">
                <div>
                  <h2>Hostel information</h2>
                  <p>Verified by system administrator.</p>
                </div>
                <span class="chip success" data-status>Verified</span>
              </div>
              <div class="three-column">
                <div class="info-block">
                  <strong>Room type</strong>
                  <p class="muted" data-room-type><?php echo htmlspecialchars($row['room_type']); ?></p>
                </div>
                <div class="info-block">
                  <strong>Availability</strong>
                  <p class="muted" data-availability><?php echo htmlspecialchars($row['status']); ?></p>
                </div>
                <div class="info-block">
                  <strong>Price</strong>
                  <p class="muted" data-price>KES <?php echo number_format($row['price']); ?> per semester</p>
                </div>
              </div>
            </div>

            <div class="panel">
              <div class="section-title">
                <h2>Student reviews</h2>
              </div>
              <div class="review-list" data-review-list>
                <div class="review-item">
                  <strong>4.5 stars - Clean and close to campus</strong>
                  <span class="muted">Security is reliable and rooms are quiet in the evening.</span>
                </div>
                <div class="review-item">
                  <strong>4 stars - Good transport access</strong>
                  <span class="muted">The caretaker responds quickly when there is an issue.</span>
                </div>
              </div>
            </div>
          </section>

          <aside class="panel action-panel">
            <div class="section-title">
              <div>
                <h2>Actions</h2>
                <p>Continue your accommodation process.</p>
              </div>
            </div>
            <div class="timeline">
              <a class="button"
                  href="virtualtour.php?id=<?php echo $row['hostel_id']; ?>">
                  Open Virtual Tour
              </a>
              <a class="button" href="contactlandlord.php" data-contact-link>Contact landlord</a>
            </div>

            <hr class="divider">

            <div class="timeline">
              <div class="timeline-item">
                <strong>Landlord</strong>
                <span class="muted" data-landlord><?php echo htmlspecialchars($row['landlord']); ?></span>
              </div>
              <div class="timeline-item">
                <strong>Amenities</strong>
                <span class="muted" data-amenities><?php echo htmlspecialchars($row['amenities']); ?></span>
              </div>
              <div class="timeline-item">
                <strong>Verification date</strong>
                <span class="chip <?php echo $row['verified'] ? 'success' : 'warning'; ?>">
                    <?php echo $row['verified'] ? 'Verified' : 'Unverified'; ?>
                </span>
              </div>
            </div>
          </aside>
        </div>
      </section>
    </main>
  </div>
  <script src="../assets/js/hosteldetails.js"></script>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
