<?php
require 'db.php';
$bankId = $_GET['bank_id'];
$sql = "SELECT price, recorded_at FROM stock_history
        WHERE bank_id = ? ORDER BY recorded_at DESC LIMIT 7";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bankId);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode(array_reverse($data));
?>
    