<?php

  //db normal
  try {
    require('data.php');
    
    $db = new PDO("pgsql:dbname=$databasename;host=localhost;port=5432;user=$user;password=$password");
  } catch (Exception $e) {
    echo 'No se pudo conectar a la base de datos: $e';
  }

  //db profesores
  try {
    require('data.php');
    $dbnombre = 'e3profesores';
    $dbprofesores = new PDO("pgsql:dbname=$dbnombre;host=localhost;port=5432;user=$user;password=$password");
  } catch (Exception $e) {
    echo 'No se pudo conectar a la base de datos: $e';
  }
    
?>