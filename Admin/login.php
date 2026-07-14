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
  <style>
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
      display: none;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <a class="brand" href="../landingpage.html">
      <span class="brand-mark">R</span>
      <span>Roomly<span style="color: var(--neon-cyan); font-weight: 900;">.</span></span>
    </a>
  </header>

  <main class="auth-page">
    <section class="auth-panel">
      <form class="auth-card" method="POST" action="login_process.php">
        <div style="text-align: center;">
          <h1>Welcome back, Admin</h1>
          <p>Sign in to manage listings, users, and reports.</p>
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
            <div style="position: relative;">
              <input id="password" name="password" type="password" required style="width: 100%; padding-right: 40px;">
              <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: var(--text-secondary);">
                <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div class="button-row" style="margin-top: 22px;">
          <button class="button" type="submit">Login</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
      const pwd = document.getElementById('password');
      const eye = document.getElementById('eyeIcon');
      if (pwd.type === 'password') {
        pwd.type = 'text';
        eye.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
      } else {
        pwd.type = 'password';
        eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
      }
    });
  </script>
</body>
</html>