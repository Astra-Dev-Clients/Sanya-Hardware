<?php  
session_start();
if (!isset($_SESSION['store_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../database/db.php';
$store_id = $_SESSION['store_id'];

$products_result = $conn->prepare("SELECT sn, product_name, sell_price, quantity FROM products WHERE store_id = ?");
$products_result->bind_param("s", $store_id);
$products_result->execute();
$products = $products_result->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f5f6fa;
    }

    .card {
      border-radius: 12px;
    }

    .card-body {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      padding: 2rem;
    }

    .form-label {
      font-weight: 500;
    }

    #salesTable th, #salesTable td {
      vertical-align: middle;
    }

    .table thead th {
      background-color: #f8f9fa;
    }

    #productSearch {
      font-size: 0.95rem;
    }

    .btn-primary {
      background-color: #007BFF;
    }

    .form-select, .form-control {
      border-radius: 0.375rem;
    }

    .btn-success {
      font-weight: 500;
      padding-left: 2rem;
      padding-right: 2rem;
    }
  </style>
</head>
<body class="bg-light">

<!-- Toast Container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
  <div id="liveToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body text-light" id="toastMessage">
        <!-- message will be inserted here -->
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>


<!-- Navbar (unstyled as per request) -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #143D60;">
  <div class="container">
    <a class="navbar-brand" href="#"><?= htmlspecialchars($_SESSION['store_name']); ?></a>
  </div>
</nav>

<div class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-4">Sell Products</h4>

      <form method="POST" action="../backend/process_sale.php" onsubmit="return submitSale()">
        <!-- Product Search & Selection -->
        <div class="row g-3 align-items-end mb-4">
          <div class="col-md-5">
            <label for="productSearch" class="form-label">Search Product</label>
            <input type="text" class="form-control" id="productSearch" list="productList" placeholder="Type to search..." autocomplete="off">
            <datalist id="productList">
              <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['product_name']) ?>"></option>
              <?php endforeach; ?>
            </datalist>

          </div>

          <div class="col-md-4">
            <label for="product" class="form-label">Select Product</label>
            <select class="form-select" id="product">
              <option value="">-- Choose a Product --</option>
              <?php foreach ($products as $product): ?>
                <option 
                  value="<?= $product['sn'] ?>" 
                  data-name="<?= htmlspecialchars($product['product_name']) ?>"
                  data-price="<?= $product['sell_price'] ?>" 
                  data-stock="<?= $product['quantity'] ?>">
                  <?= htmlspecialchars($product['product_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label for="stock" class="form-label">Available Stock</label>
            <input type="text" class="form-control" id="stock" readonly>
          </div>
        </div>

        <!-- Quantity Input -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" min="1" >
          </div>
          <div class="col-md-2 align-self-end">
            <button type="button" class="btn btn-primary w-100" onclick="addProduct()">Add</button>
          </div>
        </div>

        <hr>

        <!-- Sale Table -->
        <div class="table-responsive mb-3">
          <table class="table table-bordered" id="salesTable">
            <thead>
              <tr>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="saleItems"></tbody>
          </table>
        </div>

        <!-- Grand Total -->
        <div class="mb-4">
          <h5>Grand Total: <span id="grandTotal">KES 0.00</span></h5>
        </div>

        <!-- Payment Method -->
        <div class="mb-4">
          <label for="payment_method" class="form-label">Payment Method</label>
          <select class="form-select" name="payment_method" required>
            <option value="">Select Method</option>
            <option value="Cash">Cash</option>
            <option value="Mpesa">Mpesa</option>
            <option value="Card">Card</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" name="sales_data" id="salesData">
        <input type="hidden" name="grand_total" id="grandTotalInput">

        <div class="text-end">
          <button type="submit" class="btn btn-success px-4">Submit Sale</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript Section -->
<script>
  const products = <?= json_encode($products) ?>;
  let saleItems = [];

  function addProduct() {
    const productSelect = document.getElementById('product');
    const quantityInput = document.getElementById('quantity');
    const productId = productSelect.value;
    const quantity = parseInt(quantityInput.value);

    if (!productId || isNaN(quantity) || quantity < 1) {
      showToast("Please select a product and enter a valid quantity.", "warning");
      return;
    }

    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const productName = selectedOption.getAttribute('data-name');
    const unitPrice = parseFloat(selectedOption.getAttribute('data-price'));
    const stock = parseInt(selectedOption.getAttribute('data-stock'));

    if (stock === 0) {
      showToast("This product is out of stock.", "warning");
      return;
    }

    if (quantity > stock) {
      showToast(`Only ${stock} item(s) available.`, "warning");
      return;
    }

    const total = (quantity * unitPrice).toFixed(2);

    saleItems.push({
      product_id: parseInt(productId),
      product_name: productName,
      quantity,
      price: unitPrice,
      total: parseFloat(total)
    });

    selectedOption.setAttribute('data-stock', stock - quantity);
    document.getElementById('stock').value = stock - quantity;
    renderTable();
    calculateGrandTotal();
    quantityInput.value = '';
  }

  function renderTable() {
    const tbody = document.getElementById('saleItems');
    tbody.innerHTML = "";

    saleItems.forEach((item, index) => {
      tbody.innerHTML += `
        <tr>
          <td>${item.product_name}</td>
          <td>${item.price.toFixed(2)}</td>
          <td>${item.quantity}</td>
          <td>${item.total.toFixed(2)}</td>
          <td><button class="btn btn-sm btn-danger" onclick="removeItem(${index})">Remove</button></td>
        </tr>`;
    });
  }

  function removeItem(index) {
    const productId = saleItems[index].product_id;
    const productOption = Array.from(document.getElementById('product').options)
      .find(opt => opt.value == productId);
    if (productOption) {
      const currentStock = parseInt(productOption.getAttribute('data-stock')) || 0;
      productOption.setAttribute('data-stock', currentStock + saleItems[index].quantity);
    }

    saleItems.splice(index, 1);
    renderTable();
    calculateGrandTotal();
  }

  function calculateGrandTotal() {
    let grandTotal = saleItems.reduce((sum, item) => sum + item.total, 0);
    document.getElementById('grandTotal').innerText = `KES ${grandTotal.toFixed(2)}`;
    document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
  }

  function submitSale() {
    if (saleItems.length === 0) {
      showToast("Please add products before submitting.,",  "warning");
      return false;
    }
    document.getElementById('salesData').value = JSON.stringify(saleItems);
    return true;
  }

  // On select change -> fill search + stock
  document.getElementById('product').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const stock = selected.getAttribute('data-stock');
    const name = selected.getAttribute('data-name');
    document.getElementById('stock').value = stock || '';
    document.getElementById('productSearch').value = name || '';
  });

    // When user selects from datalist, find corresponding option manually
  document.getElementById('productSearch').addEventListener('change', function () {
    const selectedName = this.value.trim().toLowerCase();
    const select = document.getElementById('product');
    let matched = false;

    for (let option of select.options) {
      const name = option.getAttribute('data-name')?.toLowerCase();
      if (name === selectedName) {
        select.value = option.value;
        select.dispatchEvent(new Event('change'));
        matched = true;
        break;
      }
    }

    if (!matched) {
      select.value = '';
      document.getElementById('stock').value = '';
    }
  });


  function showToast(message, type = 'danger') {
    const toastEl = document.getElementById('liveToast');
    const toastMsg = document.getElementById('toastMessage');

    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastMsg.innerText = message;

    // Create and show the Bootstrap toast
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  }
  
</script>

</body>
</html>
