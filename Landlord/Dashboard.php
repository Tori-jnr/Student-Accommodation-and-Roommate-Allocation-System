<?php
session_start();




if (!isset($_SESSION['landlord_id']) || ($_SESSION['role'] ?? '') !== 'landlord') {
    header("Location: ../Login.php");
    exit();
}

require_once "../db_connect.php";

$landlord_id = $_SESSION['landlord_id'];




//Display landlord information


//----------------------------------------------------
// Get landlord information
//----------------------------------------------------

$stmt = $conn->prepare("
    SELECT full_name
    FROM landlords
    WHERE landlord_id = ?
");

$stmt->bind_param("s", $landlord_id);
$stmt->execute();

$result = $stmt->get_result();
$landlord = $result->fetch_assoc();

$landlordName = $landlord['full_name'] ?? "Landlord";

$flash = "";


//----------------------------------------------------
// ADD NEW PROPERTY
//----------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_listing') {

    $title      = trim($_POST['title']);
    $location   = trim($_POST['location']);
    $layout     = trim($_POST['layout']);
    $price      = (int)$_POST['price'];
    $amenities  = trim($_POST['amenities']);
    $virtual    = trim($_POST['virtual_tour']);

    if ($title == "" || $location == "" || $price <= 0) {

        $flash = "error";

    } else {

        //------------------------------------------------
        // Upload image
        //------------------------------------------------

        $imagePath = "";

        if (!empty($_FILES['photos']['name'][0])) {

            $uploadDir = "../uploads/hostels/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES['photos']['name'][0]);

            $target = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photos']['tmp_name'][0], $target)) {

                $imagePath = "uploads/hostels/" . $fileName;

            }

        }


        //------------------------------------------------
        // Insert into PROPERTIES
        //------------------------------------------------

        $stmt = $conn->prepare("
            INSERT INTO properties
            (
                landlord_id,
                title,
                layout,
                price,
                status,
                status_color,
                hostel_images,
                virtual_tour_file
            )
            VALUES
            (?, ?, ?, ?, 'Available', 'green', ?, ?)
        ");

        $stmt->bind_param(
            "sssiss",
            $landlord_id,
            $title,
            $layout,
            $price,
            $imagePath,
            $virtual
        );

        $stmt->execute();

        $property_id = $conn->insert_id;


        //------------------------------------------------
        // Insert into HOSTELS
        //------------------------------------------------

        $verificationCode = strtoupper(substr(md5(time()),0,8));

        $stmt = $conn->prepare("
            INSERT INTO hostels
            (
                property_id,
                landlord_id,
                name,
                location,
                verified,
                verification_code,
                image_path,
                panorama_link
            )
            VALUES
            (?, ?, ?, ?, 0, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssss",
            $property_id,
            $landlord_id,
            $title,
            $location,
            $verificationCode,
            $imagePath,
            $virtual
        );

        $stmt->execute();

        $hostel_id = $conn->insert_id;


        //------------------------------------------------
        // Insert room
        //------------------------------------------------

        $stmt = $conn->prepare("
            INSERT INTO rooms
            (
                hostel_id,
                room_type,
                price,
                status,
                amenities
            )
            VALUES
            (?, ?, ?, 'available', ?)
        ");

        $stmt->bind_param(
            "isis",
            $hostel_id,
            $layout,
            $price,
            $amenities
        );

        $stmt->execute();

        $flash = "created";

    }

}



//----------------------------------------------------
// Dashboard Statistics
//----------------------------------------------------

$totalProperties = 0;
$verifiedCount = 0;
$pendingCount = 0;
$occupiedRooms = 0;

$result = $conn->query("
SELECT COUNT(*) total
FROM properties
WHERE landlord_id='$landlord_id'
");

if($row = $result->fetch_assoc()){
    $totalProperties = $row['total'];
}


$result = $conn->query("
SELECT COUNT(*) total
FROM hostels
WHERE landlord_id='$landlord_id'
AND verified=1
");

if($row = $result->fetch_assoc()){
    $verifiedCount = $row['total'];
}

$pendingCount = $totalProperties - $verifiedCount;


$result = $conn->query("
SELECT COUNT(*) total
FROM rooms r
JOIN hostels h
ON r.hostel_id=h.hostel_id
WHERE h.landlord_id='$landlord_id'
AND r.status='occupied'
");

if($row = $result->fetch_assoc()){
    $occupiedRooms = $row['total'];
}



//----------------------------------------------------
// Fetch landlord listings
//----------------------------------------------------

$listingRows = [];

$stmt = $conn->prepare("
SELECT
    p.property_id,
    h.hostel_id,
    h.name,
    h.location,
    h.verified,
    h.image_path,

    r.room_id,
    r.room_type,
    r.price,
    r.status AS room_status

FROM properties p

INNER JOIN hostels h
ON p.property_id = h.property_id

LEFT JOIN rooms r
ON h.hostel_id = r.hostel_id

WHERE p.landlord_id = ?

ORDER BY h.created_at DESC
");

$stmt->bind_param("s",$landlord_id);
$stmt->execute();

$result = $stmt->get_result();

while($row=$result->fetch_assoc()){
    $listingRows[]=$row;
}









//----------------------------------------------------
// Toggle room availability
//----------------------------------------------------

if(
    $_SERVER['REQUEST_METHOD']=="POST"
    &&
    isset($_POST['action'])
    &&
    $_POST['action']=="toggle"
){

    $room_id=(int)$_POST['room_id'];

    $stmt=$conn->prepare("
    UPDATE rooms
    SET status=
        CASE
            WHEN status='available'
            THEN 'occupied'
            ELSE 'available'
        END
    WHERE room_id=?
    ");

    $stmt->bind_param("i",$room_id);
    $stmt->execute();

    header("Location: Dashboard.php");
    exit();
}







?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Operations Center - Roomly.</title>
    <link rel="stylesheet" href="../assets/css/Landlord Dashboard.css">
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
                <a href="Dashboard.php" class="active">
                    <svg viewBox="0 0 24 24" class="sidebar-icon"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="Student Review.php">
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

        <main class="main-content">
            <header class="top-header">
                <div class="welcome-text">
                    <small>Landlord Management Suite</small>
                    <h1>Accommodation Matrix</h1>
                </div>
                <div class="user-profile-widget">
                    <div class="profile-container"><?php echo strtoupper(substr($landlordName,0,1)); ?></div>
                    <span class="username"><?php echo htmlspecialchars($landlordName); ?></span>
                </div>
            </header>

            <?php if ($flash === 'created'): ?>
                <div class="alert" style="background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.35); color:#a7f3d0; margin: 0 32px 18px;">Listing submitted. It will show as "Pending Admin Verification" until an admin reviews and approves it.</div>
            <?php elseif ($flash === 'error'): ?>
                <div class="alert" style="margin: 0 32px 18px;">Something went wrong saving your listing. Please check the required fields (title, location, price, at least one photo) and try again.</div>
            <?php endif; ?>

            <section class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon icon-cyan"><svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg></div>
                    <div><h3>Total Properties</h3><p class="cyan-glow"><?php echo $totalProperties; ?></p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg></div>
                    <div><h3>Verified &amp; Live</h3><p class="blue-glow"><?php echo $verifiedCount; ?></p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-yellow"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <div><h3>Pending Verification</h3><p class="yellow-glow"><?php echo $pendingCount; ?></p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-purple"><svg viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <div><h3>Occupied Rooms</h3><p class="purple-glow"><?php echo $occupiedRooms; ?></p></div>
                </div>
            </section>

            <div class="content-grid">
                <div class="panel">
                    <div class="panel-header"><h2>Your Listings</h2></div>
                    <div style="width: 100%; overflow-x: auto;">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align:left;">
                                    <th style="padding:14px;">Image</th>
                                    <th style="padding:14px;">Hostel</th>
                                    <th style="padding:14px;">Location</th>
                                    <th style="padding:14px;">Room</th>
                                    <th style="padding:14px;">Price</th>
                                    <th style="padding:14px;">Verification</th>
                                    <th style="padding:14px;">Status</th>
                                    <th style="padding:14px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listingRows)): ?>
                                    <tr><td colspan="8" style="padding: 24px; text-align: center; color: var(--text-secondary);">No accommodations listed yet. Use the form to add your first hostel.</td></tr>
                                <?php endif; ?>
                                <?php if(empty($listingRows)): ?>

<tr>
<td colspan="8" style="padding:25px;text-align:center;">
No hostels found.
</td>
</tr>

<?php else: ?>

<?php foreach($listingRows as $row): ?>

<tr>

<td style="padding:10px;">

<?php

$rawPath = $row['image_path'];
$hasImage = false;
$imageSrc = "";

if (!empty($rawPath)) {
    if (preg_match('#^https?://#i', $rawPath)) {
        // External URL (e.g. seeded demo images) - use as-is, no local file check
        $imageSrc = $rawPath;
        $hasImage = true;
    } else {
        // Locally uploaded file - verify it actually exists on disk
        $localPath = "../" . ltrim($rawPath, "/");
        if (file_exists($localPath)) {
            $imageSrc = $localPath;
            $hasImage = true;
        }
    }
}

if ($hasImage) {
?>

<img
src="<?php echo htmlspecialchars($imageSrc); ?>"
style="
width:90px;
height:70px;
object-fit:cover;
border-radius:10px;
">

<?php
}
else
{
?>

<div style="
width:90px;
height:70px;
background:#222;
display:flex;
align-items:center;
justify-content:center;
border-radius:10px;
font-size:12px;
">
No Image
</div>

<?php
}
?>

</td>

<td>

<strong>

<?php echo htmlspecialchars($row['name']); ?>

</strong>

</td>

<td>

<?php echo htmlspecialchars($row['location']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['room_type']); ?>

</td>

<td>

KES <?php echo number_format($row['price']); ?>

</td>

<td>

<?php

if($row['verified'])
{
?>

<span style="color:#22c55e;font-weight:bold;">
✔ Verified
</span>

<?php
}
else
{
?>

<span style="color:#f59e0b;font-weight:bold;">
Pending
</span>

<?php
}
?>

</td>

<td>

<?php

if($row['room_status']=="available")
{

?>

<span style="color:#22c55e;">
Available
</span>

<?php

}
else
{

?>

<span style="color:#ef4444;">
Occupied
</span>

<?php

}

?>

</td>

<td>

<div style="display:flex;gap:8px;flex-wrap:wrap;">

<form method="POST">
    <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
    <input type="hidden" name="action" value="toggle">

    <button class="btn btn-secondary">
        <?php echo ($row['room_status']=="available") ? "Mark as Occupied" : "Mark as Vacant"; ?>
    </button>
</form>

<a href="edit_property.php?id=<?php echo $row['property_id']; ?>"
class="btn btn-primary">
Edit
</a>

<a
href="delete_property.php?id=<?php echo $row['property_id']; ?>"
class="btn btn-danger"
onclick="return confirm('Delete this property?');">
Delete
</a>

</div>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 30px;">
                    <div class="panel">
                        <div class="panel-header"><h2>List New Accommodation</h2></div>
                        <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 18px;">
                            <input type="hidden" name="action" value="add_listing">
                            <div>
                                <label for="hostel-name">Hostel / Property Title</label>
                                <input type="text" id="hostel-name" name="title" placeholder="e.g., Greenview Premium Suites" required>
                            </div>
                            <div>
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" placeholder="e.g., Madaraka, 0.5km from campus" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div>
                                    <label for="room-spec">Room Layout</label>
                                    <select id="room-spec" name="layout">
                                        <option value="Single Room">Single Room</option>
                                        <option value="Double Room">Double Room</option>
                                        <option value="Triple Room">Triple Room</option>
                                        <option value="Four Sharing">Four Sharing</option>
                                        <option value="Premium Studio">Premium Studio</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="room-price">Price (Per Month)</label>
                                    <input type="number" id="room-price" name="price" placeholder="Amount (KSH)" required min="1">
                                </div>
                            </div>
                            <div>
                                <label for="amenities">Amenities</label>
                                <input type="text" id="amenities" name="amenities" placeholder="e.g., WiFi, Water, Security, Parking">
                            </div>
                            <div>
                                <label for="property-photos">Upload Hostel Images</label>
                                <input type="file" id="property-photos" name="photos[]" accept="image/*" multiple required style="padding: 8px 12px; background: rgba(0, 0, 0, 0.25);">
                                <small style="color: var(--text-secondary);">First photo becomes the cover image students see.</small>
                            </div>
                            <div>
                                <label for="virtual-tour">Virtual Tour Link (optional)</label>
                                <input id="virtual-tour" name="virtual_tour" type="url" placeholder="https://example.com">
                            </div>
                            <button type="submit" class="btn btn-primary" style="justify-content: center; padding: 14px; margin-top: 6px;">Create Hostel Listing</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>