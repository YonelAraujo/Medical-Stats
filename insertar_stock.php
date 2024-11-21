<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Crear conexión
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or
    die("Problemas con la conexión");

// Verificar el valor del checkbox de urgencia
$urgencia = isset($_REQUEST['urgencia']) ? 1 : 0;

// Si el campo ID NO está vacío, se está actualizando un registro
if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    // Actualizar registro
    mysqli_query($conexion, "UPDATE pacientes
                           SET numero_quirofano='$_REQUEST[numero_quirofano]',
                           edad='$_REQUEST[edad]',
                           dni='$_REQUEST[dni]',
                           Localidad='$_REQUEST[Localidad]',
                           nombre='$_REQUEST[nombre]', 
                           procedimiento='$_REQUEST[procedimiento]',
                           cirujano='$_REQUEST[cirujano]',
                           1_Ayudante='$_REQUEST[ayudante1]',
                           2_Ayudante='$_REQUEST[ayudante2]',
                           anestesista='$_REQUEST[anestesista]',
                           instrumentador='$_REQUEST[instrumentador]',
                           tipo_anestesia='$_REQUEST[tipo_anestesia]',
                           urgencia='$urgencia'
                           WHERE id='$_REQUEST[id]'") 
    or die("Problemas en el select" . mysqli_error($conexion));
} else {
    // Si el campo ID está vacío, se está creando un registro nuevo
    mysqli_query($conexion, "INSERT INTO pacientes(
        numero_quirofano,
        edad,
        dni,
        Localidad,
        nombre,
        procedimiento,
        cirujano,
        1_Ayudante,
        2_Ayudante,
        anestesista,
        instrumentador,
        tipo_anestesia,
        urgencia) 
VALUES(
       '$_REQUEST[numero_quirofano]',
       '$_REQUEST[edad]',
       '$_REQUEST[dni]',
       '$_REQUEST[Localidad]',
       '$_REQUEST[nombre]',
       '$_REQUEST[procedimiento]',
       '$_REQUEST[cirujano]',
       '$_REQUEST[ayudante1]',
       '$_REQUEST[ayudante2]',
       '$_REQUEST[anestesista]',
       '$_REQUEST[instrumentador]',
       '$_REQUEST[tipo_anestesia]',
       '$urgencia')") or die("Problemas en el select" . mysqli_error($conexion));
}

mysqli_close($conexion);
header("Location: pagina.php");
exit;
?>
