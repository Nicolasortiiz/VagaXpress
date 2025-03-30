<?php
require_once("connector.php");

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $email = htmlspecialchars(strip_tags($email));
    $senha = htmlspecialchars(strip_tags($senha));

    $query = "SELECT * FROM Usuario WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $stmt->close();
    $conn-> close();
    $resultado = $stmt->get_result();
    if($resultado->num_rows > 0){
        echo json_encode(["resultado" => true]);
    }else{
        echo json_encode(["resultado" => false]);
    }
}
?>