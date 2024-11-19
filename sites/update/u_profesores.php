<?php
  include("../config/conexion.php");

  try {
    //verif actualizacion en db personal
    $stmt = $db->query("SELECT actualizado FROM control_actualizacion LIMIT 1;");
    $actualizado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$actualizado || !$actualizado['actualizado']) {
      $db->beginTransaction();
      $stmt = $db->query("SELECT * FROM profesores;");
      $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $update = $db->prepare("UPDATE personas SET run=:run, dv=:dv, nombre=:nombre, estamento='academico' WHERE run=:run;");
      $update_mail = $db->prepare("UPDATE mails SET mail_inst=:mail mail_pers=:mailp WHERE run=:run;");

      foreach ($profesores as $profesor) {
        $run = $profesor['run'];
        $dv = 0;
        $nombre = $profesor['nombre'] . ' ' . $profesor['apellido1'] . ' ' . $profesor['apellido2'];
        $update->bindParam(':run', $run);
        $update->bindParam(':dv', $dv);
        $update->bindParam(':nombre', $nombre);
        $update->execute();

        $update_mail->bindParam(':run', $run);
        $update_mail->bindParam(':mail', $profesor['email_institucional']);
        $update_mail->bindParam(':mailp', $profesor['email_personal']);
        $update_mail->execute();

      }
      $db->commit();

      if (!$actualizado) {
        $stmt = $db->prepare("INSERT INTO control_actualizacion (actualizado) VALUES (true);");
        $stmt->execute();
      } else {
        $stmt = $db->prepare("UPDATE control_actualizacion SET actualizado=true;");
        $stmt->execute();
      }

    } else {
      echo "La base de datos ya se actualizó";
    }

  } catch (Exception $e) {
    echo $e->getMessage();
  }