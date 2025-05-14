<?php
require 'db.php';

// Update each stock with a random small change
$conn->query("UPDATE banks SET price = ROUND(price + (RAND() - 0.5) * 2, 2)");

$conn->query("INSERT INTO stock_history (bank_id, price)
    SELECT id, price FROM banks");
?>
