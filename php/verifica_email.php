<?php
require_once "session.php";
require_once "decrypt.php";
$email = $arr['dado1'];
$arr = array("autenticacao" => 0);

$result = mysqli_query($con, "SELECT COUNT(*) FROM usuario WHERE email_usuario LIKE '$email'");

$row = mysqli_fetch_row($result);
if ($row[0] == 1) {
    $arr = array("autenticacao" => 1);
}

echo json_encode($arr);
mysqli_close($con);
