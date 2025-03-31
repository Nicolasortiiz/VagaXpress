<?php
$pubkey = shell_exec("python3 publica.py");

if($pubkey){
    echo json_encode(["pubkey" => htmlspecialchars($pubkey)]);
}else{
    echo json_encode(["pubkey" => false]);
}
?>