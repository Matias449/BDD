<?php include('../templates/header.html');   ?>

<?php
  require("../../config/conexion.php");

  if (($_SERVER["REQUEST_METHOD"] === "POST") && isset($_POST["n_estudiante"])) {
    $n_estudiante = $_POST["n_estudiante"];
    $periodo_actual = '2024-02';
    $query = "SELECT run FROM estudiantes WHERE n_alumno = :n_estudiante";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':n_estudiante', $n_estudiante);
    $stmt->execute();
    $run = $stmt->fetch()['run'];

    $query_2 = "SELECT id_plan, id_asignatura FROM notas WHERE run = :run AND periodo = :periodo_actual";
    $stmt_2 = $db->prepare($query_2);
    $stmt_2->bindParam(':run', $run);
    $stmt_2->bindParam(':periodo_actual', $periodo_actual);
    $stmt_2->execute();
    $cursos_actuales = $stmt_2->fetchAll();

    $cursos = [];
    foreach ($cursos_actuales as $curso) {
      $query_3 = "SELECT id_asignatura FROM prerequisitos WHERE id_plan = :id_plan AND id_asignatura = :id_asignatura";
      $stmt_3 = $db->prepare($query_3);
      $stmt_3->bindParam(':id_plan', $curso['id_plan']);
      $stmt_3->bindValue(':id_asignatura', substr($curso['id_asignatura'], -4));
      $stmt_3->execute();
      $curso = $stmt_3->fetch();
      if ($curso) {
        $cursos[] = $curso;
      }
    }    
  }
?>

<div class="style">
  <h1 class="title">Reporte de Proyección de Estudiante</h1>
  <p class="subtitle">Número de Estudiante: <?php echo $n_estudiante; ?></p>
</div>
<table class="styled-table">
  <tr>
    <th>ID Asignatura</th>
  </tr>
  <?php
    foreach ($cursos as $curso) {
      echo "<tr>";
      echo "<td>$curso[0]</td>";
      echo "</tr>";
    }
  ?>
</table>

<?php include('../templates/footer.html'); ?>