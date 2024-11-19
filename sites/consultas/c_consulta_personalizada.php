<?php include('../templates/header.html');   ?>

<body>

<h1 class="title">Consulta Personalizada</h1>

<?php
  require("../../config/conexion.php");
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $atributos = $_POST["atributos"];
    $tabla = $_POST["tabla"];
    $condicion = $_POST["condicion"];

    $tablas_prohibidas = ['usuarios', 'personas', 'mails', 'pg_shadow'];
    $operadores = ['=', '>', '<', '>=', '<=', '<>', 'LIKE'];
    $condiciones_prohibidas = ['DROP', ';', 'DELETE', 'INSERT', 'UPDATE', 'CREATE', 'ALTER', 'TRUNCATE', '--', 'SELECT', '#', '/*', '*/'];
    $ultra_prohibido = ";";

    try {
      if (in_array($tabla, $tablas_prohibidas)) {
        throw new PDOException("Tabla prohibida.");
      }
      if (in_array($condicion, $condiciones_prohibidas)) {
        throw new PDOException("Condición prohibida.");
      }
      if (in_array($atributos, $condiciones_prohibidas)) {
        throw new PDOException("Atributo prohibido.");
      }
      if (in_array($tabla, $tablas_prohibidas)) {
        throw new PDOException("Tabla prohibida.");
      }
      if (str_contains($condicion, $ultra_prohibido)) {
        throw new PDOException("Condición prohibida.");
      }
      if (str_contains($atributos, $ultra_prohibido)) {
        throw new PDOException("Atributo prohibido.");
      }
      if (str_contains($tabla, $ultra_prohibido)) {
        throw new PDOException("Tabla prohibida.");
      }


      $db->beginTransaction();
      $query = "SELECT $atributos FROM $tabla WHERE $condicion;";
      $stmt = $db->prepare($query);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $columnas = array_keys($result[0]);

      echo "<table class='styled-table'>";
      echo "<tr>";
      foreach ($columnas as $key) {
        echo "<th>$key</th>";
      }
      echo "</tr>";
      foreach ($result as $row) {
        echo "<tr>";
        foreach ($row as $value) {
          echo "<td>$value</td>";
        }
        echo "</tr>";
      }
      echo "</table>";


      $db->commit();
    } catch (PDOException $e) {
      $db->rollBack();
      $error_message = $e->getMessage();

      if (str_contains($error_message, "42703")) {
        echo "<p style='color:red;'>Error: Columna no existe.</p>";
      } else if (str_contains($error_message, "42P01")) {
        echo "<p style='color:red;'>Error: Tabla no existe.</p>";
      } else if (str_contains($error_message, '42601')) {
        echo "<p style='color:red;'>Error: Sintaxis de la consulta incorrecta.</p>";
      } else {
        echo "<p style='color:red;'>Error: $error_message</p>";
      }
    }

  }
?>