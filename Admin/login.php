<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roomly | Admin Login</title>
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="../landingpage.html">
      <span class="brand-mark">R</span>
      <span>Roomly <small>Admin Console</small></span>
    </a>
  </header>

  <main class="auth-page">
    <section class="auth-panel">
      <form class="auth-card" method="POST" action="login_process.php">
        <div style="text-align: center;">
          <h1>Admin Sign In</h1>
          <p>Restricted access — system administrators only.</p>
        </div>

        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert"><?php echo htmlspecialchars($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></div>
        <?php endif; ?>

        <div class="form-grid" style="margin-top: 24px;">
          <div class="field-full">
            <label for="email">Admin email</label>
            <input id="email" name="email" type="email" required>
          </div>
          <div class="field-full">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
          </div>
        </div>

        <div class="button-row" style="margin-top: 22px;">
          <button class="button" type="submit">Login</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>