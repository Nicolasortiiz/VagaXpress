<?php

function removePadding($data) {
    if (empty($data)) {
        return $data;
    }
    $length = strlen($data);
    $pad = ord($data[$length - 1]);
    
    if ($pad < 1 || $pad > 16) {
        return $data;
    }

    if ($length < $pad) {
        return $data;
    }

    $padding = substr($data, -$pad);
    $expectedPadding = str_repeat(chr($pad), $pad);
    
    if (hash_equals($padding, $expectedPadding)) {
        return substr($data, 0, $length - $pad);
    }
    
    return $data;
}

function decrypt($data){   

    $env = parse_ini_file(__DIR__ . '/../.env');
    $senhaGPG = $env['SENHA_GPG'];
    $tempDir = sys_get_temp_dir();
    
    if (empty($data)) {
        throw new Exception("Dados criptografados não fornecidos");
    }

    $data = json_decode($data, true);
    $data = $data['cript'];

    if (!isset($data['key']) || !isset($data['data'])) {
        throw new Exception("Dados criptografados inválidos");
    }

    $arqTemp = tempnam($tempDir, 'pgp_');
    if ($arqTemp === false) {
        throw new Exception("Falha ao criar arquivo temporário");
    }

    if (file_put_contents($arqTemp, $data['key']) === false) {
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

    if (!isset($decryptedPub["k"], $decryptedPub["iv"])) {
        echo json_encode(["erro" => true]);
        exit;
    }

    $key = base64_decode($decryptedPub["k"]);
    $iv = base64_decode($decryptedPub["iv"]);
    $encrypted_data = base64_decode($data["data"]);

    $decrypted = openssl_decrypt($encrypted_data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
    
    
    if ($decrypted === false) {
        echo json_encode(["erro" => true]);
    } else {
        $decrypted = removePadding($decrypted);
        $decrypted = preg_replace('/[^\x20-\x7E]/', '', $decrypted);
        $decrypted = json_decode($decrypted, true);
        
        return $decrypted;
    }
}

?>