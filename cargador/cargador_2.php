<?php
  include('../config/conexion.php');
  require('funciones.php');

  $insert_estudiantes = $db->prepare('INSERT INTO estudiantes (n_alumno, cohorte, bloqueo, causal_bloqueo, id_plan, run, logro, fecha_logro, ult_carga) VALUES (:n_alumno, :cohorte, :bloqueo, :causal_bloqueo, :id_plan, :run, :logro, :fecha_logro, :ult_carga) ON CONFLICT DO NOTHING');
  $insert_asignaturas = $db->prepare('INSERT INTO asignaturas (id_asignatura, id_plan, asignatura, nivel, pre_requisito) VALUES (:id_asignatura, :plan, :asignatura, :nivel, :prerrequisito) ON CONFLICT DO NOTHING');
  $insert_personas = $db->prepare('INSERT INTO personas (run, dv, nombre, estamento) VALUES (:run, :dv, :nombre, :estamento) ON CONFLICT DO NOTHING');
  //leer notas
  $archivo_notas = fopen("../datos/notas.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_notas);
  $insert_notas = $db->prepare('INSERT INTO notas (run, id_plan, id_asignatura, periodo, convocatoria, calificacion, nota) VALUES (:run, :id_plan, :id_asignatura, :periodo, :convocatoria, :calificacion, :nota)');

  while (($datos = fgetcsv($archivo_notas, 0, ";")) == true) {
    $id_plan = $datos[0];
    $plan = $datos[1];
    $cohorte = $datos[2];
    $sede = $datos[3];
    $run = $datos[4];
    $dv = $datos[5];
    $nombres = $datos[6];
    $apellido_p = $datos[7];
    $apellido_m = $datos[8];
    $nombre_completo = $nombres . ' ' . $apellido_p . ' ' . $apellido_m;
    $n_alumno = $datos[9];
    $periodo = $datos[10];
    if ($periodo == '2024-02') {
      $db->beginTransaction();
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindParam(':dv', $dv);
      $insert_personas->bindParam(':nombre', $nombre_completo);
      $insert_personas->bindValue(':estamento', 'estudiante_vigente');
      $insert_personas->execute();
      $db->commit();

      $db->beginTransaction();
      $insert_estudiantes->bindParam(':n_alumno', $n_alumno);
      $insert_estudiantes->bindParam(':cohorte', $cohorte);
      $insert_estudiantes->bindValue(':bloqueo', false, PDO::PARAM_BOOL);
      $insert_estudiantes->bindValue(':causal_bloqueo', null);
      $insert_estudiantes->bindParam(':id_plan', $id_plan);
      $insert_estudiantes->bindParam(':run', $run);
      $insert_estudiantes->bindValue(':logro', 'INGRESO');
      $insert_estudiantes->bindParam(':fecha_logro', $cohorte);
      $insert_estudiantes->bindParam(':ult_carga', $periodo);
      $insert_estudiantes->execute();
      $db->commit();
    }

    $id_asignatura = $datos[11];
    $asignatura = $datos[12];
    $db->beginTransaction();
    $insert_asignaturas->bindParam(':id_asignatura', $id_asignatura);
    $insert_asignaturas->bindParam(':plan', $id_plan);
    $insert_asignaturas->bindParam(':asignatura', $asignatura);
    $insert_asignaturas->bindValue(':nivel', 1);
    $insert_asignaturas->bindValue(':prerrequisito', null);
    $insert_asignaturas->execute();
    $db->commit();

    $convocatoria = $datos[13];
    $calificacion = $datos[14];
    $nota = $datos[15];
    if (strpos($nota, ',') !== false) {
      $nota = str_replace(',', '.', $nota);
      if ($nota < 1.0) {
        $nota = 1.0;
      }
      if ($calificacion == 'R'){
        $nota = 1.0;
      }
    } else if ($nota != null) {
      if ($calificacion == 'R'){
        $nota = 1.0;
      }
      $nota = (float)$nota;
      if ($nota < 1.0) {
        $nota = 1.0;
      }
    } else if (($nota == '') || ($calificacion == '')) {
      $nota = null;
      $calificacion = null;
    } 
    
    
  

    $insert_notas->bindParam(':run', $run);
    $insert_notas->bindParam(':id_plan', $id_plan);
    $insert_notas->bindParam(':id_asignatura', $id_asignatura);
    $insert_notas->bindParam(':periodo', $periodo);
    $insert_notas->bindParam(':convocatoria', $convocatoria);
    $insert_notas->bindParam(':calificacion', $calificacion);
    $insert_notas->bindParam(':nota', $nota);
    $db->beginTransaction();
    $insert_notas->execute();
    $db->commit();
  }

  fclose($archivo_notas);

  

?>