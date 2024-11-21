<?php
session_start();
// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
//Crear conexión
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or
    die("Problemas con la conexión");
//Obtener los últimos 5 registros de la tabla de Stock
    $registros = mysqli_query($conexion, "select * from stock  order by id DESC LIMIT 5;") or
    die("Problemas en el select:" . mysqli_error($conexion));
?>
<!-- Armar HTML -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Stats</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="pagina.php">Inicio</a></li>
            <li><a href="stock.php">Control de Stock</a></li>
            <li> <a href="pagina.php"><img src="LogoB.png" alt="Logo" class="logo"></a></li>
            <li><a href="Estadistica.php">Estadística</a></li>
            <li><a href="salir.php" ><img  src='CerrarSesion.png'  width='35' height='35' alt='CerrarSesion' ></a></li>
        </ul>
    </nav>
    <main>
        <h1>Control de Stock</h1>
        <div class="table-container">
        <table>
            <thead> <!--Cabecera de la tabla -->
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Vencimiento</th>
                    <th>Actualizar stock</th>
                </tr>
            </thead>
            <tbody>
    <?php
    //Armar cuerpo de la tabla con los últimos 5 registros obtenidos
    while ($reg = mysqli_fetch_array($registros)) {
        $id = $reg['id'];
        echo "<tr>";
        echo "<td>" . $reg['id'] . "</td>";
        echo "<td>" . $reg['nombre'] . "</td>";
        echo "<td>" . $reg['cantidad'] . "</td>";
        echo "<td>" . $reg['vencimiento'] . "</td>";
        //Crear botones para sumar y restar
        echo "<td>
                <form action='actualizar_stock.php' method='POST'>
                    <button class='btn-secondary_stock' name='accion' value='restar_$id'>-</button>
                    <button class='btn-secondary_stock' name='accion' value='sumar_$id'>+</button>
                </form>
              </td>";
        echo "</tr>";
    }
    ?>
</tbody>
        </table>
        </div>
        <div class="button-container">
            <button class="btn-primary" onclick="window.location.href='formulario_stock.php'"><img src='add.png' alt='Editar' width='50' height='50'></button>
        </div>
    </main>
    <br>
    <br>
    <footer>
        <p>®️ 2024 Medical Stats. Todos los derechos reservados.</p>
    </footer>
</body>

</html>
