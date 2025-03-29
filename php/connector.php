<?php
$servername = "127.0.0.1:3306";  
$username = "root";        
$password = "senhaBD";     
$dbname = "estacionamentoDB"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
