<?php
require_once "decrypt.php";
require_once "connector.php";
session_start();
$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);
if (!isset($resultado['email'], $resultado['senha'])) {
    echo json_encode(['success'=> false]);
    exit();
}
$email = $resultado['email'];
$senha = $resultado['senha'];
$segredo = $_SESSION['qr'];

$query = 'UPDATE Usuario SET senha = ?, segredo = ? WHERE email = ?';
$stmt = $conn->prepare($query);
$stmt->bind_param('sss',  $senha,$segredo, $email);

if($stmt->execute()){
    echo json_encode(['success'=> true]);
}else{
    echo json_encode(['success'=> false]);
}

unset($_SESSION['qr']);

$stmt-> close();
$conn-> close();
?>