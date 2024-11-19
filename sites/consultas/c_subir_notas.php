<?php include('../templates/header.html');   ?>

<body>
<?php
  session_start();
  if (isset($_SESSION['acta'])) {
    $actinha = $_SESSION['acta'];
  }
  require("../../config/conexion.php");
?>

<h1 class="title">Acta de Notas</h1>
<h2 class="subtitle">Subida Exitosa</h2>

<table class='styled-table'>
  <tr>
    <th>Numero Alumno</th>
    <th>Run</th>
    <th>Id Asignatura</th>
    <th>Periodo</th>
    <th>Nota</th>
  </tr>
  <?php
    foreach ($actinha as $acta) {
      echo "<tr>";
      echo "<td>$acta[0]</td>";
      echo "<td>$acta[1]</td>";
      echo "<td>$acta[2]</td>";
      echo "<td>$acta[3]</td>";
      if ($acta[5] == null) {
        $acta[5] = 'P';
      }
      echo "<td>$acta[5]</td>";
      echo "</tr>";
    }
  ?>
</table>

<?php
  $db->beginTransaction();
  $query_plan = "SELECT id_plan FROM asignaturas WHERE id_asignatura = :id_asignatura";
  $stmt_plan = $db->prepare($query_plan);
  $stmt_plan->bindParam(':id_asignatura', $actinha[0][2]);
  $stmt_plan->execute();
  $id_plan = $stmt_plan->fetch()['id_plan'];
  
  $query = "INSERT INTO notas (run, id_plan, id_asignatura, periodo, convocatoria, nota) VALUES (:run, :id_plan, :id_asignatura, :periodo, :convocatoria, :nota)";
  $stmt = $db->prepare($query);
  foreach ($actinha as $acta) {
    $stmt->bindParam(':run', $acta[1]);
    $stmt->bindParam(':id_plan', $id_plan);
    $stmt->bindParam(':id_asignatura', $acta[2]);
    $stmt->bindParam(':periodo', $acta[3]);
    $convocatoria = 'DIC';
    $stmt->bindParam(':convocatoria', $convocatoria);
    $stmt->bindParam(':nota', $acta[5]);
    $stmt->execute();
  }
  $db->commit();
  unset($_SESSION['acta']);
  

?>

<?php include('../templates/footer.html'); ?>


