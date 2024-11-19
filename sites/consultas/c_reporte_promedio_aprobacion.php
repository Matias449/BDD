<?php include('../templates/header.html');   ?>

<?php
  require("../../config/conexion.php");
  if (($_SERVER["REQUEST_METHOD"] == "POST") && isset($_POST["curso"])) {
    $id_asignatura = $_POST["curso"];
    $query = "SELECT
                id_asignatura,
                (COUNT(CASE WHEN nota > 4 THEN 1 END) * 100.0 / COUNT(*)) AS porcentaje_aprobacion
                FROM
                    notas
                WHERE
                    id_asignatura = :id_asignatura
                GROUP BY
                    id_asignatura;
                ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_asignatura', $id_asignatura);
    $stmt->execute();
    $reporte = $stmt->fetchAll();
  }
?>

<div class="style">
  <h1 class="title">Reporte de Porcentaje de Aprobación de Asignatura</h1>
  <p class="subtitle">ID Asignatura: <?php echo $id_asignatura; ?></p>
</div>
<table class="styled-table">
  <tr>
    <th>ID Asignatura</th>
    <th>Porcentaje de Aprobación</th>
  </tr>
  <?php
    foreach ($reporte as $r) {
      echo "<tr>";
      echo "<td>$r[0]</td><td>" . number_format($r[1], 2) . "%</td>";
      echo "";
      echo "</tr>";
    }
  ?>
</table>

<?php include('../templates/footer.html'); ?>


  