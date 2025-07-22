<?php
session_start();
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
  <style>
    .product-row td { vertical-align: middle; }
    .stock-label { font-size: 0.9rem; color: #6c757d; }
  </style>
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="card-title mb-4">Sell Products</h4>
      <form method="POST" action="../backend/process_sale.php" onsubmit="return submitSale()">

        <div class="row g-3 align-items-end">
          <div class="col-md-5">
            <label for="product" class="form-label">Select Product</label>
            <select class="form-select" id="product">
              <option value="">-- Choose a Product --</option>
              <?php foreach ($products as $product): ?>
                <option 
                  value="<?= $product['sn'] ?>" 
                  data-price="<?= $product['sell_price'] ?>" 
                  data-stock="<?= $product['quantity'] ?>">
                  <?= htmlspecialchars($product['product_name']) ?> (Stock: <?= $product['quantity'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-2">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" min="1" value="1">
          </div>

          <div class="col-md-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-select" id="payment_method" name="payment_method" required>
              <option value="">Select</option>
              <option value="Cash">Cash</option>
              <option value="Mpesa">M-Pesa</option>
              <option value="Card">Card</option>
            </select>
          </div>

          <div class="col-md-2">
            <button type="button" class="btn btn-primary w-100" onclick="addProduct()">Add</button>
          </div>
        </div>

        <hr>

        <div class="table-responsive mt-3">
          <table class="table table-bordered" id="salesTable">
            <thead class="table-secondary">
              <tr>
                <th>Product</th>
                <th>Unit Price (KES)</th>
                <th>Quantity</th>
                <th>Total (KES)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="saleItems"></tbody>
          </table>
        </div>

        <div class="mt-3">
          <h5>Grand Total: <span id="grandTotal">KES 0.00</span></h5>
        </div>

        <input type="hidden" name="sales_data" id="salesData">
        <input type="hidden" name="grand_total" id="grandTotalInput">

        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success px-4">Submit Sale</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const products = <?= json_encode($products) ?>;
  let saleItems = [];

  function addProduct() {
    const productSelect = document.getElementById('product');
    const quantityInput = document.getElementById('quantity');
    const productId = productSelect.value;
    const quantity = parseInt(quantityInput.value);

    if (!productId || quantity < 1) {
      alert("Please select a product and enter a valid quantity.");
      return;
    }

    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const productName = selectedOption.text;
    const unitPrice = parseFloat(selectedOption.getAttribute('data-price'));
    const stock = parseInt(selectedOption.getAttribute('data-stock'));

    if (stock === 0) {
      alert("The item is no longer in stock.");
      return;
    }

    if (quantity > stock) {
      alert(`Only ${stock} item(s) available in stock.`);
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

    // Update stock locally
    selectedOption.setAttribute('data-stock', stock - quantity);

    renderTable();
    calculateGrandTotal();
  }

  function renderTable() {
    const tbody = document.getElementById('saleItems');
    tbody.innerHTML = "";

    saleItems.forEach((item, index) => {
      const row = `
        <tr class="product-row">
          <td>${item.product_name}</td>
          <td>${item.price.toFixed(2)}</td>
          <td>${item.quantity}</td>
          <td>${item.total.toFixed(2)}</td>
          <td><button class="btn btn-sm btn-danger" onclick="removeItem(${index})">Remove</button></td>
        </tr>
      `;
      tbody.innerHTML += row;
    });
  }

  function removeItem(index) {
    // restore stock
    const productId = saleItems[index].product_id;
    const productOption = Array.from(document.getElementById('product').options)
      .find(opt => opt.value == productId);
    if (productOption) {
      let currentStock = parseInt(productOption.getAttribute('data-stock')) || 0;
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
      alert("Please add products before submitting.");
      return false;
    }

    document.getElementById('salesData').value = JSON.stringify(saleItems);
    return true;
  }
</script>
</body>
</html>
