<?php

function addPadding($data) {
    $block_size = 16;
    $pad = $block_size - (strlen($data) % $block_size);
    return $data . str_repeat(chr($pad), $pad);
}

function encrypt($data) {    
    $key = openssl_random_pseudo_bytes(16);
    $iv = openssl_random_pseudo_bytes(16);
    
    $dataWithPadding = addPadding(json_encode($data));
    $encrypted_data = openssl_encrypt($dataWithPadding, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
    $dataGPG = [
        "k" => base64_encode($key),
        "iv" => base64_encode($iv),
        "resultado" => base64_encode($encrypted_data)
    ];
    
    $gnupg = new gnupg();
    $gnupg->seterrormode(gnupg::ERROR_EXCEPTION);
    $gnupg->addencryptkey("nicolas.ortiz@pucpr.edu.br");
    
    $encryptedGPG = $gnupg->encrypt(json_encode($dataGPG));
    
    if (empty($encryptedGPG)) {
        throw new Exception("Falha na criptografia GPG");
    }
    
    return ['cript' => $encryptedGPG];
}

function enviaDados($url,$data){
    $data = encrypt($data);
    $data = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $resposta = curl_exec($ch);
    curl_close($ch);


    if ($resposta === null) {
        throw new Exception("Erro ao executar o curl");
    }
        
    return $resposta;
}
?>