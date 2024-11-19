<?php
  session_start();
  include("../config/conexion.php");
  if (!isset($_SESSION['user']) || $_SESSION['rol'] != 'admin') {
    header('Location: index.php');
    exit();
  }

  $run = $_GET['run'];
  $query_mail = "SELECT mail_inst FROM mails WHERE run = :run";
  $stmt_mail = $db->prepare($query_mail);
  $stmt_mail->bindParam(':run', $run);
  $stmt_mail->execute();
  $mail = $stmt_mail->fetch();
  $email = $mail['mail_inst'];
  
  include("templates/header.html");
?>

<body>
  <div class="admin">
    <h1 class="title">Registro de Usuarios</h1>
    <form class="search-form" method="POST" action="consultas/c_registrar_usuario.php">
      <label class="form-label for="run">RUN</label>
      <input class="form-input" type="text" name="run" id="run" value="<?php echo $run; ?>" readonly>

      <label class="form-label" for="email">Correo Institucional</label>
      <?php if ($email) { ?>
        <input class="form-input" type="email" name="email" id="email" value="<?php echo $email; ?>" readonly>
      <?php } else { ?>
        <input class="form-input" type="email" name="email" id="email" required>
      <?php } ?>
      

      <label class="form-label" for="password">Contraseña</label>
      <input class="form-input" type="password" name="password" id="password" required>

      <input type="submit" class="form-button" value="Registrar Usuario">
    </form>
  </div>
</body>
</html>

