<?php
header('Content-Type: application/json');
$pubkey = shell_exec("gpg --armor --export");

if($pubkey){
    echo json_encode(["pubkey" => htmlspecialchars($pubkey)]);
}else{
    echo json_encode(["pubkey" => false]);
}
?>