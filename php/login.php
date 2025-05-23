<?php
require_once "decrypt.php";
require_once "connector.php";

$dadosCriptografados = file_get_contents('php://input');
$resultado = decrypt($dadosCriptografados);

$email = $resultado["email"];
$senha = $resultado["senha"];
$email = htmlspecialchars(strip_tags($email));
$senha = htmlspecialchars(strip_tags($senha));

$query = "SELECT * FROM Usuario WHERE email = ? AND senha = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $email, $senha);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();
$conn->close();

if ($resultado->num_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}

?>