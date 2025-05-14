<?php
require 'db.php';
$result = $conn->query("SELECT * FROM banks");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>
