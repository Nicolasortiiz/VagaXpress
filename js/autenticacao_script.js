let chavePublica;

window.onload = function () {
    fetch("../php/chave_pub.php", {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.pubkey) {
                chavePublica = data.pubkey;
            } else {
                window.alert("Ocorreu um erro, reinicie a página!")
            }
        })
        .catch(error => console.error(error));
};

async function criptografar(dados) {
    var k = CryptoJS.lib.WordArray.random(16);
    var iv = CryptoJS.lib.WordArray.random(16);
    var resultado = CryptoJS.AES.encrypt(JSON.stringify(dados), k, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    }).toString();

    var data = {
        k: k.toString(CryptoJS.enc.Base64),
        iv: iv.toString(CryptoJS.enc.Base64),
        resultado: resultado
    };
    var dataString = JSON.stringify(data);

    const publicKey = await openpgp.readKey({ armoredKey: chavePublica });
    const message = await openpgp.createMessage({ text: dataString });
    const res = await openpgp.encrypt({
        message: message,
        encryptionKeys: publicKey
    });
    return res;
}

function enviarLogin() {
    document.getElementById("botaoLogin").disabled = true;
    let email = /^[A-z0-9\.]+@[a-z]+\.com[a-z\.]{0,3}$/;
    let senha = /^.{7,20}$/;

    var verificadorEmail = email.test(document.getElementById('email').value);
    var verificadorSenha = senha.test(document.getElementById('senha').value);

    if (document.getElementById("email").value != "" &&
        document.getElementById("senha").value != "") {

        if ((verificadorEmail && verificadorSenha) || document.getElementById("email").value == "teste") {
            enviarDados();
        } else {
            alert("Erro no login, credenciais incorretos!");
        }
    } else {
        alert("Preencha todos os campos.");
    }

    document.getElementById('senha').value = '';
    document.getElementById("botaoLogin").disabled = false;

}

async function enviarDados() {
    var dados = {
        email: document.getElementById('email').value,
        senha: CryptoJS.SHA256(document.getElementById('senha').value).toString()
    };

    var res = await criptografar(dados);

    fetch("/php/login.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cript: res
        })
    })
        .then(response => response.json())
        .then(data => {
            // Tirar usuário teste
            if (dados.email == "teste") {
                validarLogin();
            } else {
                if (!data.success) {
                    alert("Erro no login, credenciais incorretos!");
                    document.getElementById('senha').value = '';
                    document.getElementById("botaoLogin").disabled = false;

                } else {
                    document.querySelector(".input_box").style.display = 'none';
                    document.querySelector(".divSMS").style.display = 'flex';
                }
            }
        })
        .catch(error => console.error(error));

}

async function validarLogin() {
    document.getElementById("botaoSMS").disabled = true;
    var dados = {
        data: new Date().toISOString(),
        email: document.getElementById('email').value,
        input: document.getElementById('inputSMS').value
    };

    var res = await criptografar(dados);
    fetch("/php/valida_otp.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cript: res
        })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert("Código incorreto!");

            } else {
                document.querySelectorAll('input').forEach(input => {
                    input.value = ''; 
                });
                location.href = "../index.html";
            }
        })
        .catch(error => console.error(error));

    document.getElementById("botaoSMS").disabled = false;
}