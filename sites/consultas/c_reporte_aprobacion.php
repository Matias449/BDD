<?php include('../templates/header.html');   ?>

<body>
<?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['periodo'])) {
    include("../../config/conexion.php");
    $periodo = $_POST['periodo'];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $periodo = $_POST['periodo'];
      if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
        echo "<p style='color:red;'>El periodo debe tener el formato aaaa-mm y cada carácter debe ser un número.</p>";
      }
    }
      
    $query = "SELECT 
              a.id_asignatura,
              a.asignatura,
              COUNT(*) AS total_notas,
              COUNT(CASE WHEN n.nota > 4 THEN 1 END) AS notas_aprobadas,
              (COUNT(CASE WHEN n.nota > 4 THEN 1 END) * 100.0 / COUNT(*)) AS porcentaje_aprobacion
              FROM 
                  notas n
              JOIN 
                  asignaturas a ON n.id_asignatura = a.id_asignatura
              WHERE 
                  n.periodo = :periodo
              GROUP BY 
                  a.id_asignatura, a.asignatura;
              ";
    

    $stmt = $db->prepare($query);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->execute();
    $reporte = $stmt->fetchAll();
  }
?>
<h1 class="title">Reporte de Aprobación de Periodo</h1>
<p class="subtitle">Periodo: <?php echo $periodo; ?></p>
<table class="styled-table">
  <tr>
    <th>ID Asignatura</th>
    <th>Asignatura</th>
    <th>Total de Notas</th>
    <th>Notas Aprobadas</th>
    <th>Porcentaje de Aprobación</th>
  </tr>
  <?php
    foreach ($reporte as $r) {
      echo "<tr>";
      echo "<td>$r[0]</td><td>$r[1]</td><td>$r[2]</td><td>$r[3]</td><td>" . number_format($r[4], 2) . "%</td>";
      echo "";
      echo "</tr>";
    }
  ?>
</table>


<?php include('../templates/footer.html'); ?>
