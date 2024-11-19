<?php
  
  include('../config/conexion.php');
  require('funciones.php');

  //leer profesores
  $archivo_profesores = fopen("../datos/docentes_planificados.csv", "r");
  //saltar la primera linea
  fgetcsv($archivo_profesores);

  $insert_profesores = $db->prepare('INSERT INTO profesores (run, dedicacion, contrato, diurno, vespertino, sede, carrera, grado, jerarquia, cargo) VALUES (:run, :dedicacion, :contrato, :diurno, :vespertino, :sede, :carrera, :grado, :jerarquia, :cargo) ON CONFLICT DO NOTHING');
  $insert_administrativos = $db->prepare('INSERT INTO administrativos (run, dedicacion, contrato, cargo) VALUES (:run, :dedicacion, :contrato, :cargo) ON CONFLICT DO NOTHING');
  $insert_mail = $db->prepare('INSERT INTO mails (mail_inst, mail_pers, run) VALUES (:mail_inst, :mail_pers, :run) ON CONFLICT DO NOTHING');
  $insert_personas = $db->prepare('INSERT INTO personas (run, dv, nombre, estamento) VALUES (:run, :dv, :nombre, :estamento) ON CONFLICT DO NOTHING');

  while (($datos = fgetcsv($archivo_profesores, 0, ";")) == true) {
    $run = $datos[0];
    $nombre = $datos[1];
    $apellido_p = $datos[2];
    $telefono = $datos[3];
    $mail_personal = $datos[4];
    $mail_institucional = $datos[5];
    $dedicacion = $datos[6];
    $contrato = $datos[7];
    $diurno = $datos[8];
    $vespertino = $datos[9];
    $sede = $datos[10];
    $carrera = $datos[11];
    $grado = $datos[12];
    $jerarquia = $datos[13];
    $cargo = $datos[14];
    $estamento = $datos[15];

    if ($run == null) {
      continue;
    } else if ($nombre == null) {
      continue;
    } else if ($mail_personal == null || $mail_institucional == null || !esCorreoValido($mail_personal) || !esCorreoValido($mail_institucional)) {
      if (str_contains($mail_personal, ' ')) {
        $mail_personal = str_replace(' ', '', $mail_personal);
      } else if (str_contains($mail_institucional, ' ')) {
        $mail_institucional = str_replace(' ', '', $mail_institucional);
      }
    } else if (!is_numeric($dedicacion)) {
      if ($dedicacion == 0){
        $dedicacion = 0;
      } else if ($dedicacion == null) {
        $dedicacion = 0;
      }
    } else if (is_numeric($dedicacion) && ($dedicacion < 0 || $dedicacion > 40)) {
      $dedicacion = 0;
    } else if ($contrato != "HONORARIO" && $contrato != "FULL TIME" && $contrato != "PART TIME") {
      $contrato = "NO DEFINIDO";
    } else if ($diurno != "diurno" && $diurno != null) {
      ;
    } else if ($vespertino != "vespertino" && $vespertino != null) {
      ;
    } else if ($grado != "LICENCIATURA" && $grado != "MAGISTER" && $grado != "DOCTORADO") {
      if ($grado == 0) {
        $grado = "SIN GRADO";
      } else if ($grado == null) {
        $grado = "SIN GRADO";
      }
    } 
    if ($estamento == "Administrativo" || $estamento == "administrativo") {
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindValue(':dv', '0');
      $insert_personas->bindParam(':nombre', $nombre);
      $insert_personas->bindValue(':estamento', 'administrativo');
      $db->beginTransaction();
      $insert_personas->execute();
      $db->commit();

      $insert_administrativos->bindParam(':run', $run);
      $insert_administrativos->bindParam(':dedicacion', $dedicacion);
      $insert_administrativos->bindParam(':contrato', $contrato);
      $insert_administrativos->bindParam(':cargo', $cargo);
      $db->beginTransaction();
      $insert_administrativos->execute();
      $db->commit();

      $insert_mail->bindParam(':mail_inst', $mail_institucional);
      $insert_mail->bindParam(':mail_pers', $mail_personal);
      $insert_mail->bindParam(':run', $run);
      $db->beginTransaction();
      $insert_mail->execute();
      $db->commit();

    } else if ($estamento == "Académico" || strpos($jerarquia, "DOCENTE")) {
      $insert_personas->bindParam(':run', $run);
      $insert_personas->bindValue(':dv', '0');
      $insert_personas->bindParam(':nombre', $nombre);
      $insert_personas->bindValue(':estamento', 'academico');
      $db->beginTransaction();
      $insert_personas->execute();
      $db->commit();

      $insert_profesores->bindParam(':run', $run);
      $insert_profesores->bindParam(':dedicacion', $dedicacion);
      $insert_profesores->bindParam(':contrato', $contrato);
      if ($diurno == 'diurno') {
        $insert_profesores->bindValue(':diurno', true, PDO::PARAM_BOOL);
      } else if ($diurno == 'A VECES') {
        continue;
      } else {
        $insert_profesores->bindValue(':diurno', false, PDO::PARAM_BOOL);
      }
      if ($vespertino == 'vespertino') {
        $insert_profesores->bindValue(':vespertino', true, PDO::PARAM_BOOL);
      } else {
        $insert_profesores->bindValue(':vespertino', false, PDO::PARAM_BOOL);
      }
      $insert_profesores->bindParam(':sede', $sede);
      $insert_profesores->bindParam(':carrera', $carrera);
      if ($carrera == "MAGIA") {
        $carrera = "Magia";
      } else if ($carrera == 'HECHICERÍA'){
        $carrera = "Hechicería";
      }
      $insert_profesores->bindParam(':grado', $grado);
      $insert_profesores->bindParam(':jerarquia', $jerarquia);
      $insert_profesores->bindParam(':cargo', $cargo);
      $db->beginTransaction();
      $insert_profesores->execute();
      $db->commit();
      
      $insert_mail->bindParam(':mail_inst', $mail_institucional);
      $insert_mail->bindParam(':mail_pers', $mail_personal);
      $insert_mail->bindParam(':run', $run);
      $db->beginTransaction();
      $insert_mail->execute();
      $db->commit();
    }
  }


  fclose($archivo_profesores);

?>