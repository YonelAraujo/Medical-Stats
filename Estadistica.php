<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "medical_stats") or die("Problemas con la conexión");

if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if (empty($user) || empty($pass)) {
        $_SESSION['error'] = "Usuario o contraseña no pueden estar vacíos.";
        header("Location: login.php");
        exit();
    } else {
        $registros = mysqli_query($conexion, "SELECT * FROM usuarios WHERE usuario = '$user'") or die("Error de conexión" . mysqli_error($conexion));
        if ($reg = mysqli_fetch_array($registros)) {
            if ($pass == $reg['contrasena']) {
                $_SESSION['usuario'] = $user;
            } else {
                $_SESSION['error'] = "Usuario o contraseña incorrecto";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Usuario no encontrado.";
            header("Location: login.php");
            exit();
        }
    }
}


$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '2000-01-01';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');

// Realizar la consulta para obtener los procedimientos y su cantidad
$sql = "SELECT procedimiento, COUNT(*) as cantidad, SUM(CASE WHEN urgencia = 1 THEN 1 ELSE 0 END) as urgencias 
        FROM pacientes 
        WHERE fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
        GROUP BY procedimiento";
$result = mysqli_query($conexion, $sql) or die("Problemas en el select:" . mysqli_error($conexion));

$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 resultados";
}

$search_results = [];
if (isset($_POST['submit'])) {
    $search = $_POST['search'];
    
    $sql_search = "SELECT * FROM pacientes WHERE dni LIKE '%$search%' OR nombre LIKE '%$search%' OR procedimiento LIKE '%$search%'";
    $result_search = mysqli_query($conexion, $sql_search) or die("Problemas en el select:" . mysqli_error($conexion));
    
    $tabla_resultados = '';
    if ($result_search->num_rows > 0) {
        $tabla_resultados = "<div class='table-container'><table border='1'>";
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
                <th>Urgencia</th>
              </tr></thead>";
        while($row = $result_search->fetch_assoc()) {
            $search_results[] = $row;
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
                    <td>{$row['urgencia']}</td>
                </tr>";
        }
        $tabla_resultados .= "</table></div>";
    } else {
        $tabla_resultados = "No se encontraron resultados";
    }
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Estadística</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="pagina.php">Inicio</a></li>
            <li><a href="stock.php">Control de Stock</a></li>
            <li><a href="pagina.php"><img src="LogoB.png" alt="Logo" class="logo"></a></li>
            <li><a href="estadistica.php">Estadística</a></li>
            <li><a href="salir.php"><img src='CerrarSesion.png' width='35' height='35' alt='CerrarSesion'></a></li>
        </ul>
    </nav>
    <div class="estadistica">
        <form method="post" action="">
            <label for="fecha_inicio">Fecha Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            <label for="fecha_fin">Fecha Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
            <button type="submit">Filtrar</button>
        </form>
        <canvas id="myChart"></canvas>
        <script>
            var ctx = document.getElementById('myChart').getContext('2d');
            var chartData = <?php echo json_encode($data); ?>;
            var labels = chartData.map(function(e) { return e.procedimiento; });
            var values = chartData.map(function(e) { return e.cantidad; });
            var urgencias = chartData.map(function(e) { return e.urgencias; });

            // Debugging output
            console.log("Labels: ", labels); // Para depuración
            console.log("Values: ", values); // Para depuración
            console.log("Urgencias: ", urgencias); // Para depuración

            // Ajustar la configuración del gráfico
            var myChart = new Chart(ctx, {  
                type: 'bar', // Cambia el tipo de gráfico a 'bar'
                data: {
                    labels: labels,
                    datasets: [
                    {
                        label: 'Cantidad de Procedimientos',
                        data: values,
                        backgroundColor: 'rgba(0, 123, 255, 0.5)', // color azul
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Urgencias',
                        data: urgencias,
                        type: 'line',
                        backgroundColor: 'rgba(255, 99, 132, 0.5)', // color rojo
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        lineTension: 0
                    }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                beginAtZero: true // Asegura que el eje Y comience en 0
                            }
                        }
                    }
                }
            });
        </script>
    </div>
    <form method="POST" action="">
        <input type="text" name="search" placeholder="Buscar por DNI, Nombre o Procedimiento">
        <input type="submit" name="submit" value="Buscar" class="btn-primary">
    </form>
    <div>
        <?php if (isset($tabla_resultados)) echo $tabla_resultados; ?>
    </div>
</body>
</html>
