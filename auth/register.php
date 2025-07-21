<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Store Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Register Your Store</h3>
      </div>
      <div class="card-body">
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form action="../backend/process_register.php" method="POST">
          <div class="mb-3">
            <label for="store_id" class="form-label">Store ID</label>
            <input type="text" class="form-control" name="store_id" required>
          </div>
          <div class="mb-3">
            <label for="store_name" class="form-label">Store Name</label>
            <input type="text" class="form-control" name="store_name" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary">Register</button>
          <a href="login.php" class="btn btn-link">Already have an account? Login</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
