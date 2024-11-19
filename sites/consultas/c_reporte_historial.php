<?php include("../templates/header.html"); ?>

<?php
  require("../../config/conexion.php");

  if (($_SERVER["REQUEST_METHOD"] === "POST") && isset($_POST["n_estudiante"])) {
    $n_estudiante = $_POST["n_estudiante"];
    $query = "SELECT run FROM estudiantes WHERE n_alumno = :n_estudiante";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':n_estudiante', $n_estudiante);
    $stmt->execute();
    $run = $stmt->fetch()['run'];

    $query_2 = "SELECT id_asignatura, MAX(nota) as nota_maxima, periodo
                FROM notas
                WHERE run = :run AND periodo != '2024-02'
                GROUP BY id_asignatura, periodo
                ORDER BY periodo";
    $stmt_2 = $db->prepare($query_2);
    $stmt_2->bindParam(':run', $run);
    $stmt_2->execute();
    $cursos = $stmt_2->fetchAll();

    $query_3 = "SELECT periodo, AVG(nota) as promedio_periodo
                FROM notas
                WHERE run = :run
                GROUP BY periodo
                ORDER BY periodo";
    $stmt_3 = $db->prepare($query_3);
    $stmt_3->bindParam(':run', $run);
    $stmt_3->execute();
    $promedios = $stmt_3->fetchAll();

  
  }
?>

<div class="style">
  <h1 class="title">Reporte de Historial de Estudiante</h1>
  <p class="subtitle">
    Número de Estudiante: <?php echo $n_estudiante; ?>
  </p>
</div>

<table class="styled-table">
  <tr>
    <th>ID Asignatura</th>
    <th>Nota</th>
    <th>Periodo</th>
  </tr>
  <?php
    $suma_notas = 0;
    $total_notas = 0;
    $last_periodo = "";
    foreach ($cursos as $curso) {
      if ($last_periodo !== "" && $last_periodo !== $curso[2]) {
        echo "<tr style='font-weight: bold;'>
                <td colspan='2'>Promedio del Periodo $last_periodo</td>
                <td>" . number_format($promedios[array_search($last_periodo, array_column($promedios, 'periodo'))]['promedio_periodo'], 2) . "</td>
              </tr>";
      } 
      
      $suma_notas += $curso[1];
      $total_notas += 1;

      echo "<tr>";
      echo "<td>$curso[0]</td><td>$curso[1]</td><td>$curso[2]</td>";
      echo "</tr>";

      $last_periodo = $curso[2];
    }
    echo "<tr style='font-weight: bold;'>
            <td colspan='2'>Promedio del Periodo $last_periodo</td>
            <td>" . number_format($promedios[array_search($last_periodo, array_column($promedios, 'periodo'))]['promedio_periodo'], 2) . "</td>
          </tr>";

    $ppa = $suma_notas / $total_notas;
    echo "<tr style='font-weight: bold;'>
            <td colspan='2'>Promedio Ponderado Acumulado</td>
            <td>" . number_format($ppa, 2) . "</td>
          </tr>";

  ?>
</table>

<?php include("../templates/footer.html"); ?>
    