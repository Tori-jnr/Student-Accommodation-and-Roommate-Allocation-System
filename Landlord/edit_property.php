<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../Login.php");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

if (!isset($_GET['id'])) {
    header("Location: Dashboard.php");
    exit();
}

$property_id = (int)$_GET['id'];

$stmt = $conn->prepare("
SELECT
    p.property_id,
    p.title,
    p.layout,
    p.price,
    p.hostel_images,
    p.virtual_tour_file,

    h.hostel_id,
    h.name,
    h.location,
    h.image_path,
    h.panorama_link,

    r.room_id,
    r.room_type,
    r.amenities

FROM properties p

JOIN hostels h
ON p.property_id=h.property_id

LEFT JOIN rooms r
ON h.hostel_id=r.hostel_id

WHERE p.property_id=?
AND p.landlord_id=?
");

$stmt->bind_param("is",$property_id,$landlord_id);
$stmt->execute();

$data=$stmt->get_result()->fetch_assoc();

if(!$data){
    die("Property not found.");
}


if(isset($_POST['save'])){

$title=$_POST['title'];
$location=$_POST['location'];
$layout=$_POST['layout'];
$price=$_POST['price'];
$amenities=$_POST['amenities'];
$virtual=$_POST['virtual'];

$image=$data['image_path'];

if(!empty($_FILES['photo']['name'])){

    $dir="../uploads/hostels/";

    if(!is_dir($dir)){
        mkdir($dir,0777,true);
    }

    $filename=time()."_".$_FILES['photo']['name'];

    move_uploaded_file(
        $_FILES['photo']['tmp_name'],
        $dir.$filename
    );

    $image="uploads/hostels/".$filename;
}

$stmt=$conn->prepare("
UPDATE properties

SET

title=?,
layout=?,
price=?,
hostel_images=?,
virtual_tour_file=?

WHERE property_id=?
");

$stmt->bind_param(
"ssissi",
$title,
$layout,
$price,
$image,
$virtual,
$property_id
);

$stmt->execute();



$stmt=$conn->prepare("
UPDATE hostels

SET

name=?,
location=?,
image_path=?,
panorama_link=?

WHERE property_id=?
");

$stmt->bind_param(
"ssssi",
$title,
$location,
$image,
$virtual,
$property_id
);

$stmt->execute();



$stmt=$conn->prepare("
UPDATE rooms

SET

room_type=?,
price=?,
amenities=?

WHERE hostel_id=?
");

$stmt->bind_param(
"sisi",
$layout,
$price,
$amenities,
$data['hostel_id']
);

$stmt->execute();

header("Location: Dashboard.php");
exit();

}
?>
<!DOCTYPE html>

<html>

<head>

<title>Edit Property</title>

<link rel="stylesheet" href="../assets/css/Landlord Dashboard.css">
<style>
    /* Scoped fixes for the Edit Property page only */
    .edit-container .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }
    .edit-title {
        margin-bottom: 6px;
    }
    .edit-subtitle {
        margin-top: 6px;
    }
    .edit-container .back-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        padding: 0;
        border-radius: 8px;
        flex-shrink: 0;
        font-size: 18px;
        text-decoration: none;
    }
</style>
</head>

<body>

<div class="dashboard-shell">

<div class="main-content">

<div class="edit-container">

<div class="page-header">

<div>

<h1 class="edit-title">
Edit Property
</h1>

<p class="edit-subtitle">
Update your property information.
</p>

</div>

<a href="Dashboard.php" class="back-btn" title="Back to Dashboard" aria-label="Back to Dashboard">
←
</a>

</div>


<div class="profile-layout">

<!-- LEFT PANEL -->

<div class="panel profile-card">

<div class="avatar-preview-container">

<?php if(!empty($data['image_path'])): ?>

<img
id="previewImage"
src="../<?= htmlspecialchars($data['image_path']) ?>">

<?php else: ?>

<div class="avatar-fallback">

🏠

</div>

<?php endif; ?>

</div>

<h2 class="profile-name">

<?= htmlspecialchars($data['title']) ?>

</h2>

<p class="profile-role">

Property

</p>

<p class="profile-bio">

<?= htmlspecialchars($data['location']) ?>

<br><br>

KES <?= number_format($data['price']) ?>

</p>

</div>


<!-- RIGHT PANEL -->

<div class="panel form-panel">

<h2>Property Details</h2>

<form method="POST" enctype="multipart/form-data">

<div class="form-grid">

<div class="form-group">

<label>Hostel Name</label>

<input
type="text"
name="title"
value="<?= htmlspecialchars($data['title']) ?>">

</div>


<div class="form-group">

<label>Location</label>

<input
type="text"
name="location"
value="<?= htmlspecialchars($data['location']) ?>">

</div>


<div class="form-group">

<label>Room Type</label>

<input
type="text"
name="layout"
value="<?= htmlspecialchars($data['layout']) ?>">

</div>


<div class="form-group">

<label>Price</label>

<input
type="number"
name="price"
value="<?= $data['price'] ?>">

</div>


<div class="form-group full-width">

<label>Amenities</label>

<textarea
name="amenities"><?= htmlspecialchars($data['amenities']) ?></textarea>

</div>


<div class="form-group full-width">

<label>Virtual Tour Link</label>

<input
type="text"
name="virtual"
value="<?= htmlspecialchars($data['virtual_tour_file']) ?>">

</div>


<div class="form-group full-width">

<label>Replace Image</label>

<input
type="file"
name="photo"
id="photoInput">

</div>

</div>


<div class="form-actions">

<a
href="Dashboard.php"
class="cancel-btn">

Cancel

</a>

<button
type="submit"
name="save"
class="action-btn">

💾 Save Changes

</button>

</div>

</form>

</div>

</div>

</div>

</div>

<script>

const photo=document.getElementById("photoInput");

if(photo){

photo.addEventListener("change",function(){

const file=this.files[0];

if(file){

const reader=new FileReader();

reader.onload=function(e){

const img=document.getElementById("previewImage");

if(img){

img.src=e.target.result;

}

}

reader.readAsDataURL(file);

}

});

}

</script>

</body>
</html>