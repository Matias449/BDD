<?php
  include('../config/conexion.php');
  require('funciones.php');
  require_once('crear_tablas.php');

  //leer planes
  $archivo_planes = fopen("../datos/planes.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_planes);

  $insert_nom_planes = $db->prepare('INSERT INTO nom_planes (codigo_plan, nombre) VALUES (:codigo_plan, :plan) ON CONFLICT DO NOTHING');
  $insert_carrera = $db->prepare('INSERT INTO carreras (carrera) VALUES (:nombre_carrera) ON CONFLICT DO NOTHING');
  $insert_planes = $db->prepare('INSERT INTO planes (codigo_plan, facultad, carrera, jornada, sede, grado, modalidad, inicio) VALUES (:codigo_plan, :facultad, :carrera, :jornada, :sede, :grado, :modalidad, :inicio) ON CONFLICT DO NOTHING');
  $insert_facultad = $db->prepare('INSERT INTO facultades (facultad) VALUES (:nombre_facultad) ON CONFLICT DO NOTHING');


  $array_carreas = array();
  $array_nom_planes = array();
  $array_facultades = array();

  while (($datos = fgetcsv($archivo_planes, 0, ";")) == true) {
    $codigo_plan = $datos[0];
    $facultad = $datos[1];
    $carrera = $datos[2];
    $plan = $datos[3];
    $jornada = $datos[4];
    $sede = $datos[5];
    $grado = $datos[6];
    $modalidad = $datos[7];
    $inicio = $datos[8];
    $fecha = date_create_from_format('d/m/y', $inicio);
    $fecha_sql = $fecha->format('Y-m-d');

    $array_carreas[] = $carrera;
    $array_nom_planes[] = array($codigo_plan, $plan);
    $array_facultades[] = $facultad;

    try {
      $db->beginTransaction();
      $insert_carrera->bindParam(':nombre_carrera', $carrera);
      $insert_carrera->execute();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }

    try {
      $db->beginTransaction();
      $insert_facultad->bindParam(':nombre_facultad', $facultad);
      $insert_facultad->execute();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
    }

    try {
      $insert_planes->bindParam(':codigo_plan', $codigo_plan);
      $insert_planes->bindParam(':facultad', $facultad);
      $insert_planes->bindParam(':carrera', $carrera);
      $insert_planes->bindParam(':jornada', $jornada);
      $insert_planes->bindParam(':sede', $sede);
      $insert_planes->bindParam(':grado', $grado);
      $insert_planes->bindParam(':modalidad', $modalidad);
      $insert_planes->bindParam(':inicio', $fecha_sql);

      $db->beginTransaction();
      $insert_planes->execute();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      echo "Failed: " . $e->getMessage();
    }

  }


  
  foreach ($array_nom_planes as $nom_plan) {
    $insert_nom_planes->bindParam(':codigo_plan', $nom_plan[0]);
    $insert_nom_planes->bindParam(':plan', $nom_plan[1]);
    $db->beginTransaction();
    $insert_nom_planes->execute();
    $db->commit();
  }

  fclose($archivo_planes);


  //Leer asignaturas
  $archivo_asignaturas = fopen("../datos/asiganturas.csv", "r");
  $array_asignaturas = array();
  //saltar la primera linea
  fgetcsv($archivo_asignaturas);

  $insert_asignaturas = $db->prepare('INSERT INTO asignaturas (id_asignatura, id_plan, asignatura, nivel, pre_requisito) VALUES (:id_asignatura, :plan, :asignatura, :nivel, :prerrequisito) ON CONFLICT DO NOTHING');

  while (($datos = fgetcsv($archivo_asignaturas, 0, ";")) == true) {
    $id_plan = $datos[0];
    $id_asignatura = $datos[1];
    $asignatura = $datos[2];
    $nivel = $datos[3];
    if ($nivel == "") {
      $nivel = 1;
    }
    $prerrequisito = $datos[4];
    if ($prerrequisito == "") {
      $prerrequisito = null;
    }

    $array_asignaturas[] = array($id_plan, $id_asignatura, $asignatura, $nivel, $prerrequisito);
  }

  foreach ($array_asignaturas as $asignatura) {
    $insert_asignaturas->bindParam(':id_asignatura', $asignatura[1]);
    $insert_asignaturas->bindParam(':plan', $asignatura[0]);
    $insert_asignaturas->bindParam(':asignatura', $asignatura[2]);
    $insert_asignaturas->bindParam(':nivel', $asignatura[3]);
    $insert_asignaturas->bindParam(':prerrequisito', $asignatura[4]);
    $db->beginTransaction();
    $insert_asignaturas->execute();
    $db->commit();
  }

  
  fclose($archivo_asignaturas);


  //leer prerrequisitos
  $archivo_prerrequisitos = fopen("../datos/prerrequisitos.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_prerrequisitos);
  $array_prerrequisitos = array();
  $insert_prerrequisitos = $db->prepare('INSERT INTO prerequisitos (id_plan, id_asignatura, pre1, pre2) VALUES (:id_plan, :id_asignatura, :pre1, :pre2)');

  while (($datos = fgetcsv($archivo_prerrequisitos, 0, ";")) == true) {
    $id_plan = $datos[0];
    $id_asignatura = $datos[1];
    $pre1 = $datos[4];
    if ($pre1 == "") {
      $pre1 = null;
      continue;
    }
    $pre2 = $datos[5];

    $array_prerrequisitos[] = array($id_plan, $id_asignatura, $pre1, $pre2);
  }

  foreach ($array_prerrequisitos as $prerrequisito) {
    $insert_prerrequisitos->bindParam(':id_plan', $prerrequisito[0]);
    $insert_prerrequisitos->bindParam(':id_asignatura', $prerrequisito[1]);
    $insert_prerrequisitos->bindParam(':pre1', $prerrequisito[3]);
    $insert_prerrequisitos->bindParam(':pre2', $prerrequisito[4]);
    $db->beginTransaction();
    $insert_prerrequisitos->execute();
    $db->commit();
  }

  fclose($archivo_prerrequisitos);

  //leer planeacion
  $archivo_planeacion = fopen("../datos/planeacion.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_planeacion);
  $array_departamentos = array();
  $insert_departamentos = $db->prepare('INSERT INTO departamentos (id_depto, departamento, facultad) VALUES (:id_depto, :departamento, :facultad) ON CONFLICT DO NOTHING');
  
  
  while (($datos = fgetcsv($archivo_planeacion, 0, ";")) == true) {
    $periodo = $datos[0];
    $sede = $datos[1];
    $facultad = $datos[2];
    $id_depto = $datos[3];
    $departamento = $datos[4];
    $id_asignatura = $datos[5];
    $asignatura = $datos[6];
    $seccion = $datos[7];
    $duración = $datos[8];
    $jornada = $datos[9];
    $cupo = $datos[10];
    $inscritos = $datos[11];
    $dia = $datos[12];
    $hora_inicio = $datos[13];
    $hora_fin = $datos[14];
    $fecha_inicio = $datos[15];
    $fecha_fin = $datos[16];
    $lugar = $datos[17];
    $edificio = $datos[18];
    $profesor = $datos[19];
    $run = $datos[20];
    $nombre_profe = $datos[21];
    $apellido_profe_1 = $datos[22];
    if ($apellido_profe_1 == 0) {
      $apellido_profe_1 = '';
    }
    $apellido_profe_2 = $datos[23];
    if ($apellido_profe_2 == 0) {
      $apellido_profe_2 = '';
    }
    $jerarquia = $datos[24];
    $nombre_completo = $nombre_profe . ' ' . $apellido_profe_1 . ' ' . $apellido_profe_2;
       

    $array_facultades[] = $facultad;
    $array_departamentos[] = array($id_depto, $departamento, $facultad);
  }


  foreach ($array_facultades as $facultad) {
    $insert_facultad->bindParam(':nombre_facultad', $facultad);
    $db->beginTransaction();
    $insert_facultad->execute();
    $db->commit();
  }

  
  foreach ($array_departamentos as $departamento) {
    $insert_departamentos->bindParam(':id_depto', $departamento[0]);
    $insert_departamentos->bindParam(':departamento', $departamento[1]);
    $insert_departamentos->bindParam(':facultad', $departamento[2]);
    $db->beginTransaction();
    $insert_departamentos->execute();
    $db->commit();
  }

  fclose($archivo_planeacion);

  //leer estudiantes
  $archivo_estudiantes = fopen("../datos/estudiantes.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_estudiantes);
  $insert_estudiantes = $db->prepare('INSERT INTO estudiantes (n_alumno, cohorte, bloqueo, causal_bloqueo, id_plan, run, logro, fecha_logro, ult_carga) VALUES (:n_alumno, :cohorte, :bloqueo, :causal_bloqueo, :id_plan, :run, :logro, :fecha_logro, :ult_carga) ON CONFLICT DO NOTHING');
  $insert_exalumnos = $db->prepare('INSERT INTO exalumnos (n_alumno, cohorte, id_plan, run, ult_carga, titulo) VALUES (:n_alumno, :cohorte, :id_plan, :run, :ult_carga, :titulo) ON CONFLICT DO NOTHING');
  $insert_personas = $db->prepare('INSERT INTO personas (run, dv, nombre, estamento) VALUES (:run, :dv, :nombre, :estamento) ON CONFLICT DO NOTHING');

  while (($datos = fgetcsv($archivo_estudiantes, 0, ";")) == true) {
    $id_plan = $datos[0];
    $carrera = $datos[1];
    $cohorte = $datos[2];
    $n_alumno = $datos[3];
    $bloqueo = $datos[4];
    $causal_bloqueo = $datos[5];
    $run = $datos[6];
    $dv = $datos[7];
    $nombre_1 = $datos[8];
    $nombre_2 = $datos[9];
    $apellido_paterno = $datos[10];
    $apellido_materno = $datos[11];
    $logro = $datos[12];
    $fecha_logro = $datos[13];
    $ult_carga = $datos[14];
    
    if (!is_string($apellido_materno)) {
      $nombre = $nombre_1 . ' ' . $nombre_2 . ' ' . $apellido_paterno;
    } else {
      $nombre = $nombre_1 . ' ' . $nombre_2 . ' ' . $apellido_paterno . ' ' . $apellido_materno;
    }

    if (str_contains($logro, 'LICENCIATURA')) {
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindParam(':dv', $dv);
      $insert_personas->bindParam(':nombre', $nombre);
      $insert_personas->bindValue(':estamento', 'exalumno');
      $db->beginTransaction();
      $insert_personas->execute();
      $db->commit();

      $titulo = $logro;
      $insert_exalumnos->bindParam(':titulo', $titulo);
      $insert_exalumnos->bindParam(':n_alumno', $n_alumno);
      $insert_exalumnos->bindParam(':cohorte', $cohorte);
      $insert_exalumnos->bindParam(':id_plan', $id_plan);
      $insert_exalumnos->bindParam(':run', $run);
      $insert_exalumnos->bindParam(':ult_carga', $ult_carga);
      $db->beginTransaction();
      $insert_exalumnos->execute();
      $db->commit();

    } else if (str_contains($logro, 'TITULO')) {
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindParam(':dv', $dv);
      $insert_personas->bindParam(':nombre', $nombre);
      $insert_personas->bindValue(':estamento', 'exalumno');
      $db->beginTransaction();
      $insert_personas->execute();
      $db->commit();

      $titulo = substr($logro, 35, 52);
      $insert_exalumnos->bindParam(':titulo', $titulo);
      $insert_exalumnos->bindParam(':n_alumno', $n_alumno);
      $insert_exalumnos->bindParam(':cohorte', $cohorte);
      $insert_exalumnos->bindParam(':id_plan', $id_plan);
      $insert_exalumnos->bindParam(':run', $run);
      $insert_exalumnos->bindParam(':ult_carga', $ult_carga);
      $db->beginTransaction();
      $insert_exalumnos->execute();
      $db->commit();

    } else {
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindParam(':dv', $dv);
      $insert_personas->bindParam(':nombre', $nombre);
      if ($ult_carga == '2024-02') {
        $insert_personas->bindValue(':estamento', 'estudiante_vigente');
      } else {
        $insert_personas->bindValue(':estamento', 'estudiante_no_vigente');
      }
      $db->beginTransaction();
      $insert_personas->execute();
      $db->commit();

      $insert_estudiantes->bindParam(':n_alumno', $n_alumno);
      $insert_estudiantes->bindParam(':cohorte', $cohorte);
      if ($bloqueo == 'N') {
        $insert_estudiantes->bindValue(':bloqueo', false, PDO::PARAM_BOOL);
      } else {
        $insert_estudiantes->bindValue(':bloqueo', true, PDO::PARAM_BOOL);
      }
      $insert_estudiantes->bindParam(':causal_bloqueo', $causal_bloqueo);
      $insert_estudiantes->bindParam(':id_plan', $id_plan);
      $insert_estudiantes->bindParam(':run', $run);
      $insert_estudiantes->bindParam(':logro', $logro);
      $insert_estudiantes->bindParam(':fecha_logro', $fecha_logro);
      $insert_estudiantes->bindParam(':ult_carga', $ult_carga);
      $db->beginTransaction();
      $insert_estudiantes->execute();
      $db->commit();

    } 
  }

  fclose($archivo_estudiantes);

  












?>