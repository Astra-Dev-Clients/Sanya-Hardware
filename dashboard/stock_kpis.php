<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: ../auth/index.php");
    exit();
}

$store_id = $_SESSION['store_id'];

// Fetch unique product categories
$categoryStmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE store_id = ?");
$categoryStmt->bind_param("i", $store_id);
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
$categoryStmt->close();

$selectedCategory = $_GET['category'] ?? '';
$dateStart = $_GET['start_date'] ?? '';
$dateEnd = $_GET['end_date'] ?? '';

$query = "SELECT * FROM products WHERE store_id = ?";
$params = [$store_id];
$types = "i";

if (!empty($selectedCategory)) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
    $types .= "s";
}

if (!empty($dateStart) && !empty($dateEnd)) {
    $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $dateStart;
    $params[] = $dateEnd;
    $types .= "ss";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$totalStock = $totalSales = $totalQty = $productCount = $buySum = $sellSum = 0;
$monthlyData = [];

while ($row = $result->fetch_assoc()) {
    $totalStock += $row['buy_price'] * $row['quantity'];
    $totalSales += $row['sell_price'] * $row['quantity'];
    $totalQty += $row['quantity'];
    $buySum += $row['buy_price'];
    $sellSum += $row['sell_price'];
    $productCount++;

    $month = date('Y-m', strtotime($row['created_at']));
    $monthlyData[$month] = ($monthlyData[$month] ?? 0) + ($row['buy_price'] * $row['quantity']);
}

$avgBuy = $productCount ? $buySum / $productCount : 0;
$avgSell = $productCount ? $sellSum / $productCount : 0;

ksort($monthlyData);
$months = array_keys($monthlyData);
$stockValues = array_values($monthlyData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock KPIs | <?= htmlspecialchars($_SESSION['store_name']); ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="icon" type="image/png" href="../assets/img/favicons/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="../assets/img/favicons/favicon.svg" />
  <link rel="shortcut icon" href="../assets/img/favicons/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/favicons/apple-touch-icon.png" />
  <link rel="manifest" href="../assets/img/favicons/site.webmanifest" />

  <style>
    body { background-color: #f9f9f9; }
    .kpi-card { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-left: 5px solid #143D60; background-color: white; transition: 0.3s ease-in-out; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); }
    .kpi-title { font-size: 1rem; font-weight: 500; color: #555; }
    .kpi-value { font-size: 1.6rem; font-weight: bold; color: #143D60; }
    .chart-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
  </style>

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
<body>

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
            <span class="nav-icon-wrapper"><i class="bi bi-house nav-icon"></i></span> Home
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
  <h2 class="text-center fw-bold mb-4"><?= htmlspecialchars($_SESSION['store_name']); ?> – Stock KPIs</h2>

  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <label>Category</label>
      <select name="category" class="form-select">
        <option value="">All</option>
        <?php foreach ($categories as $cat): ?>
          <option <?= $selectedCategory == $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label>Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= $dateStart ?>">
    </div>
    <div class="col-md-3">
      <label>End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= $dateEnd ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button class="btn btn-primary w-100" type="submit">Filter</button>
    </div>
  </form>

  <div class="row g-4">
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Total Stock Value</div><div class="kpi-value">KES <?= number_format($totalStock, 2) ?></div></div></div>
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Total Sales Value</div><div class="kpi-value">KES <?= number_format($totalSales, 2) ?></div></div></div>
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Quantity In Stock</div><div class="kpi-value"><?= number_format($totalQty) ?> Items</div></div></div>
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Total Products</div><div class="kpi-value"><?= $productCount ?></div></div></div>
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Avg. Buying Price</div><div class="kpi-value">KES <?= number_format($avgBuy, 2) ?></div></div></div>
    <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-title">Avg. Selling Price</div><div class="kpi-value">KES <?= number_format($avgSell, 2) ?></div></div></div>
  </div>

  <div class="chart-container mt-5">
    <h5>📈 Stock Value Trend</h5>
    <canvas id="trendChart"></canvas>
  </div>
</div>

<script>
  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
      labels: <?= json_encode($months) ?>,
      datasets: [{
        label: 'Stock Value (KES)',
        data: <?= json_encode($stockValues) ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        tension: 0.3,
        fill: true
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          ticks: {
            callback: value => 'KES ' + value.toLocaleString()
          }
        }
      }
    }
  });
</script>

</body>
</html>