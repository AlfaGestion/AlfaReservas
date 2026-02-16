<?php
$db = new mysqli('localhost', 'root', '', 'reserva-canchas', 3306);
if ($db->connect_error) { echo 'DB error: ' . $db->connect_error . PHP_EOL; exit(1);} 
$sql = "SELECT b.id, b.date, b.name, b.phone, b.total, b.parcial, b.reservation, b.payment, b.diference, b.total_payment, b.id_preference_parcial, b.id_preference_total, mp.preference_id, mp.payment_id, mp.status, mp.created_at FROM bookings b LEFT JOIN mercado_pago mp ON mp.id_booking = b.id WHERE b.date='2026-02-16' AND b.phone='1156955241' ORDER BY b.id DESC, mp.id DESC LIMIT 20";
$res = $db->query($sql);
if (!$res) { echo 'Query error: ' . $db->error . PHP_EOL; exit(1);} 
while ($row = $res->fetch_assoc()) { echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
?>
