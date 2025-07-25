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

    .btn-primary-1 {
     background-color: #143D60;
      color: white;
      border: none;
      padding: 0.5rem 1.5rem;
      font-size: 1rem;
      font-weight: 500;
    }

    .btn-primary-1:hover {
      background-color: #0f2a40;
      color: white;
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


  input[type="number"] {
    -moz-appearance: textfield;
  }
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  input[type="number"] {
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    background-color: #fff;
  }


  .btn-lg {
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
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


<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #143D60;"> 
  <div class="container">
    <a class="navbar-brand d-flex flex-column align-items-start" href="#">
        <span class="fs-5"><?= htmlspecialchars($_SESSION['store_name']); ?></span>
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
          <a class="nav-link text-white" href="#">
            <span class="nav-icon-wrapper"><i class="bi bi-boxes nav-icon"></i></span> Stock
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">
            <span class="nav-icon-wrapper"><i class="bi bi-bar-chart-line nav-icon"></i></span> Analytics
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">
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



<div class="container my-4">
  <div class="card shadow-sm border-0 rounded ">
    <div class="card-body">
      <h4 class="mb-4">Sell Products</h4>

      <form method="POST" action="../backend/process_sale.php" onsubmit="return handlePayment(event)">

         <input type="hidden" name="amount_paid" id="amount_paid">
        <input type="hidden" name="change_given" id="change_given">
        <input type="hidden" name="mpesa_number" id="mpesa_number">
        <input type="hidden" name="transaction_id" id="transaction_id">

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
            <input type="number" class="form-control" id="quantity" min="1"  placeholder="Enter quantity">
          </div>
          <div class="col-md-2 align-self-end">
            <button type="button" class="btn btn-lg" style="background-color: #143D60; color: white;" onclick="addProduct()"> <i class="bi bi-plus-circle"></i> Add</button>
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

        <!-- Total -->
        <div class="mb-4">
          <h5>Total: <span id="grandTotal">KES 0.00</span></h5>
        </div>

        <!-- Payment Method -->
        <div class="mb-4 select col-md-4">
          <label for="payment_method" class="form-label">Payment Method</label>
          <select class="form-select" name="payment_method" required>
            <option value="">Select Method</option>
            <option value="Cash">Cash</option>
            <option value="Mpesa">Mpesa</option>
            <option value="Card">Card</option>
          </select>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" name="sales_data" id="salesData">
        <input type="hidden" name="grand_total" id="grandTotalInput">

        <div class="text-end">
          <button type="submit" class="btn btn-primary-1 px-4"><i class="bi bi-cart-plus"></i>  Make Sale</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Cash Payment Modal -->
<div class="modal fade" id="cashModal" tabindex="-1" aria-labelledby="cashModalLabel" aria-hidden="true">
  <div class="modal-dialog ">
    <form id="cashPaymentForm" onsubmit="return submitSale()" method="POST" action="../backend/process_sale.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cash Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="amountPaid" class="form-label">Amount Paid (Ksh)</label>
            <input type="number" step="0.01" class="form-control" id="amountPaid" name="amount_paid" required>
          </div>
          <div class="mb-3">
            <label for="changeGiven" class="form-label">Change Given (Ksh)</label>
            <input type="number" step="0.01" class="form-control" id="changeGiven" name="change_given" readonly>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" id="confirmCashPayment" class="btn btn-success">Complete Payment</button>
        </div>
      </div>
    </form>
  </div>
</div>




<!-- Mpesa Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1" aria-labelledby="mpesaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="mpesaPaymentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mpesa Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="mpesaNumber" class="form-label">Mpesa Number</label>
          <input type="text" class="form-control" id="mpesaNumber" name="phone" placeholder="e.g. 254712345678" required>
        </div>
        <input type="hidden" id="mpesaTotal" name="amount" value="0.00">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirm Payment</button>
      </div>
    </form>
  </div>
</div>




<!-- JavaScript Section -->
<script>
  // check if quantity is entered
  document.getElementById('quantity').addEventListener('input', function() {
    const quantity = parseInt(this.value);
    if (isNaN(quantity) || quantity < 1) {
      this.value = '';
      showToast("Please enter a valid quantity.", "danger");
    }
  });
</script>


<!-- prevent deafult in all forms -->






<script>
  const products = <?= json_encode($products) ?>;
  let saleItems = [];

  function addProduct() {
    const productSelect = document.getElementById('product');
    const quantityInput = document.getElementById('quantity');
    const productId = productSelect.value;
    const quantity = parseInt(quantityInput.value);

    if (!productId || isNaN(quantity) || quantity < 1) {
      showToast("Please select a product and enter a valid quantity.", "danger");
      return;
    }

    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const productName = selectedOption.getAttribute('data-name');
    const unitPrice = parseFloat(selectedOption.getAttribute('data-price'));
    const stock = parseInt(selectedOption.getAttribute('data-stock'));

    if (stock === 0) {
      showToast("This product is out of stock.", "danger");
      return;
    }

    if (quantity > stock) {
      showToast(`Only ${stock} item(s) available.`, "danger");
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
      showToast("Please add products before submitting.", "danger");
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




<script>
  // Prevent default form submission and show cash modal if selected
function handlePayment(event) {
  event.preventDefault();

  if (saleItems.length === 0) {
    showToast("Please add products before submitting.", "danger");
    return false;
  }

  const method = document.querySelector('[name="payment_method"]').value;
  const grandTotal = parseFloat(document.getElementById("grandTotalInput").value);
  document.getElementById("salesData").value = JSON.stringify(saleItems);

  if (method === "Cash") {
    const modal = new bootstrap.Modal(document.getElementById("cashModal"));
    modal.show();
    return false;
  }

  if (method === "Mpesa") {
    document.getElementById("mpesaTotal").value = grandTotal;
    const modal = new bootstrap.Modal(document.getElementById("mpesaModal"));
    modal.show();
    return false;
  }

  event.target.submit();
}


  // Polling function to check payment status
 function pollPaymentStatus(checkoutId, attempts = 0) {
  if (attempts > 20) return;

  fetch(`../backend/mpesa_status.php?checkout_id=${checkoutId}`)
    .then(res => res.json())
    .then(data => {
      if (!data || !data.status) {
        setTimeout(() => pollPaymentStatus(checkoutId, attempts + 1), 1000);
        return;
      }

      if (data.status === 'success') {
        showToast("Payment successful. Recording sale...", "success");

        // Step 1: Prepare sale payload
        const payload = new FormData();
        payload.append("payment_method", "Mpesa");
        payload.append("sales_data", JSON.stringify(saleItems));
        payload.append("grand_total", data.amount);
        payload.append("amount_paid", data.amount);
        payload.append("change_given", "0.00");
        payload.append("mpesa_number", data.phone);
        payload.append("transaction_id", data.receipt);
        payload.append("transaction_status", "Success");

        // Step 2: Send to process_sale.php
        fetch("../backend/process_sale.php", {
          method: "POST",
          body: payload
        })
          .then(res => res.json())
          .then(result => {
            if (result.success) {
              showToast("Sale recorded successfully ✅", "success");

              // Optional: Clear sale and reset UI
              saleItems = [];
              renderTable();
              calculateGrandTotal();
              document.getElementById("mpesaNumber").value = "";

              setTimeout(() => location.reload(), 2500);
            } else {
              showToast("Database error: " + result.message, "danger");
            }
          })
          .catch(err => {
            console.error("DB error:", err);
            showToast("Failed to record sale: " + err.message, "danger");
          });

      } else if (data.status === 'cancelled') {
        showToast("Payment was cancelled by user ❌", "danger");
      } else {
        setTimeout(() => pollPaymentStatus(checkoutId, attempts + 1), 1000);
      }
    })
    .catch(err => {
      console.error("Polling error:", err);
      showToast("Polling failed: " + err.message, "danger");
    });
}




// Handle Mpesa Payment Submit
document.getElementById("mpesaPaymentForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const phone = document.getElementById("mpesaNumber").value.trim();
  const amount = parseFloat(document.getElementById("mpesaTotal").value);

  // Log phone and amount
  console.log("Mpesa Number:", phone);
  console.log("Mpesa Amount:", amount);

  if (!/^2547\d{8}$/.test(phone)) {
    showToast("Invalid Mpesa number format. Use 2547XXXXXXXX.", "danger");
    return;
  }

  const payload = {
    mpesa_number: phone,
    grand_total: amount,
    sales_data: saleItems
  };

  // Log payload
  console.log("Payload:", payload);

  fetch("../backend/mpesa_payment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast("STK Push sent! Await user confirmation.", "success");
        bootstrap.Modal.getInstance(document.getElementById("mpesaModal")).hide();
        pollPaymentStatus(data.checkout_id); // Add this
      } else {
        showToast("Mpesa error: " + data.message, "danger");
      }
    })
    .catch(error => {
      console.error("Mpesa error:", error);
      showToast("Network error: " + error.message, "danger");
    });
});



  // Calculate change on cash input
  document.getElementById("amountPaid").addEventListener("input", function () {
    const amountPaid = parseFloat(this.value);
    const grandTotal = parseFloat(document.getElementById("grandTotal").innerText.replace("KES", "").trim());

    const change = amountPaid - grandTotal;
    document.getElementById("changeGiven").value = change >= 0 ? change.toFixed(2) : "0.00";
  });


  // Confirm cash and submit
document.getElementById("confirmCashPayment").addEventListener("click", function () {
  const amountPaid = parseFloat(document.getElementById("amountPaid").value);
  const grandTotal = parseFloat(document.getElementById("grandTotal").innerText.replace("KES", "").trim());

  if (isNaN(amountPaid) || amountPaid < grandTotal) {
    showToast("Amount paid is less than total. Please enter correct amount.", "danger");
    return;
  }

  // Prepare form data
  const formData = new FormData();
  formData.append("amount_paid", amountPaid.toFixed(2));
  formData.append("change_given", (amountPaid - grandTotal).toFixed(2));
  formData.append("payment_method", "Cash");
  formData.append("grand_total", grandTotal.toFixed(2));
  formData.append("sales_data", JSON.stringify(saleItems));

  // Hide modal
  const modal = bootstrap.Modal.getInstance(document.getElementById("cashModal"));
  modal.hide();

  // Send fetch POST
  fetch("../backend/process_sale.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      showToast(data.message, data.success ? "success" : "danger");

      if (data.success) {
        // Reset everything
        saleItems = [];
        renderTable();
        calculateGrandTotal();
        document.getElementById("amountPaid").value = '';
        document.getElementById("changeGiven").value = '';
      }
    })
    .catch(err => {
      console.error("Fetch error:", err);
      showToast("An error occurred while processing payment.", "danger");
    });
});



</script>




</body>
</html>
