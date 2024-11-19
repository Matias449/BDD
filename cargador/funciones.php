<?php
function esCorreoValido($email) {
  // Filtro para validar el correo electrónico según el formato IETF RFC 3696
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

?>