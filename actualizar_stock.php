<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
//Inicio la conexión
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or
    die("Problemas con la conexión");


if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // Extraer el ID del producto de la acción
    $operacion = explode('_', $accion);
    $tipo_accion = $operacion[0]; // "sumar" o "restar"
    $id = intval($operacion[1]);

    // Obtener la cantidad actual del producto
    $query = "SELECT cantidad FROM stock WHERE id = $id";
    $resultado = mysqli_query($conexion, $query) or die("Error al obtener la cantidad actual: " . mysqli_error($conexion));
    $producto = mysqli_fetch_assoc($resultado);
    $cantidad_actual = intval($producto['cantidad']);

    // Determinar la nueva cantidad
    if ($tipo_accion == 'sumar') {
        $nueva_cantidad = $cantidad_actual + 1;

        // Actualizar la cantidad en la base de datos
        $update_query = "UPDATE stock SET cantidad = $nueva_cantidad WHERE id = $id";
        mysqli_query($conexion, $update_query) or die("Error al actualizar la cantidad: " . mysqli_error($conexion));
    } elseif ($tipo_accion == 'restar' && $cantidad_actual > 0) {
        $nueva_cantidad = $cantidad_actual - 1;

        if ($nueva_cantidad > 0) {
            // Si la cantidad es mayor que 0, solo actualiza la cantidad
            $update_query = "UPDATE stock SET cantidad = $nueva_cantidad WHERE id = $id";
            mysqli_query($conexion, $update_query) or die("Error al actualizar la cantidad: " . mysqli_error($conexion));
        } else {
            // Si la cantidad llega a 0, elimina el registro
            $delete_query = "DELETE FROM stock WHERE id = $id";
            mysqli_query($conexion, $delete_query) or die("Error al eliminar el registro: " . mysqli_error($conexion));
            echo "Registro eliminado correctamente.";
        }
    } else {
        die("No se puede restar más de la cantidad disponible.");
    }

    echo "Operación realizada correctamente.";
}

// Redirigir de vuelta a la página de stock
header("Location: stock.php");
exit;
