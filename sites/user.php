<?php
  session_start();

  if (!isset($_SESSION['user']) || $_SESSION['rol'] != 'user') {
    header('Location: index.php');
    exit();
  }

  include("templates/header.html");
?>

<body>
  <div class="style">
    <h1 class="title">Menú de Usuario</h1>
    <p class="description">Bienvenid@ <?php echo $_SESSION['user']['email']; ?></p>

    <p class="prompt">Reporte, cantidad de estudiantes dentro y fuera de nivel</p>
    <form class="search-form" method="POST" action="consultas/c_reporte_estudiantes.php">
      <input type="submit" class="form-button" value="Generar Reporte">
    </form>

    <p class="prompt">Reporte, porcentaje de aprobación por periodo</p>
    <form class="search-form" method="POST" action="consultas/c_reporte_aprobacion.php">
      <input class="form-input" type="text" name="periodo" id="periodo" placeholder="????-??">
      <input type="submit" class="form-button" value="Generar Reporte">
    </form>

    <p class="prompt">Reporte, promedio porcentaje de aprobación histórico. Ingrese curso:</p>
    <form class="search-form" method="POST" action="consultas/c_reporte_promedio_aprobacion.php">
      <input class="form-input type="text" name="curso" id="curso" placeholder="Código Curso">
      <input type="submit" class="form-button" value="Generar Reporte">
    </form>

    <p class="prompt">Reporte, proyección de cursos 2025 para estudiante</p>
    <form class="search-form" method="POST" action="consultas/c_reporte_proyeccion.php">
      <input class="form-input" type="number" name="n_estudiante" id="n_estudiante" placeholder="Número de Estudiante">
      <input type="submit" class="form-button" value="Generar Reporte">
    </form>

    <p class="prompt">Reporte, historial académico de estudiante</p>
    <form class="search-form" method="POST" action="consultas/c_reporte_historial.php">
      <input class="form-input" type="number" name="n_estudiante" id="n_estudiante" placeholder="Número de Estudiante">
      <input type="submit" class="form-button" value="Generar Reporte">
    </form>
  
    <p class="prompt">Carga de notas desde archivo .CSV</p>
    <form class="search-form" method="POST" action="consultas/c_cargar_notas.php" enctype="multipart/form-data">
      <input class="form-input" type="file" name="file" id="file" accept=".csv" required>
      <input type="submit" class="form-button" value="Cargar Notas">
    </form>

    <p class="prompt">Consulta personalizada</p>
    <form class="search-form" method="POST" action="consultas/c_consulta_personalizada.php">
      <input class="form-input" type="text" name="atributos" id="atributos" placeholder="Atributo(s)">
      <input class="form-input" type="text" name="tabla" id="tabla" placeholder="Tabla">
      <input class="form-input" type="text" name="condicion" id="condicion" placeholder="Condición">
      <input type="submit" class="form-button" value="Consultar">
    </form>


    <form class="logout-form" method="POST" action="consultas/c_logout.php">
      <input type="submit" class="form-button" value="Cerrar Sesión">
    </form>
    
  </div>


</body>

</html>


