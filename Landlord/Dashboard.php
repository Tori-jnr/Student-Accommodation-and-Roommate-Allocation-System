<?php
session_start();

if (!isset($_SESSION['student_id']) || ($_SESSION['role'] ?? '') !== 'landlord') {
    header("Location: ../Login.html");
    exit();
}

$host = "127.0.0.1"; $username = "root"; $password = ""; $dbname = "roomly_db"; $port = 3307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$landlord_id = $_SESSION['student_id'];

$landlordRow = $conn->query("SELECT name FROM landlords WHERE landlord_id = '$landlord_id'")->fetch_assoc();
$landlordName = $landlordRow['name'] ?? 'Property Manager';

$flash = '';

// ---- Handle "List New Accommodation" form submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_listing') {
    $title       = $conn->real_escape_string(trim($_POST['title'] ?? ''));
    $location    = $conn->real_escape_string(trim($_POST['location'] ?? ''));
    $layout      = $conn->real_escape_string(trim($_POST['layout'] ?? 'Single Room'));
    $price       = intval($_POST['price'] ?? 0);
    $amenities   = $conn->real_escape_string(trim($_POST['amenities'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $panorama    = $conn->real_escape_string(trim($_POST['virtual_tour'] ?? ''));

    if ($title === '' || $location === '' || $price <= 0 || empty($_FILES['photos']['name'][0])) {
        $flash = 'error';
    } else {
        // Save uploaded photos to disk
        $uploadDir = '../uploads/hostels/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $savedPaths = [];
        $fileCount = count($_FILES['photos']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $mime = mime_content_type($_FILES['photos']['tmp_name'][$i]);
            if (!in_array($mime, $allowedTypes)) continue;
            $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
            $safeName = 'h_' . uniqid() . '_' . $i . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $uploadDir . $safeName)) {
                $savedPaths[] = 'uploads/hostels/' . $safeName;
            }
        }

        if (empty($savedPaths)) {
            $flash = 'error';
        } else {
            $coverImage = $conn->real_escape_string($savedPaths[0]);

            $conn->query("
                INSERT INTO hostels (landlord_id, landlord, name, location, description, amenities, image_path, panorama_link, verified)
                VALUES ('$landlord_id', '$landlordName', '$title', '$location', '$description', '$amenities', '$coverImage', '$panorama', 0)
            ");
            $hostel_id = $conn->insert_id;

            foreach ($savedPaths as $path) {
                $safePath = $conn->real_escape_string($path);
                $conn->query("INSERT INTO hostel_photos (hostel_id, image_path) VALUES ($hostel_id, '$safePath')");
            }

            $conn->query("INSERT INTO rooms (hostel_id, room_type, price, status) VALUES ($hostel_id, '$layout', $price, 'available')");

            $safeTitle = $conn->real_escape_string($title);
            $conn->query("INSERT INTO activity_log (student_id, activity_type, activity_title, activity_description) VALUES (1, 'listing', 'New Listing Submitted', 'Hostel listing \'$safeTitle\' was submitted by $landlordName and is awaiting admin verification.')");

            $flash = 'created';
        }
    }
}

// ---- Handle room availability toggle ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $room_id = intval($_POST['room_id'] ?? 0);
    $check = $conn->query("
        SELECT r.room_id, r.status FROM rooms r
        JOIN hostels h ON r.hostel_id = h.hostel_id
        WHERE r.room_id = $room_id AND h.landlord_id = '$landlord_id'
    ")->fetch_assoc();
    if ($check) {
        $newStatus = $check['status'] === 'available' ? 'occupied' : 'available';
        $conn->query("UPDATE rooms SET status = '$newStatus' WHERE room_id = $room_id");
    }
}

// ---- Fetch this landlord's listings ----
$listingsResult = $conn->query("
    SELECT h.hostel_id, h.name, h.location, h.verified, h.image_path,
           r.room_id, r.room_type, r.price, r.status AS room_status
    FROM hostels h
    JOIN rooms r ON r.hostel_id = h.hostel_id
    WHERE h.landlord_id = '$landlord_id'
    ORDER BY h.created_at DESC
");
$listingRows = [];
while ($row = $listingsResult->fetch_assoc()) {
    $listingRows[] = $row;
}

$totalCount = count($listingRows);
$verifiedCount = count(array_filter($listingRows, fn($r) => intval($r['verified']) === 1));
$pendingCount = $totalCount - $verifiedCount;
$occupiedCount = count(array_filter($listingRows, fn($r) => $r['room_status'] === 'occupied'));
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
                <a href="Student Review.html">
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
                <div class="stat-card"><h3>Total Properties</h3><p class="cyan-glow"><?php echo $totalCount; ?></p></div>
                <div class="stat-card"><h3>Verified &amp; Live</h3><p class="blue-glow"><?php echo $verifiedCount; ?></p></div>
                <div class="stat-card"><h3>Pending Verification</h3><p class="yellow-glow"><?php echo $pendingCount; ?></p></div>
                <div class="stat-card"><h3>Occupied Rooms</h3><p class="purple-glow" style="font-size: 1.25rem; margin-top: 6px;"><?php echo $occupiedCount; ?></p></div>
            </section>

            <div class="content-grid">
                <div class="panel">
                    <div class="panel-header"><h2>Your Listings</h2></div>
                    <div style="width: 100%; overflow-x: auto;">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left;">
                                    <th style="padding: 14px 12px;">Hostel</th>
                                    <th style="padding: 14px 12px;">Layout</th>
                                    <th style="padding: 14px 12px;">Price</th>
                                    <th style="padding: 14px 12px;">Verification</th>
                                    <th style="padding: 14px 12px;">Room Status</th>
                                    <th style="padding: 14px 12px; text-align: center;">Controls</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($listingRows)): ?>
                                    <tr><td colspan="6" style="padding: 24px; text-align: center; color: var(--text-secondary);">No accommodations listed yet. Use the form to add your first hostel.</td></tr>
                                <?php else: foreach ($listingRows as $row): ?>
                                    <tr style="border-bottom: 1px solid var(--glass-border);">
                                        <td style="padding: 16px 12px; font-weight: 600;"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td style="padding: 16px 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($row['room_type']); ?></td>
                                        <td style="padding: 16px 12px; font-weight: 600; color: var(--neon-cyan);">KES <?php echo number_format($row['price']); ?></td>
                                        <td style="padding: 16px 12px;">
                                            <?php if ($row['verified']): ?>
                                                <span style="color: var(--success); font-weight: 600; font-size: 0.85rem;">● Verified</span>
                                            <?php else: ?>
                                                <span style="color: var(--warning); font-weight: 600; font-size: 0.85rem;">● Pending Admin Verification</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 16px 12px; text-transform: capitalize;"><?php echo htmlspecialchars($row['room_status']); ?></td>
                                        <td style="padding: 16px 12px; text-align: center;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                                <input type="hidden" name="action" value="toggle">
                                                <button type="submit" class="btn btn-secondary" style="font-size: 0.75rem; padding: 6px 12px;">
                                                    <?php echo $row['room_status'] === 'available' ? 'Mark Occupied' : 'Mark Available'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
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
                                        <option value="2 bedroom">2 bedroom</option>
                                        <option value="4 bedroom">4 bedroom</option>
                                        <option value="Studio">Studio</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="room-price">Price (Per Sem)</label>
                                    <input type="number" id="room-price" name="price" placeholder="Amount (KSH)" required min="1">
                                </div>
                            </div>
                            <div>
                                <label for="amenities">Amenities</label>
                                <input type="text" id="amenities" name="amenities" placeholder="e.g., WiFi, Water, Security, Parking">
                            </div>
                            <div>
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" placeholder="Tell students about this hostel..." style="width: 100%; padding: 10px; background: rgba(0,0,0,0.25); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff;"></textarea>
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