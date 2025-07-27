<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT store_id, store_name, till_number, phone, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- favicons -->
    <link rel="icon" type="image/png" href="../assets/img/favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="../assets/img/favicons/favicon.svg" />
    <link rel="shortcut icon" href="../assets/img/favicons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/favicons/apple-touch-icon.png" />
    <link rel="manifest" href="../assets/img/favicons/site.webmanifest" />


   <style>
     .nav-icon { font-size: 1.8rem; }
    .navbar-brand small { font-size: 0.75rem; font-weight: 500; margin-top: -6px; }
    .nav-link { display: flex; flex-direction: column; align-items: center; padding: 0.5rem 0.75rem; }
    .nav-link span { font-size: 0.7rem; }

  .nav-icon-wrapper {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    margin-right: 8px;
    transition: background-color 0.3s ease;
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
  }

  .nav-link:hover .nav-icon-wrapper {
    background-color: #ffffff33;
  }

  .nav-icon {
    font-size: 18px;
    color: white;
  }


  .navbar-nav .nav-item {
    margin-left: 10px;
  }


</style>
</head>
<body class="bg-light ">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #143D60;"> 
  <div class="container">
  <a class="navbar-brand d-flex align-items-start" href="#">
    <img src="../assets/img/logos/sanya-bg.png" alt="Logo" style="height: 50px; width: auto;">
  </a>


    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarIcons">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarIcons">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-white" href="index.php">
            <span class="nav-icon-wrapper"><i class="bi bi-house nav-icon nav-active"></i></span> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="sell.php">
            <span class="nav-icon-wrapper"><i class="bi bi-cart nav-icon"></i></span> Make Sales
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="transactions.php">
            <span class="nav-icon-wrapper"><i class="bi bi-receipt-cutoff nav-icon"></i></span> Transactions
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="analytics.php">
            <span class="nav-icon-wrapper"><i class="bi bi-boxes nav-icon"></i></span> Stock
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="stock_kpis.php">
            <span class="nav-icon-wrapper"><i class="bi bi-bar-chart-line nav-icon"></i></span> Analytics
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="settings.php">
            <span class="nav-icon-wrapper"><i class="bi bi-gear nav-icon"></i></span> Settings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="../auth/logout.php">
            <span class="nav-icon-wrapper"><i class="bi bi-box-arrow-right nav-icon"></i></span> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4>Manage Your Profile</h4>
      </div>
      <form action="../backend/update_profile.php" method="POST" class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $user_id ?>">
        
        <div class="mb-3">
          <label for="store_id" class="form-label">Store ID (readonly)</label>
          <input type="text" id="store_id" name="store_id" class="form-control" value="<?= htmlspecialchars($user['store_id']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="store_name" class="form-label">Store Name</label>
          <input type="text" id="store_name" name="store_name" class="form-control" value="<?= htmlspecialchars($user['store_name']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="till_number" class="form-label">Till Number</label>
          <input type="text" id="till_number" name="till_number" class="form-control" value="<?= htmlspecialchars($user['till_number']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Phone</label>
          <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email (optional)</label>
          <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">New Password (leave blank to keep current)</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</body>
</html>
