<?php
require '../db_connect.php';

$id = (int)$_GET['id'];

$sql = "SELECT
            h.hostel_id,
            h.name,
            h.location,
            h.panorama_link,
            r.room_type,
            r.price,
            r.status
        FROM hostels h
        JOIN rooms r ON h.hostel_id = r.hostel_id
        WHERE h.hostel_id = $id
        LIMIT 1";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);

//confirm student login and get student id for activity capture
session_start();
require '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$id = (int)$_GET['id'];




//insert into activity table 
mysqli_query($conn,"
INSERT INTO activity_log
(student_id,activity_type,activity_title,activity_description)
VALUES
(
$student_id,
'tour',
'Virtual tour viewed',
'Viewed ".$row['name']." virtual tour'
)");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Tour | Roomly</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
  <link rel="stylesheet" href="../assets/css/virtualtour.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
     <h2 class="logo">Roomly<span class="dot">.</span></h2>

      <div class="nav-container">
        <span class="nav-heading">Student Menu</span>
        <nav class="nav-links" aria-label="Student navigation">
          <a href="dashboard.php">
            <i data-lucide="layout-dashboard"></i>
            Dashboard
          </a>
          <a  href="hostels.php" class="active">
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
            <h1><?php echo htmlspecialchars($row['name']); ?> Tour</h1>
            <p>Explore the space before you move in.</p>
          </div>
        </div>
<a class="button secondary" id="back-link" href="hosteldetails.php?id=<?php echo $row['hostel_id']; ?>">Back to details</a>
<script>
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (id) document.getElementById('back-link').href = `hosteldetails.php?id=${id}`;
</script>

</header>

      <section class="page">
        <div class="detail-layout">
          <section>
            <div id="panorama" style="width: 100%; height: 500px; border-radius: 8px;"></div>
          </section>
          <aside class="panel">
            <div class="section-title">
              <h2>Tour details</h2>
            </div>
            <div class="timeline">
              <div class="timeline-item">
                <strong>Hostel</strong>
               <span class="muted"><?php echo htmlspecialchars($row['name']); ?></span>
              </div>
              <div class="timeline-item">
                <strong>Room type</strong>
                <span class="muted"><?php echo htmlspecialchars($row['room_type']); ?></span>
              </div>
            </div>
            
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
            
            <p style="margin-bottom: 15px;">Like what you see? Reach out to secure your spot.</p>
            <div class="button-row">
              <a class="button" href="#">Contact Landlord</a>
            </div>
          </aside>
        </div>
      </section>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
  <script>
    pannellum.viewer('panorama', {
        "type": "equirectangular",
        "panorama": "<?php echo htmlspecialchars($row['panorama_link']); ?>",
        "autoLoad": true
    });
  </script>
  <script src="../assets/js/virtualtour.js"></script>
  <script> lucide.createIcons(); </script>
</body>
</html>