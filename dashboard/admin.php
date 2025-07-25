<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['astra_id'])) {
    header("Location: ../auth/astra/index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Astra Prime Dashboard</title>

  <!-- Bootstrap & DataTables -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>

<body>
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold">User Management</h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-plus-circle"></i> Add User
      </button>
    </div>

    <table id="userTable" class="table table-striped table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Store Name</th>
          <th>Store ID</th>
          <th>Till</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['store_name']) ?></td>
          <td><?= htmlspecialchars($row['store_id']) ?></td>
          <td><?= htmlspecialchars($row['till_number'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <button class="btn btn-sm btn-info text-white"
              data-bs-toggle="modal"
              data-bs-target="#editUserModal"
              data-id="<?= $row['id'] ?>"
              data-store_name="<?= htmlspecialchars($row['store_name']) ?>"
              data-store_id="<?= htmlspecialchars($row['store_id']) ?>"
              data-till="<?= htmlspecialchars($row['till_number']) ?>"
              data-phone="<?= htmlspecialchars($row['phone']) ?>"
              data-email="<?= htmlspecialchars($row['email']) ?>"
              onclick="editUser(this)">
              <i class="bi bi-pencil-square"></i>
            </button>

            <button class="btn btn-sm btn-danger"
              data-bs-toggle="modal"
              data-bs-target="#deleteUserModal"
              data-id="<?= $row['id'] ?>"
              data-name="<?= htmlspecialchars($row['store_name']) ?>"
              onclick="deleteUser(this)">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
      <form action="../backend/users/add_user.php" method="POST" class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title">Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Store Name</label>
            <input type="text" name="store_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Store ID</label>
            <input type="text" name="store_id" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Till Number</label>
            <input type="text" name="till_number" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
      <form action="../backend/users/edit_user.php" method="POST" class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Store Name</label>
            <input type="text" name="store_name" id="edit_store_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Store ID</label>
            <input type="text" name="store_id" id="edit_store_id" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Till Number</label>
            <input type="text" name="till_number" id="edit_till" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" id="edit_phone" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="edit_email" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          <button class="btn btn-primary" type="submit">Update</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
      <form action="../backend/users/delete_user.php" method="POST" class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title">Delete User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="delete_user_name"></strong>?</p>
          <input type="hidden" name="id" id="delete_user_id">
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          <button class="btn btn-danger" type="submit">Delete</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      $('#userTable').DataTable({
        language: {
          emptyTable: "No users found. Add one to get started!",
          search: "Search:"
        },
        responsive: true,
        pageLength: 10
      });
    });

    function editUser(button) {
      $('#edit_id').val($(button).data('id'));
      $('#edit_store_name').val($(button).data('store_name'));
      $('#edit_store_id').val($(button).data('store_id'));
      $('#edit_till').val($(button).data('till'));
      $('#edit_phone').val($(button).data('phone'));
      $('#edit_email').val($(button).data('email'));
    }

    function deleteUser(button) {
      $('#delete_user_id').val($(button).data('id'));
      $('#delete_user_name').text($(button).data('name'));
    }
  </script>
</body>
</html>
