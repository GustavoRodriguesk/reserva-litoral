<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=reserva_litoral', 'postgres', 'postgres');
    echo "Sucesso com postgres/postgres\n";
    $pdo->exec("ALTER TABLE booking.rooms DROP CONSTRAINT IF EXISTS rooms_status_check; ALTER TABLE booking.rooms ADD CONSTRAINT rooms_status_check CHECK (status::text = ANY (ARRAY['available'::character varying, 'reserved'::character varying, 'occupied'::character varying, 'cleaning'::character varying, 'inspected'::character varying, 'maintenance'::character varying, 'blocked'::character varying]::text[]));");
    echo "Constraint alterada com sucesso!\n";
} catch (Exception $e) {
    echo "Falha postgres/postgres: " . $e->getMessage() . "\n";
}

try {
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=reserva_litoral', 'postgres', 'admin');
    echo "Sucesso com postgres/admin\n";
    $pdo->exec("ALTER TABLE booking.rooms DROP CONSTRAINT IF EXISTS rooms_status_check; ALTER TABLE booking.rooms ADD CONSTRAINT rooms_status_check CHECK (status::text = ANY (ARRAY['available'::character varying, 'reserved'::character varying, 'occupied'::character varying, 'cleaning'::character varying, 'inspected'::character varying, 'maintenance'::character varying, 'blocked'::character varying]::text[]));");
    echo "Constraint alterada com sucesso!\n";
} catch (Exception $e) {
    echo "Falha postgres/admin: " . $e->getMessage() . "\n";
}
