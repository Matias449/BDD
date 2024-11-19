<!DOCTYPE html>
<html lang = "es">
<meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>

<?php
  include("update/u_profesores.php");
?>

<body>
    <div class="login">
      <h1 class="title">Bienvenido a Bananer</h1>
      <h2 class="title">Iniciar Sesión</h2>
      <form class="login-form" method = "POST" action = "consultas/c_login.php">
        <label class="form-label" for="email">Correo Institucional</label>
        <input class="form-input" type="email" name="email" id="email" required>
        <label class="form-label" for="password">Contraseña</label>
        <input class="form-input" type="password" name="password" id="password" required>

        <button class="form-button" type="submit">Iniciar Sesión</button>
      </form>
    </div>
</body>

</html>

