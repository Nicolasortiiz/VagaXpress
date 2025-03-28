<?php
function decryptAES($cipherData, $iv, $chave){
    $cipherData = base64_decode($cipherData);
    $iv = base64_decode($iv);
    $chave = base64_decode($chave);
    $decryptedData = openssl_decrypt($cipherData, 'AES-256-CBC', $chave, OPENSSL_RAW_DATA, $iv);
    return $decryptedData;
}
?>