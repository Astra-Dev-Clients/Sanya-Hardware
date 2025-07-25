<?php
require '../../database/db.php';
header('Content-Type: application/json');

$query = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        $row['id'],
        htmlspecialchars($row['store_name']),
        htmlspecialchars($row['store_id']),
        htmlspecialchars($row['till_number']),
        htmlspecialchars($row['phone']),
        htmlspecialchars($row['email']),
        htmlspecialchars($row['created_at']),
        "<button class='btn btn-sm btn-info text-white' onclick='editUser(" . json_encode($row) . ")'><i class='bi bi-pencil-square'></i></button>
         <button class='btn btn-sm btn-danger' onclick='deleteUser({$row['id']})'><i class='bi bi-trash'></i></button>"
    ];
}

echo json_encode(['data' => $data]);
