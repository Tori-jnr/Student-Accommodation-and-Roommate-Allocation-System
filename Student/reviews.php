
<?php
session_start();
require '../db_connect.php';

//checck if user is logged or redirect
if (!isset($_SESSION['student_id'])) {
    header("Location: ../Login.php");
    exit();
}

$student_id = $_SESSION['student_id'];


$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hostel_id = (int)$_POST["hostel_id"];
    $rating = (int)$_POST["rating"];
    $comment = trim($_POST["comment"]);
    $verification_code = trim($_POST["verification_code"]);

    $check = $conn->prepare("SELECT hostel_id FROM hostels WHERE hostel_id=? AND verification_code=? AND verified=1");
    $check->bind_param("is",$hostel_id,$verification_code);
    $check->execute();
    $valid = $check->get_result();

    if($valid->num_rows==0){
        $message="Invalid verification code.";
    }else{
        $dup=$conn->prepare("SELECT review_id FROM reviews WHERE student_id=? AND hostel_id=?");
        $dup->bind_param("ii",$student_id,$hostel_id);
        $dup->execute();
        if($dup->get_result()->num_rows>0){
            $message="You have already reviewed this hostel.";
        }else{
            $ins=$conn->prepare("INSERT INTO reviews(student_id,hostel_id,rating,comment) VALUES(?,?,?,?)");
            $ins->bind_param("iiis",$student_id,$hostel_id,$rating,$comment);
            if($ins->execute()){
                $message="Review submitted successfully.";



                //capturing activities made and insert it to table

                  $stmt = $conn->prepare("INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description) VALUES (?, 'review', 'Review posted', 'Posted a hostel review')");
                  if ($stmt) {
                  $stmt->bind_param("i", $student_id);
                  $stmt->execute();
                   $stmt->close();
                  }
            }else{
                $message="Database error: ".$ins->error;
            }
        }
    }
}










//profile fetching
$stmt = mysqli_prepare($conn,
    "SELECT full_name, profile_pic
     FROM students
     WHERE student_id = ?");

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);




?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews | Roomly</title>
  <link rel="stylesheet" href="../assets/css/reviews.css">
  <script src ="https://unpkg.com/lucide@latest"></script>
</head>
<!--popup message close function-->
<script>
function closePopup(){
    document.getElementById("popup").style.display = "none";
}
</script>
<body>

<!--popup message settings-->
<?php if (!empty($message)) : ?>
<div id="popup" class="popup-overlay">
    <div class="popup-box">
        <h3>Roomly</h3>
        <p><?= htmlspecialchars($message) ?></p>

        <button onclick="closePopup()">OK</button>
    </div>
</div>
<?php endif; ?>


  <div class="app-shell">
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

                            <a href="reviews.php" class ="active">
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
          
            <h1>Hostel reviews</h1>
            <p>Share your experience to help fellow students make informed decisions.</p>
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


      <section class="page">
        <div class="two-column" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
          <form class="panel" method="POST">
           
            <div class="section-title">
              <h2>Submit a review</h2>
              <p class="muted" style="font-size: 0.9em; margin-bottom: 10px;">You can only review verified hostels you have interacted with.</p>

            </div>
            <div class="form-grid">
              <div class="field-full">
                <label for="hostel">Select Hostel</label>

                <!-- filtering only verified hostels -->
                  <?php
                    $hostels = $conn->query("SELECT hostel_id, name FROM hostels WHERE verified = 1");
                  ?>

                <select id="hostel" name="hostel_id">

                  <?php while($row = $hostels->fetch_assoc()){ ?>

                    <option value="<?= $row['hostel_id']; ?>">
                  <?= htmlspecialchars($row['name']); ?>
                    </option>

                  <?php } ?>

              </select>
              </div>
              <div class="field-full">
                <label for="rating">Rating</label>
                <select id="rating" name="rating">
                  <option value="5">5 stars - Excellent</option>
                  <option value="4">4 stars - Good</option>
                  <option value="3">3 stars - Average</option>
                  <option value="2">2 stars - Poor</option>
                  <option value="1">1 star - Terrible</option>
                </select>
              </div>

        <div class="field-full">
                <label for="verification-code">Verification Code</label>
                <input type="text" name="verification_code" id="verification-code" placeholder="Enter code provided by your landlord" required>
              </div>

              <div class="field-full">
                <label for="review">Your Review</label>
                <textarea id="review" name="comment" placeholder="How was the security, water supply, and noise level?" style="height: 100px;"></textarea>
              </div>
            </div>
            <div class="button-row" style="margin-top: 18px;">
              <button class="button" type="submit">Submit review</button>
            </div>
          </form>

          <aside class="panel">
            <div class="section-title">
              <h2>Your past reviews</h2>
            </div>
            <div class="review-list">
              <div class="review-item" style="margin-bottom: 15px; padding-bottom: 15px; ">
                <?php

                  $stmt = $conn->prepare("
SELECT
    hostels.name,
    reviews.rating,
    reviews.comment,
    reviews.created_at
FROM reviews
JOIN hostels
ON reviews.hostel_id = hostels.hostel_id
WHERE reviews.student_id = ?
ORDER BY reviews.created_at DESC
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
?>

<div class="review-item">
    <strong><?= htmlspecialchars($row['name']); ?></strong>

    <span style="color:gold;">
        <?= str_repeat("★", $row['rating']); ?>
    </span>

    <p><?= htmlspecialchars($row['comment']); ?></p>

    <small><?= htmlspecialchars($row['created_at']); ?></small>
</div>

<?php } ?>
              </div>
              
            </div>
          </aside>

        </div>
      </section>
    </main>
  </div>
  <script src="../assets/js/reviews.js"></script>
  <script> lucide.createIcons() </script>

  <script>
    // Sync Profile Data from Local Storage to Dashboard
        window.addEventListener('DOMContentLoaded', () => {
            const savedName = localStorage.getItem('student-name');
            const savedAvatar = localStorage.getItem('student-avatar');

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
