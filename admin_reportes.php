<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'hotel_rivo');

// Datos para estadísticas
$ingresos = $conn->query("SELECT SUM(monto) AS total_ingresos FROM pagos")->fetch_assoc();
$ocupacion = $conn->query("SELECT COUNT(*) AS habitaciones_ocupadas FROM habitaciones WHERE estado = 'ocupada'")->fetch_assoc();
$reservas_confirmadas = $conn->query("SELECT COUNT(*) AS reservas_confirmadas FROM reservas WHERE estado = 'confirmada'")->fetch_assoc();
$promedio_puntuaciones = $conn->query("SELECT AVG(puntuacion) AS promedio_puntuacion FROM comentarios")->fetch_assoc();

// Para ingresos mensuales, si deseas mostrar los ingresos de cada mes
$ingresos_mensuales = $conn->query("SELECT MONTH(fecha_pago) AS mes, SUM(monto) AS ingresos_mes FROM pagos GROUP BY MONTH(fecha_pago)")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="css/admin_estilos.css">
    <meta charset="UTF-8">
    <title>Reportes y Estadísticas - Hotel Rivo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Reportes y Estadísticas - Hotel Rivo</h1>

    <!-- Reporte de pagos confirmados -->
    <p><strong>Total de Pagos Confirmados:</strong> <?= $ingresos['total_ingresos'] ?></p>

    <!-- Reporte de Promedio de Puntuaciones de Comentarios -->
    <p><strong>Promedio de Puntuaciones de Comentarios:</strong> <?= number_format($promedio_puntuaciones['promedio_puntuacion'], 2) ?></p>

    <!-- Reporte de Ocupación Actual -->
    <p><strong>Ocupación Actual:</strong> <?= $ocupacion['habitaciones_ocupadas'] ?> habitaciones ocupadas</p>

    <!-- Gráfico de estadísticas generales -->
    <canvas id="estadisticasChart" width="400" height="200"></canvas>
    <script>
        var ctx = document.getElementById('estadisticasChart').getContext('2d');
        var estadisticasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Ocupación', 'Reservas Confirmadas'],
                datasets: [{
                    label: 'Estadísticas del Hotel',
                    data: [<?= $ingresos['total_ingresos'] ?>, <?= $ocupacion['habitaciones_ocupadas'] ?>, <?= $reservas_confirmadas['reservas_confirmadas'] ?>],
                    backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)', 'rgba(75, 192, 192, 1)'],
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

    <!-- Gráfico de ingresos mensuales -->
    <canvas id="ingresosMensualesChart" width="400" height="200"></canvas>
    <script>
        var ctx2 = document.getElementById('ingresosMensualesChart').getContext('2d');
        var ingresosMensualesChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: [<?php 
                    foreach ($ingresos_mensuales as $mes) {
                        echo "'Mes " . $mes['mes'] . "',";
                    } ?>],
                datasets: [{
                    label: 'Ingresos Mensuales',
                    data: [<?php 
                        foreach ($ingresos_mensuales as $mes) {
                            echo $mes['ingresos_mes'] . ",";
                        } ?>],
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
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

    <p><a href="admin_dashboard.php">Volver al Panel</a></p>
</body>
</html>
