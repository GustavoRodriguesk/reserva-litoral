<?php
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=reserva_litoral', 'reserva_app', 'admin');
$stmt = $pdo->query("SELECT rolname FROM pg_roles;");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query("SELECT tableowner FROM pg_tables WHERE tablename = 'rooms';");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
