<?php
// Configuración de la conexión PDO
try {
    $dbh = new PDO('mysql:host=mi_mariadb;dbname=bd_prueba', 'root', 'root');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✔ Conexión exitosa a la base de datos con PDO</p>";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
