<?php
  require("../../config/conexion.php");

  $run = $_POST['run'];

  $query = "SELECT * FROM personas WHERE run = :run";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':run', $run);
  $stmt->execute();
  $user = $stmt->fetch();

  if ($user) {
    $estamento = $user['estamento'];
    if ($estamento == 'academico' || $estamento == 'administrativo') {
      header('Location: ../registro_usuario.php?run=' . $run);
    } else {
      echo "El usuario no se puede registrar";
    }
  } else {
    echo "Usuario no encontrado";
  }
?>

      