<?php
// Inicio la conexión
session_start();
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or die("Problemas con la conexión");

// Si ingreso usuario y contraseña
if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if (empty($user) || empty($pass)) {
        //Si el usuario y contraseña estan vacíos cargo el error
        $_SESSION['error'] = "Usuario o contraseña no pueden estar vacíos.";
        //Redirijo al login
        header("Location: login.php");
        exit();
    } else { //Si no estan vacíos
        //Obtengo información del usuario
        $registros = mysqli_query($conexion, "select * from usuarios where usuario = '$user'") or die("Error de conexion" . mysqli_error($conexion));
        if ($reg = mysqli_fetch_array($registros)) {
            //Verifico que la contraseña sea correcta
            if ($pass == $reg['contrasena']) {
                $_SESSION['usuario'] = $user;
            } else { //Si la contraseña es incorrecta
                $_SESSION['error'] = "Usuario o contraseña incorrecto";
                header("Location: login.php");
                exit();
            }
        } else { //Si no encontró el usuario en la base de datos
            $_SESSION['error'] = "Usuario no encontrado.";
            header("Location: login.php");
            exit();
        }
    }
}

//Con el usuario y contraseña verificados
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '2000-01-01';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');

// Realizar la consulta para obtener los procedimientos y su cantidad
$sql = "SELECT procedimiento, COUNT(*) as cantidad FROM pacientes WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' GROUP BY procedimiento";
$result = mysqli_query($conexion, $sql) or die("Problemas en el select:" . mysqli_error($conexion));

//Si obtengo resultados los cargo en el array
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 resultados";
}

$search_results = [];
// Si el formulario de búsqueda es enviado
if (isset($_POST['submit'])) {
    $search = $_POST['search'];

    // Consulta SQL para buscar por DNI, nombre o procedimiento y mostrar todos los datos
    $sql_search = "SELECT * FROM pacientes WHERE dni LIKE '%$search%' OR nombre LIKE '%$search%' OR procedimiento LIKE '%$search%'";
    $result_search = mysqli_query($conexion, $sql_search) or die("Problemas en el select:" . mysqli_error($conexion));

    $tabla_resultados = '';
    if ($result_search->num_rows > 0) {
        //Si obtuvo resultados, armo la tabla HTML con los datos en $tabla_resultados
        $tabla_resultados = "<div class='table-container'><table border='1'>";
        //Cabecera de la tabla
        $tabla_resultados .= "<thead><tr> 
                <th>ID</th>
                <th>Fecha</th>
                <th>Numero quirofano</th>
                <th>Edad</th>
                <th>DNI</th>
                <th>Localidad</th>
                <th>Apellido y Nombre</th>
                <th>Cirugia</th>
                <th>Cirujano</th>
                <th>1° Ayudante</th>
                <th>2° Ayudante</th>
                <th>Instrumentador</th>
                <th>Anestesista</th>
                <th>Tipo anestesia</th>
              </tr></thead>";
        while ($row = $result_search->fetch_assoc()) {
            $search_results[] = $row; // Guardar los resultados de la búsqueda
            //Cuerpo de la tabla
            $tabla_resultados .= "
                <tr>
                    <td>{$row['id']}</td>
                    <td>{$row['fecha']}</td>
                    <td>{$row['numero_quirofano']}</td>
                    <td>{$row['edad']}</td>
                    <td>{$row['dni']}</td>
                    <td>{$row['Localidad']}</td>
                    <td>{$row['nombre']}</td>
                    <td>{$row['procedimiento']}</td>
                    <td>{$row['cirujano']}</td>
                    <td>{$row['1_Ayudante']}</td>
                    <td>{$row['2_Ayudante']}</td>
                    <td>{$row['instrumentador']}</td>
                    <td>{$row['anestesista']}</td>
                    <td>{$row['tipo_anestesia']}</td>
                </tr>";
        }
        $tabla_resultados .= "</table></div>";
    } else {
        //Si no encuentra resultados
        $tabla_resultados = "No se encontraron resultados";
    }
}

mysqli_close($conexion);
?>
<!-- Armar el HTML -->
<!DOCTYPE html>
<html>

<!-- Cabecera del HTML -->

<head>
    <title>Estadística</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>

<!-- Cuerpo del HTML -->

<body>
    <nav>
        <!-- Barra de navegación -->
        <ul>
            <li><a href="pagina.php">Inicio</a></li>
            <li><a href="control.php">Control de Stock</a></li>
            <li><a href="pagina.php"><img src="LogoB.png" alt="Logo" class="logo"></a></li>
            <li><a href="estadistica.php">Estadística</a></li>
            <li><a href="salir.php"><img src='CerrarSesion.png' width='35' height='35' alt='CerrarSesion'></a></li>
        </ul>
    </nav>
    <div class="estadistica">
        <!-- Armar el formulario HTML dentro del div -->
        <form method="post" action="">
            <label for="fecha_inicio">Fecha Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            <label for="fecha_fin">Fecha Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
            <button type="submit">Filtrar</button>
        </form>
        <canvas id="myChart"></canvas>

        <!-- Código JavaScript para generar la grafica -->
        <script>
            var ctx = document.getElementById('myChart').getContext('2d');
            var chartData = <?php echo json_encode($data); ?>;
            var labels = chartData.map(function(e) {
                return e.procedimiento;
            });
            var values = chartData.map(function(e) {
                return e.cantidad;
            });
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad de Procedimientos',
                        data: values,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>

    <!-- Botón de búsqueda -->
    <form method="POST" action="">
        <input type="text" name="search" placeholder="Buscar por DNI, Nombre o Procedimiento">
        <input type="submit" name="submit" value="Buscar" class="btn-primary">
    </form>

    <!-- Si la tabla tiene datos, se muestran -->
    <div>
        <?php if (isset($tabla_resultados)) echo $tabla_resultados; ?>
    </div>
</body>

</html>
