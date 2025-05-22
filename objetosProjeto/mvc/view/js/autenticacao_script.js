let chavePublica;

window.onload = function () {
    fetch("/api/usuario.php?action=verificar_login_autenticacao")
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                if (data.pubkey) {
                    chavePublica = data.pubkey;
                } else {
                    window.alert("Ocorreu um erro, reinicie a página!")
                }
            }else if(data.login == 1){
                window.alert(data.msg);
                window.location.href = "../index.html";
            }else if(data.login == 2){
                window.alert(data.msg);
                window.location.href = "administracao.html";
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
    let senha = /^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d)(?=.*[A-Z])(?=.*[a-z]).{8,}$/;

    var verificadorEmail = email.test(document.getElementById('email').value);
    var verificadorSenha = senha.test(document.getElementById('senha').value);

    if (document.getElementById("email").value != "" &&
        document.getElementById("senha").value != "") {

        if ((verificadorEmail && verificadorSenha) || document.getElementById("email").value == "teste") {
            enviarDados();
        } else if (document.getElementById("email").value == "admin") { //Fazer autenticação de admin de verdade eventualmente
            window.location.href = "administracao.html";
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

    fetch("/api/usuario.php?action=validar_conta", {
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
                if (data.error) {
                    alert(data.msg);
                } else {
                    document.querySelector(".input_box").style.display = 'none';
                    document.querySelector(".divSMS").style.display = 'flex';
                }
            }
        })
        .catch(error => console.error(error));
        
    document.getElementById('senha').value = '';
    document.getElementById("botaoLogin").disabled = false;
}

async function validarLogin() {
    document.getElementById("botaoSMS").disabled = true;
    var dados = {
        data: new Date().toISOString(),
        email: document.getElementById('email').value,
        token: document.getElementById('inputSMS').value
    };

    var res = await criptografar(dados);
    fetch("/api/usuario.php?action=validar_otp", {
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
            if (data.error) {
                alert(data.msg);
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