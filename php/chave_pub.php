<?php
$pubkey = shell_exec("python3 publica.py " . escapeshellarg("73613CFFF5D9EB2ABC8F66F7B0A5008C1950B989"));

if($pubkey){
    echo json_encode(["pubkey" => htmlspecialchars($pubkey)]);
}else{
    echo json_encode(["pubkey" => false]);
}
?>