<?php 
    include_once 'conexion.php';
    $totales = isset($_POST["totales"]) ? $_POST["totales"] : "";
    $anios = isset($_POST["anios"]) ? $_POST["anios"] : "";

    $consulta="SELECT SUM(venta) as ventas, YEAR(fecha) as anio FROM detalle_fac 
        INNER JOIN encabezado_fac on detalle_fac.codigo=encabezado_fac.codigo
        GROUP BY anio";

    if ($totales && $totales != "" && @floatval($totales)) {
        $consulta= $consulta . " HAVING ventas >= $totales";

        if ($anios && $anios != "") {
            $consulta= $consulta . " AND anio IN(" . implode(',', $anios) . ")";
        }
    } else {
        if ($anios && $anios != "") {
            $consulta= $consulta . " HAVING anio IN(" . implode(',', $anios) . ")";
        }
    }

    $ejecucion= mysqli_query($conexion,$consulta);

    $anios_query = "SELECT DISTINCT YEAR(fecha) as anio FROM encabezado_fac ORDER BY anio asc";
    $anios_ejecucion = mysqli_query($conexion, $anios_query);

    $anios_ejecucion->data_seek(0);
    $primer_anio = $anios_ejecucion->fetch_assoc();
    $primer_anio = $primer_anio["anio"];

    $anios_ejecucion->data_seek($anios_ejecucion->num_rows - 1);
    $ultimo_anio = $anios_ejecucion->fetch_assoc();
    $ultimo_anio = $ultimo_anio["anio"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/series-label.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<link rel="stylesheet" href="estilo.css">
    <title>Graficos 2</title>
</head>
<body>
    <form action="index.php" method="POST" style="text-align: center;">
        <label for="totales">Ventas por anio iguales o mayores a:</label>
        <input type="text" id="totales" name="totales" value="<?php echo $totales; ?>">
        <?php 
            $anios_ejecucion->data_seek(0);
            while($dato = mysqli_fetch_assoc($anios_ejecucion)) {
                echo "<label for='". $dato["anio"] ."'>". $dato["anio"] ."</label>";
                echo "<input type='checkbox' name='anios[]' ". ( (isset($_POST['anios']) && in_array($dato["anio"], $_POST['anios'])) ? "checked" : "") ." id='". $dato["anio"] ."' value='". $dato["anio"] ."'>";
            }
        ?>
        <button type="submit" name="submit" value="submit">Graficar</button>
    </form>
<figure class="highcharts-figure">
    <div id="container"></div>
</figure>
</body>
</html>
<script >
    
Highcharts.chart('container', {
    

    title: {
        text: 'Estadisticas de ventas',
        align: 'center'
    },

    subtitle: {
        text: 'Ventas por anios',
        align: 'center'
    },

    yAxis: {
        title: {
            text: 'Total ventas en dolares $'
        }
    },

    xAxis: {
        accessibility: {
            rangeDescription: '<?php echo "Rango: desde $primer_anio hasta $ultimo_anio"; ?>'
        }
    },

    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle'
    },

    plotOptions: {
        series: {
            label: {
                connectorAllowed: false
            },
            pointStart: <?php echo $primer_anio; ?>
        }
    },

    series: [{
        name: 'Ventas anuales',
        data: [
           <?php
                while($dato=mysqli_fetch_assoc($ejecucion))
                {
                    $dato_ventas = number_format($dato["ventas"],2,'.','');
                    $dato_fecha = $dato["anio"];
                    echo "[". $dato_fecha . "," . $dato_ventas ."],";
                }
            ?>
            ]
   
    }],

    responsive: {
        rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                }
            }
        }]
    }

});


</script>