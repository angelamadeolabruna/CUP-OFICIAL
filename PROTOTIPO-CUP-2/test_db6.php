<?php
$dsn = 'pgsql:host=aws-0-us-east-1.pooler.supabase.com;port=5432;dbname=postgres;user=postgres.lushzxszershruaayfrd;password=SistemaCUP2026Admin';
try {
    $pdo = new PDO($dsn);
    echo "Conexion exitosa en puerto 5432!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
