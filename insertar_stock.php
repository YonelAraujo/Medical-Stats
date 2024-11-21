<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Crear conexión
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or
    die("Problemas con la conexión: " . mysqli_connect_error());

// Validar que los campos requeridos existan y no estén vacíos
if (
    isset($_POST['nombre']) && isset($_POST['vencimiento']) && isset($_POST['cantidad']) &&
    !empty($_POST['nombre']) && !empty($_POST['vencimiento']) && !empty($_POST['cantidad'])
) {

    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $vencimiento = mysqli_real_escape_string($conexion, $_POST['vencimiento']);
    $cantidad = intval($_POST['cantidad']); // Asegurar que cantidad sea un entero

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Actualizar registro existente
        $id = intval($_POST['id']); // Asegurar que el ID sea un entero
        $stmt = $conexion->prepare("UPDATE stock SET nombre = ?, vencimiento = ?, cantidad = ? WHERE id = ?");
        if ($stmt === false) {
            die("Problemas en la preparación del Update: " . $conexion->error);
        }
        $stmt->bind_param("ssii", $nombre, $vencimiento, $cantidad, $id);
        if ($stmt->execute()) {
            echo "Registro actualizado correctamente.";
        } else {
            die("Problemas en el Update: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Insertar un nuevo registro
        $stmt = $conexion->prepare("INSERT INTO stock (nombre, vencimiento, cantidad) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("Problemas en la preparación del Insert: " . $conexion->error);
        }
        $stmt->bind_param("ssi", $nombre, $vencimiento, $cantidad);
        if ($stmt->execute()) {
            echo "Registro insertado correctamente.";
        } else {
            die("Problemas en el Insert: " . $stmt->error);
        }
        $stmt->close();
    }
} else {
    die("Faltan datos requeridos en el formulario.");
}

// Cerrar la conexión
mysqli_close($conexion);

// Redirigir a la página de stock después de la operación
header("Location: stock.php");
exit;
?>
