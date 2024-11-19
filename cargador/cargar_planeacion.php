<?php
  include('../config/conexion.php');
  require('funciones.php');
  require_once('crear_tablas.php');

  //leer planeacion 
  $archivo_planeacion = fopen("../datos/planeacion2.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_planeacion);

  //crear tabla planeacion
  try {
    $db->beginTransaction();
    $query = "CREATE TABLE IF NOT EXISTS planeacion (
      id_planeacion SERIAL PRIMARY KEY,
      periodo varchar(8) NOT NULL,
      sede varchar(100) NOT NULL,
      facultad varchar(100) NOT NULL REFERENCES facultades(facultad),
      id_depto INT NOT NULL REFERENCES departamentos(id_depto),
      id_asignatura varchar(12) NOT NULL REFERENCES asignaturas(id_asignatura),
      seccion varchar(4) NOT NULL,
      duracion varchar(4) NOT NULL CHECK (duracion IN ('S', 'I')),
      jornada varchar(4) NOT NULL CHECK (jornada IN ('D', 'V')),
      cupo INT NOT NULL,
      inscritos INT NOT NULL,
      dia varchar(1) NOT NULL CHECK (dia IN ('L', 'M', 'W', 'J', 'V', 'S')),
      hora_inicio varchar(8) NOT NULL,
      hora_fin varchar(8) NOT NULL,
      fecha_inicio varchar(10) NOT NULL,
      fecha_fin varchar(10) NOT NULL,
      lugar varchar(100) NOT NULL,
      edificio varchar(100) NOT NULL,
      profesor varchar(100) NOT NULL,
      run_profesor varchar(30) NOT NULL
    )";
    $db->exec($query);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  $insert_planeacion = $db->prepare('INSERT INTO planeacion (periodo, sede, facultad, id_depto, id_asignatura, seccion, duracion, jornada, cupo, inscritos, dia, hora_inicio, hora_fin, fecha_inicio, fecha_fin, lugar, edificio, profesor, run_profesor) VALUES (:periodo, :sede, :facultad, :id_depto, :id_asignatura, :seccion, :duracion, :jornada, :cupo, :inscritos, :dia, :hora_inicio, :hora_fin, :fecha_inicio, :fecha_fin, :lugar, :edificio, :profesor, :run_profesor) ON CONFLICT DO NOTHING');

  while (($datos = fgetcsv($archivo_planeacion, 0, ";")) == true) {
    $periodo = $datos[0];
    $sede = $datos[1];
    $facultad = $datos[2];
    $id_depto = $datos[3];
    $depto = $datos[4];
    $id_asignatura = $datos[5];
    $asignatura = $datos[6];
    $seccion = $datos[7];
    $duracion = $datos[8];
    $jornada = $datos[9];
    if ($jornada == "Diurno") {
      $jornada = "D";
    } else if ($jornada == "Vespertino") {
      $jornada = "V";
    }
    $cupo = $datos[10];
    $inscritos = $datos[11];
    $dia = $datos[12];
    if ($dia == "lunes") {
      $dia = "L";
    } else if ($dia == "martes") {
      $dia = "M";
    } else if ($dia == "miércoles") {
      $dia = "W";
    } else if ($dia == "jueves") {
      $dia = "J";
    } else if ($dia == "viernes") {
      $dia = "V";
    } else if ($dia == "sábado") {
      $dia = "S";
    }
    $hora_inicio = $datos[13];
    $hora_fin = $datos[14];
    $fecha_inicio = $datos[15];
    $fecha_fin = $datos[16];
    $lugar = $datos[17];
    $edificio = $datos[18];
    $profesor = $datos[19];
    $run_profesor = $datos[20];

    if ($periodo == null || $periodo == '') {
      continue;
    }

    $db->beginTransaction();
    $insert_planeacion->bindParam(':periodo', $periodo);
    $insert_planeacion->bindParam(':sede', $sede);
    $insert_planeacion->bindParam(':facultad', $facultad);
    $insert_planeacion->bindParam(':id_depto', $id_depto);
    $insert_planeacion->bindParam(':id_asignatura', $id_asignatura);
    $insert_planeacion->bindParam(':seccion', $seccion);
    $insert_planeacion->bindParam(':duracion', $duracion);
    $insert_planeacion->bindParam(':jornada', $jornada);
    $insert_planeacion->bindParam(':cupo', $cupo);
    $insert_planeacion->bindParam(':inscritos', $inscritos);
    $insert_planeacion->bindParam(':dia', $dia);
    $insert_planeacion->bindParam(':hora_inicio', $hora_inicio);
    $insert_planeacion->bindParam(':hora_fin', $hora_fin);
    $insert_planeacion->bindParam(':fecha_inicio', $fecha_inicio);
    $insert_planeacion->bindParam(':fecha_fin', $fecha_fin);
    $insert_planeacion->bindParam(':lugar', $lugar);
    $insert_planeacion->bindParam(':edificio', $edificio);
    $insert_planeacion->bindParam(':profesor', $profesor);
    $insert_planeacion->bindParam(':run_profesor', $run_profesor);
    $insert_planeacion->execute();
    $db->commit();



  }

  fclose($archivo_planeacion);

  //tabla control de actualizacion
  try {
    $db->beginTransaction();
    $query_control = "CREATE TABLE IF NOT EXISTS control_actualizacion (
      id serial PRIMARY KEY,
      actualizado BOOLEAN DEFAULT FALSE
    )";
    $db->exec($query_control);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //crear el stored procedure para generar vista
  try {
    $db->beginTransaction();
    $query_vista = "CREATE OR REPLACE PROCEDURE generar_vista()
        LANGUAGE plpgsql AS $$
        BEGIN
          CREATE OR REPLACE VIEW vista_acta AS
          SELECT a.n_alumno, p.nombre, a.id_asignatura, a.periodo, a.nota
          FROM acta a
          JOIN personas p ON a.run = p.run;
        END;
        $$";
    $db->exec($query_vista);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //crear trigger para actualizar calificaciones
  try {
    $db->beginTransaction();
    $query_trigger = "CREATE OR REPLACE FUNCTION calcular_calificacion()
        RETURNS TRIGGER AS $$
        BEGIN
          IF NEW.nota IS NOT NULL THEN
            IF NEW.nota >= 6.6 THEN
              NEW.calificacion = 'SO';
            ELSIF NEW.nota >= 6.0 THEN
              NEW.calificacion = 'MB';
            ELSIF NEW.nota >= 5.0 THEN
              NEW.calificacion = 'B';
            ELSIf NEW.nota >= 4.0 THEN
              NEW.calificacion = 'SU';
            ELSIF NEW.nota >= 3.0 THEN
              NEW.calificacion = 'I';
            ELSIF NEW.nota >= 2.0 THEN
              NEW.calificacion = 'M';
            ELSE
              NEW.calificacion = 'MM';
            END IF;
            RETURN NEW;
          ELSE
            NEW.calificacion = 'P';
            RETURN NEW;
          END IF;

        END;
        $$ LANGUAGE plpgsql";
    $db->exec($query_trigger);

    $query_triggersin = "CREATE TRIGGER insertar_calificacion_1
        BEFORE INSERT ON notas
        FOR EACH ROW
        EXECUTE FUNCTION calcular_calificacion()";
    $db->exec($query_triggersin);


    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  //crear trigger para actualizar notas
  try {
    $db->beginTransaction();
    $query_trigger = "CREATE TRIGGER insertar_calificacion
        BEFORE INSERT ON notas
        FOR EACH ROW
        EXECUTE FUNCTION calcular_calificacion()";
    $db->exec($query_trigger);
    $db->commit();
  } catch (Exception $e) {
    $db->rollBack();
    echo "Failed: " . $e->getMessage();
  }
?>