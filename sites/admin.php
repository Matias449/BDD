<?php
  session_start();
  
  if (!isset($_SESSION['user']) || $_SESSION['rol'] != 'admin') {
    header('Location: index.php');
    exit();
  }

  include("templates/header.html");
?>

<body>
  <div class="admin">
    <h1 class="title">Menú de Administrador</h1>
    <h2 class="subtitle"> Registro de Usuarios</h2>
    <form class="search-form" method="POST" action="consultas/c_verificar_admin.php">
      <label class="form-label" for="run">RUN</label>
      <input class="form-input" type="number" name="run" id="run" required>
      <input type="submit" class="form-button" value="Buscar">
    </form>

    <form class="logout-form" method="POST" action="consultas/c_logout.php">
      <input type="submit" class="form-button" value="Cerrar Sesión">
    </form>
    
  </div>
</body>





    




