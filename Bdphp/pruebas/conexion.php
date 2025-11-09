<?php
$mysqli = new mysqli("localhost", "zarucoo", "root", "bd_prueba");

if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Procesar botones
if (isset($_POST['crear_tabla'])) {
    if ($mysqli->query("CREATE TABLE test(id INT, nombre VARCHAR(50))")) {
        echo "<p>✅ Tabla 'test' creada</p>";
    } else {
        echo "<p>❌ Error: " . $mysqli->error . "</p>";
    }
}

if (isset($_POST['borrar_tabla'])) {
    if ($mysqli->query("DROP TABLE test")) {
        echo "<p>✅ Tabla 'test' eliminada</p>";
    } else {
        echo "<p>❌ Error: " . $mysqli->error . "</p>";
    }
}

if (isset($_POST['insertar_dato'])) {
    if ($mysqli->query("INSERT INTO test(id, nombre) VALUES (1, 'Gasparin')")) {
        echo "<p>✅ Dato insertado</p>";
    } else {
        echo "<p>❌ Error: " . $mysqli->error . "</p>";
    }
}

if (isset($_POST['ver_datos'])) {
    $resultado = $mysqli->query("SELECT * FROM test");
    if ($resultado) {
        echo "<h3>Datos en la tabla:</h3>";
        while($row = $resultado->fetch_assoc()) {
            echo "ID: " . $row['id'] . " - Nombre: " . $row['nombre'] . "<br>";
        }
    } else {
        echo "<p>❌ Error: " . $mysqli->error . "</p>";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prueba MySQL</title>
</head>
<body>
    <h1>Control de Base de Datos</h1>
    
    <form method="POST">
        <button name="crear_tabla">CREAR TABLA</button>
    </form>
    
    <form method="POST">
        <button name="borrar_tabla">BORRAR TABLA</button>
    </form>
    
    <form method="POST">
        <button name="insertar_dato">INSERTAR DATO</button>
    </form>
    
    <form method="POST">
        <button name="ver_datos">VER DATOS</button>
    </form>
</body>
</html>