<?php

function removePadding($data) {

    $pad = ord($data[strlen($data) - 1]);
    if ($pad < 1 || $pad > 16) {
        return $data; 
    }
    if (strspn($data, chr($pad), strlen($data) - $pad) != $pad) {
        return $data;
    }

    return substr($data, 0, -$pad);
}

function decrypt($data){   

    $senhaGPG = "senha@gpg";
    $tempDir = sys_get_temp_dir();

    if (empty($data)) {
        throw new Exception("Dados criptografados não fornecidos");
    }

    $data = json_decode($data, true);
    $data = $data['cript'];

    $arqTemp = tempnam($tempDir, 'pgp_');
    if ($arqTemp === false) {
        throw new Exception("Falha ao criar arquivo temporário");
    }

    if (file_put_contents($arqTemp, $data) === false) {
        throw new Exception("Falha ao escrever arquivo temporário");
    }

    $comando = sprintf(
        'gpg --batch --quiet --yes --pinentry-mode loopback --passphrase %s --decrypt %s 2>&1',
        escapeshellarg($senhaGPG),escapeshellarg($arqTemp)
    );
    
    $decryptedPub = shell_exec($comando);
    
    unlink($arqTemp);

    if (empty($decryptedPub)) {
        throw new Exception("Falha na descriptografia GPG");
    }

    $decryptedPub = json_decode($decryptedPub, true);

    if (!isset($decryptedPub["k"], $decryptedPub["iv"], $decryptedPub["resultado"])) {
        echo json_encode(["erro" => true]);
        exit;
    }

    $key = base64_decode($decryptedPub["k"]);
    $iv = base64_decode($decryptedPub["iv"]);
    $encrypted_data = base64_decode($decryptedPub["resultado"]);

    $decrypted = openssl_decrypt($encrypted_data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
    
    
    if ($decrypted === false) {
        echo json_encode(["erro" => true]);
    } else {
        $decrypted = removePadding($decrypted);
        $decrypted = json_decode($decrypted, true);
        return $decrypted;
    }
}

?>