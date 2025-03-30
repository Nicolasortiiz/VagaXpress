<?php

require_once "session.php";
require_once "decrypt.php";
$email = $arr['dado1'];
$senha = $arr['dado2'];

$result = mysqli_query($con, "UPDATE usuario SET senha_usuario = '$senha' WHERE email_usuario = '$email'");

mysqli_close($con);
