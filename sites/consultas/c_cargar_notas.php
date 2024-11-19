<?php include('../templates/header.html');   ?>

<body>
<?php
  session_start();
  require("../../config/conexion.php");
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    echo "Archivo subido correctamente \n";
    $archivo = $_FILES['file']['tmp_name'];
    $handle = fopen($archivo, "r");
    //saltar la primera linea
    fgetcsv($handle);

    try {
      $db->beginTransaction();
      
      //crear tabla temp acta
      $db->exec('CREATE TEMP TABLE acta (
        n_alumno INT PRIMARY KEY,
        run INT NOT NULL,
        id_asignatura varchar(12) NOT NULL,
        periodo varchar(7) NOT NULL,
        calificacion varchar(4),
        nota varchar(4)
      )');
      $db->commit();

      $contenido_archivo = [];


      while (($datos = fgetcsv($handle, 0, ";")) == true) {
        $n_alumno = $datos[0];
        if ($n_alumno == '' || $n_alumno == null) {
          throw new Exception("Error: n_alumno nulo, corrige el archivo manualmente");
        }
        $run = $datos[1];
        if ($run == '' || $run == null) {
          throw new Exception("Error: run nulo, corrige el archivo manualmente");
        }
        $id_asignatura = $datos[2];
        $seccion = $datos[3];
        $periodo = $datos[4];
        if ($periodo[6] == '1') {
          $periodo = substr($periodo, 0, 4) . '-01';
        } else if ($periodo[6] == '2') {
          $periodo = substr($periodo, 0, 4) . '-02';
        }
        $oportunidad1 = $datos[5];
        if (str_contains($oportunidad1, ',')) {
          $oportunidad1 = str_replace(',', '.', $oportunidad1);
        }
        $oportunidad2 = $datos[6];
        if (str_contains($oportunidad2, ',')) {
          $oportunidad2 = str_replace(',', '.', $oportunidad2);
        }
        $nota_final = null;
        $calificacion = null;

        //verificar run a n_alumno
        $query = "SELECT n_alumno FROM estudiantes WHERE run = :run";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':run', $run);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
          throw new Exception("Error: run $run no corresponde a ningún n_alumno, corrige el archivo manualmente");
        }
        $num_alumno = $stmt->fetch()['n_alumno'];
        if ($num_alumno != $n_alumno) {
          throw new Exception("Error: run $run no corresponde a n_alumno $n_alumno, corrige el archivo manualmente");
        }

        //verificar fila nula
        if ($n_alumno == '' || $n_alumno == null || $run == '' || $run == null || $id_asignatura == '' || $id_asignatura == null || $seccion == '' || $seccion == null || $periodo == '' || $periodo == null) {
          throw new Exception("Error: fila nula, corrige el archivo manualmente");
        }

        //verificar notas
        if (!str_contains($oportunidad1, 'P') && $oportunidad1 != '' && $oportunidad1 != null) {
          $nota1 = floatval($oportunidad1);
          if ($nota1 < 1.0 || $nota1 > 7.0) {
            throw new Exception("Error: nota de $n_alumno en oportunidad 1 fuera de rango, corrige el archivo manualmente");
          } else if ($oportunidad1 >= 4.0) {
            $nota_final = $nota1;
          } else {
            if ($oportunidad2 == '' || $oportunidad2 == null) {
              $nota_final = floatval($oportunidad1);
            } else if (!str_contains($oportunidad2, 'P')) {
              $nota_final = floatval($oportunidad2);
            } else if ($oportunidad2 == 'P') {
              $nota_final = null;
            } else if ($oportunidad2 == 'NP') {
              $nota_final = 1.0;
            }
          }
            
        } else if ($oportunidad1 == 'P') {
          if ($oportunidad2 != '' && $oportunidad2 != null) {
            throw new Exception("Error: nota de $n_alumno en oportunidad 2 estando pendiente en oportunidad 1, corrige el archivo manualmente");
          } else {
            $nota_final = null;
            $calificacion = 'P';
          }
        } else if ($oportunidad1 == 'NP') {
          if ($oportunidad2 == '' || $oportunidad2 == null) {
            $nota_final = null;
            $calificacion = 'NP';
          } else {
            if (!str_contains($oportunidad2, 'P')) {
              $nota2 = floatval($oportunidad2);
              if ($nota2 < 1.0 || $nota2 > 7.0) {
                throw new Exception("Error: nota de $n_alumno en oportunidad 2 fuera de rango, corrige el archivo manualmente");
              } else {
                $nota_final = $nota2;
                
              } 
            }
          }
        }
        $insert_acta = $db->prepare('INSERT INTO acta (n_alumno, run, id_asignatura, periodo, calificacion, nota) VALUES (:n_alumno, :run, :id_asignatura, :periodo, :calificacion, :nota)');
        $db->beginTransaction();
        $insert_acta->bindParam(':n_alumno', $n_alumno);
        $insert_acta->bindParam(':run', $run);
        $insert_acta->bindParam(':id_asignatura', $id_asignatura);
        $insert_acta->bindParam(':periodo', $periodo);
        $insert_acta->bindParam(':calificacion', $calificacion);
        $insert_acta->bindParam(':nota', $nota_final);
        $insert_acta->execute();
        $db->commit();

        $contenido_archivo[] = [$n_alumno, $run, $id_asignatura, $periodo, $calificacion, $nota_final];

      }
      fclose($handle);

      $db->exec("CALL generar_vista()");

      $_SESSION['acta'] = $contenido_archivo;

      echo "acta y vista generadas correctamente";
    } catch (Exception $e) {
      echo "Failed: " . $e->getMessage();
    }
  } else {
    echo "Error al subir el archivo" . $_FILES['file']['error'];
  }
?>

<h1 class="title">Acta Notas</h1>
<table class="styled-table">
  <tr>
    <th>Número de Alumno</th>
    <th>Nombre</th>
    <th>ID Asignatura</th>
    <th>Periodo</th>
    <th>Nota</th>
  </tr>
  <?php
    $query = "SELECT * FROM vista_acta";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $acta = $stmt->fetchAll();
    foreach ($acta as $a) {
      if ($a[4] == null) {
        $a[4] = 'P';
      }
      echo "<tr>";
      echo "<td>$a[0]</td><td>$a[1]</td><td>$a[2]</td><td>$a[3]</td><td>$a[4]</td>";
      echo "</tr>";
    }
  ?>

</table>

<h2 class="subtitle">Si la información del acta es correcta, suba las notas al sistema</h2>
<form action="c_subir_notas.php" method="post" >
  <div style="text-align: center;">
    <input type="submit" value="Subir notas al sistema" style="height: 30px; width: 250px; font-size: 16px;">
  </div>
</form>





<?php include('../templates/footer.html'); ?>