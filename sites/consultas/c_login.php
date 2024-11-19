<?php
  session_start();
  include("../../config/conexion.php");
  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = "SELECT * FROM usuarios WHERE email = :email";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = $user;
    $_SESSION['rol'] = $user['rol'];

    if ($user['rol'] == 'admin') {
      header('Location: ../admin.php');
    } else {
      header('Location: ../user.php');
    }
  } else {
    echo "Mail o contraseña incorrectos";
  }
?>