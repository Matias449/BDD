<?php include('../templates/header.html');?>

<?php

  require("../../config/conexion.php");

  $query = "SELECT * FROM estudiantes WHERE ult_carga='2024-02'";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $estudiantes = $stmt->fetchAll();
  $en_nivel = 0;
  $no_en_nivel = 0;

  $cohortes = ['2020-01', '2020-02', '2021-01', '2021-02', '2022-01', '2022-02', '2023-01', '2023-02', '2024-01'];
  foreach ($estudiantes as $estudiante) {
    if (str_contains($estudiante['logro'], 'INGRESO')) {
      if ($estudiante['cohorte'] == '2024-02') {
        $en_nivel += 1;
      } else {
        $no_en_nivel += 1;
      }
    } else if (str_contains($estudiante['logro'], 'SEMESTRE')) {
      $logro = intval(substr($estudiante['logro'], 0, 1));
      $cohortes_index = array_search($estudiante['cohorte'], $cohortes);
      if ($cohortes_index) {
        if ($cohortes_index + $logro == 9) {
          $en_nivel += 1;
        } else {
          $no_en_nivel += 1;
        }
      }
    }
  }
?>

<body>
  <div class="style">
    <h1 class="title">Reporte de Estudiantes</h1>
    <h2 class="subtitle">Estudiantes en Nivel</h2>
    <p class="description"><?php echo $en_nivel; ?></p>
    <h2 class="subtitle">Estudiantes no en Nivel</h2>
    <p class="description"><?php echo $no_en_nivel; ?></p>
  </div>
</body>

<?php include('../templates/footer.html');?>



      
    
