<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$store_id = $_SESSION['store_id'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($_SESSION['store_name']); ?> Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
  <!-- boostrap icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>
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
          <a class="nav-link text-white" href="#">
            <span class="nav-icon-wrapper"><i class="bi bi-house nav-icon"></i></span> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">
            <span class="nav-icon-wrapper"><i class="bi bi-people nav-icon"></i></span> Members
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



<!-- Main Content -->

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Product Inventory</h2>
    <button class="btn" style="background-color: #143D60; color: white;" data-bs-toggle="modal" data-bs-target="#addProductModal"> <i class="bi bi-plus-circle"></i> Add Product</button>
  </div>

  <table id="productTable" class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>SN</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Description</th>
        <th>Buying Price</th>
        <th>Selling Price</th>
        <th>Quantity</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = "SELECT * FROM products WHERE store_id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("i", $store_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $sn = 1;
      while ($row = $result->fetch_assoc()):
      ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= htmlspecialchars($row['buy_price']) ?></td>
        <td><?= htmlspecialchars($row['sell_price']) ?></td>
        <td><?= htmlspecialchars($row['quantity']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
        <button 
        class="btn btn-sm btn-secondary text-white"
        data-name="<?= $row['product_name']; ?>"
        data-category="<?= $row['category']; ?>"
        data-description="<?= $row['description']; ?>"
        data-buy_price="<?= $row['buy_price']; ?>"
        data-sell_price="<?= $row['sell_price']; ?>"
        data-quantity="<?= $row['quantity']; ?>"
        onclick="viewProduct(this)"
        data-bs-toggle="modal" 
        data-bs-target="#viewProductModal"
        >
       <i class="bi bi-eye"></i>
        </button>


        <button class="btn btn-sm btn-info text-white"
                data-bs-toggle="modal" data-bs-target="#editProductModal"
                data-sn="<?= $row['sn'] ?>"
                data-name="<?= htmlspecialchars($row['product_name']) ?>"
                data-category="<?= htmlspecialchars($row['category']) ?>"
                data-description="<?= htmlspecialchars($row['description']) ?>"
                data-buy_price="<?= htmlspecialchars($row['buy_price']) ?>"
                data-sell_price="<?= htmlspecialchars($row['sell_price']) ?>"
                data-quantity="<?= htmlspecialchars($row['quantity']) ?>"
                onclick="editProduct(this)"> <i class="bi bi-pencil-square"></i></button>

        <button class="btn btn-sm btn-danger"
                data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                data-sn="<?= $row['sn'] ?>"
                data-name="<?= htmlspecialchars($row['product_name']) ?>"
                onclick="deleteProduct(this)"><i class="bi bi-trash"></i></button>
        </td>


      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="../backend/add_product.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Product Name</label>
          <input type="text" name="product_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Buying Price</label>
          <input type="number" name="buying_price" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Selling Price</label>
          <input type="number" name="selling_price" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Save Product</button>
      </div>
    </form>
  </div>
</div>


<!-- View Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true"> 
  <div class="modal-dialog modal-lg"> <!-- Wider modal -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form class="row g-3">
          <div class="col-md-6">
            <label for="view_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="view_name" readonly>
          </div>
          <div class="col-md-6">
            <label for="view_category" class="form-label">Category</label>
            <input type="text" class="form-control" id="view_category" readonly>
          </div>
          <div class="col-md-12">
            <label for="view_description" class="form-label">Description</label>
            <textarea class="form-control" id="view_description" rows="2" readonly></textarea>
          </div>
          <div class="col-md-4">
            <label for="view_buying" class="form-label">Buying Price</label>
            <input type="text" class="form-control" id="view_buying" readonly>
          </div>
          <div class="col-md-4">
            <label for="view_selling" class="form-label">Selling Price</label>
            <input type="text" class="form-control" id="view_selling" readonly>
          </div>
          <div class="col-md-4">
            <label for="view_quantity" class="form-label">Quantity</label>
            <input type="text" class="form-control" id="view_quantity" readonly>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>




<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editProductForm" action="../backend/edit_product.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sn" id="edit_sn">

          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" class="form-control" name="product_name" id="edit_product_name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" name="category" id="edit_category" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_description" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Buying Price</label>
            <input type="number" step="0.01" class="form-control" name="buy_price" id="edit_buy_price" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Selling Price</label>
            <input type="number" step="0.01" class="form-control" name="sell_price" id="edit_sell_price" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="edit_quantity" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Product</button>
        </div>
      </div>
    </form>
  </div>
</div>




<!-- Delete Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="../backend/delete_product.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this product?</p>
          <input type="hidden" name="sn" id="delete_sn">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>



<!-- Footer fixed bottom -->
<footer class="footer mt-auto py-3 fixed-bottom" style="background-color: #DDEB9D; color: white;">
  <div class="container">
    <span class="text-muted">&copy; 2025 Astra Softwares</span>
  </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script>
  const toasts = document.querySelectorAll('.toast');
  toasts.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();
  });
</script>


<script>
  $(document).ready(function () {
    $('#productTable').DataTable({
      dom: 'Bfrtip',
      buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'],
      language: {
        emptyTable: "No products available. Click '+ Add Product' to get started."
      },
      lengthMenu: [5, 10, 25, 50, 100],
      pageLength: 10,
      responsive: true
    });
  });
</script>

<script>
function viewProduct(button) {
  document.getElementById('view_name').value = button.dataset.name;
  document.getElementById('view_category').value = button.dataset.category;
  document.getElementById('view_description').value = button.dataset.description;
  document.getElementById('view_buying').value = button.dataset.buy_price;
  document.getElementById('view_selling').value = button.dataset.sell_price;
  document.getElementById('view_quantity').value = button.dataset.quantity;
}





function editProduct(button) {
  document.getElementById('edit_sn').value = button.dataset.sn;
  document.getElementById('edit_product_name').value = button.dataset.name;
  document.getElementById('edit_category').value = button.dataset.category;
  document.getElementById('edit_description').value = button.dataset.description;
  document.getElementById('edit_buy_price').value = button.dataset.buy_price;
  document.getElementById('edit_sell_price').value = button.dataset.sell_price;
  document.getElementById('edit_quantity').value = button.dataset.quantity;
}


  function deleteProduct(button) {
    const sn = button.dataset.sn;
    const name = button.dataset.name;
    document.getElementById('delete_sn').value = sn;

    // Insert product name in confirmation text
    const modalBody = document.querySelector('#deleteProductModal .modal-body');
    modalBody.innerHTML = `<p>Are you sure you want to delete <strong>${name}</strong>?</p>
                           <input type="hidden" name="sn" id="delete_sn" value="${sn}">`;
  }
</script>


</body>
</html>
