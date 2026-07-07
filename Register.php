<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roomly | Register</title>
  <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="landingpage.html">
      <span class="brand-mark">R</span>
      <span>Roomly <small>Accommodation and roommate allocation</small></span>
    </a>
    <nav class="nav-pills" aria-label="Main navigation">
      <a href="Login.html">Login</a>
      <a class="active" href="Register.php">Register</a>
    </nav>
  </header>

  <main class="auth-page">
    <section class="auth-panel">
      <form class="auth-card" method="POST" action="register_process.php">
        <div class="signup-text">
    <h1>Where Comfort Meets Community</h1>
    <p>Create your account and find a place that feels like home.</p>
</div>


 <!-- DYNAMIC PHP ERROR ALERTS -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert">
                 <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>


        <div class="form-grid" style="margin-top: 24px;">
          <div class="field">
            <label for="full-name">Full name</label>
            <input id="full-name" type="text" name="full_name" placeholder="Full name" required>
          </div>
          <div class="field">
            <label for="phone">Phone number</label>
            <input id="phone" type="tel" name="phone" placeholder="07..." required>
          </div>
          <div class="field-full">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" placeholder="name@example.com" required>
          </div>
          <div class="field">
            <label for="role">Account type</label>
            <select id="role" name="role" required>
              <option value="student">Student</option>
              <option value="landlord">Landlord</option>
            </select>
          </div>
          <div class="field">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
              <option value="">Select gender</option>
              <option value="female">Female</option>
              <option value="male">Male</option>
            </select>
          </div>
          <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Create password" required>
          </div>
          <div class="field">
            <label for="confirm-password">Confirm password</label>
            <input id="confirm-password" type="password" name="confirm_password" placeholder="Confirm password" required>
          </div>
        </div>

        <div class="button-row" style="margin-top: 22px;">
          <button class="button" type="submit">Sign up</button>
          <a class="button secondary" href="Login.html">Back to login</a>
        </div>
      </form>
    </section>

    <section class="auth-visual" aria-label="Registration preview">
      <div class="visual-board">
        <h2>Why join Roomly?</h2>
        <div class="three-column">
          <div class="map-card">
            <div>
              <strong>Smart Matching</strong>
              <p class="muted" style="font-size: 0.8rem; margin-top: 4px;">Find roommates based on your lifestyle.</p>
            </div>
            <span class="chip info">Smart Filter</span>
          </div>
          <div class="map-card">
            <div>
              <strong>Verified Hostels</strong>
              <p class="muted" style="font-size: 0.8rem; margin-top: 4px;">Every listing is checked by our team.</p>
            </div>
            <span class="chip success">Secure</span>
          </div>
          <div class="map-card">
            <div>
              <strong>Virtual Tours</strong>
              <p class="muted" style="font-size: 0.8rem; margin-top: 4px;">Explore rooms in 360° before visiting.</p>
            </div>
            <span class="chip warning">Explore</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>