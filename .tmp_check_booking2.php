<?php
$db = new mysqli('localhost', 'root', '', 'reserva-canchas', 3306);
if ($db->connect_error) { echo 'DB error: ' . $db->connect_error . PHP_EOL; exit(1);} 
$sql = "SELECT id, date, name, phone, total, parcial, reservation, payment, diference, total_payment, payment_method FROM bookings WHERE date='2026-02-16' ORDER BY id DESC LIMIT 20";
$res = $db->query($sql);
if (!$res) { echo 'Query error: ' . $db->error . PHP_EOL; exit(1);} 
while ($row = $res->fetch_assoc()) { echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
?>
