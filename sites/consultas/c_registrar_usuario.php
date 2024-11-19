<?php

  session_start();
  include("../../config/conexion.php");

  if (!isset($_SESSION['user']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../index.php');
    exit();
  }

  $run = $_POST['run'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $query = "INSERT INTO usuarios (run, email, password_hash, rol) VALUES (:run, :email, :password_hash, 'user')";
  $stmt = $db->prepare($query);
  $stmt->bindParam(':run', $run);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':password_hash', $password_hash);
  $stmt->execute();

  header('Location: ../admin.php');
?>

