<?php
$mysqli = new mysqli("localhost", "zarucoo", "root", "mysql");

if ($mysqli->connect_errno) {
    echo "Fallo al conectar a MariaDB: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

echo $mysqli->host_info . "\n";

$mysqli = new mysqli("127.0.0.1", "zarucoo", "root", "mysql", 3306);

if ($mysqli->connect_errno) {
    echo "Fallo al conectar a MariaDB: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

echo $mysqli->host_info . "\n";
?>
