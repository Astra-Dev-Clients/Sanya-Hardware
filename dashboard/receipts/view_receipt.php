<?php
require '../../database/db.php';
$sale_id = $_GET['sale_id'] ?? 0;

$stmt = $conn->prepare("SELECT s.*, CONCAT(a.fname, ' ', a.lname) AS assistant_name FROM sales s LEFT JOIN assistants a ON s.assistant_id = a.id WHERE s.id = ?");
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

$items_stmt = $conn->prepare("SELECT si.*, p.product_name FROM sale_items si JOIN products p ON si.product_id = p.sn WHERE si.sale_id = ?");
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Receipt #<?= $sale['id'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      background: #fff;
      color: #000;
      margin: 0;
      padding: 0;
    }

    .receipt {
      width: 280px;
      margin: auto;
      padding: 10px;
    }

    .header {
      text-align: center;
      margin-bottom: 10px;
    }

    .header i {
      font-size: 22px;
      display: block;
    }

    .header h4 {
      margin: 5px 0;
      font-size: 14px;
      text-transform: uppercase;
    }

    .info p {
      margin: 2px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 2px 0;
      text-align: left;
    }

    th {
      border-bottom: 1px dashed #000;
    }

    td:last-child, th:last-child {
      text-align: right;
    }

    .totals {
      margin-top: 10px;
      border-top: 1px dashed #000;
      padding-top: 5px;
    }

    .totals p {
      display: flex;
      justify-content: space-between;
      margin: 2px 0;
    }

    .footer {
      text-align: center;
      margin-top: 10px;
      font-size: 11px;
    }

    .qr {
      text-align: center;
      margin-top: 10px;
    }

    .qr img {
      width: 100px;
      height: 100px;
    }

    .print-btn {
      display: block;
      margin: 20px auto;
      padding: 5px 10px;
      font-size: 12px;
    }

    @media print {
      .print-btn {
        display: none;
      }

      body {
        background: none;
      }
    }
  </style>
</head>
<body>
  <div class="receipt" style="border: 1px dotted black; margin-top:20px;">
    <div class="header">
      <i class="bi bi-tools"></i>
      <h4>Sanya Hardwares</h4>
      <small>Reliable Building Solutions</small>
    </div>

    <div class="info">
      <p>Receipt #: <?= $sale['id'] ?></p>
      <p>Date: <?= $sale['sale_time'] ?></p>
      <p>Assistant: <?= $sale['assistant_name'] ?? 'N/A' ?></p>
      <p>Payment: <?= $sale['payment_method'] ?></p>
      <p>Txn ID: <?= $sale['transaction_id'] ?: '-' ?></p>
      <p>Mpesa #: <?= $sale['mpesa_number'] ?: '-' ?></p>
    </div>

    <table>
      <thead>
        <tr><th>Item</th><th>Qty</th><th>Total</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td><?= $item['product_name'] ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= number_format($item['total_price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="totals">
      <p><span>Total Paid:</span> <span>KES <?= number_format($sale['amount_paid'], 2) ?></span></p>
      <p><span>Change:</span> <span>KES <?= number_format($sale['change_given'], 2) ?></span></p>
    </div>

    <div class="qr">
      <!-- You can dynamically generate a QR here (e.g., using Google Charts API or local QR lib) -->
      <img src="https://api.qrserver.com/v1/create-qr-code/?data=Receipt+<?= $sale['id'] ?>&amp;size=100x100" alt="QR Code">
    </div>

    <div class="footer">
      <p>Thank you for shopping at Sanya Hardware!</p>
      <p>Returns accepted within 7 days with receipt.</p>
    </div>
  </div>

  <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
</body>
</html>
