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
                //enviar para página adm
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
};

function enviaEmail() {
    document.getElementById("botaoEmail").disabled = true;
    let email = /^[A-z0-9\.]+@[a-z]+\.com[a-z\.]{0,3}$/;

    var verificadorEmail = email.test(document.getElementById('email').value);
    if (document.getElementById('email').value != "") {
        if (verificadorEmail) {
            verificaEmail();
        } else {
            alert("Email inválido!");
        }
    } else {
        alert("Preencha todos os campos.");
    }
    document.getElementById("botaoEmail").disabled = false;
};

function verificaSenha() {
    document.getElementById("botaoSenha").disabled = true;
    let senha = /^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d)(?=.*[A-Z])(?=.*[a-z]).{8,}$/;

    var verificadorSenha = senha.test(document.getElementById('senha').value);
    if (document.getElementById('senha').value != "" &&
        document.getElementById('confirmar_senha').value != "") {

        if (verificadorSenha) {

            if (document.getElementById('senha').value == document.getElementById('confirmar_senha').value) {

                document.querySelector(".divSenha").style.display = 'none';
                document.querySelector(".divToken").style.display = 'flex';

            } else {
                alert("As duas senhas não são iguais.");
                document.getElementById("botaoSenha").disabled = false;
            }
        } else {
            alert("Senha inválida!");
            document.getElementById("botaoSenha").disabled = false;
        }
    } else {
        alert("Preencha todos os campos.");
        document.getElementById("botaoSenha").disabled = false;
    }
}
async function verificaEmail() {

    var dados = {
        email: document.getElementById('email').value
    };

    var res = await criptografar(dados);

    fetch("/api/usuario.php?action=validar_email", {
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
                document.querySelector(".divEmail").style.display = 'none';
                document.querySelector(".divSenha").style.display = 'flex';
            }
        })
        .catch(error => console.error(error));
    document.getElementById("botaoEmail").disabled = false;
}

async function verificaToken() {
    document.getElementById("botaoToken").disabled = true;
    if (document.getElementById('token').value != "") {
        var dados = {
            token: document.getElementById('token').value,
            senha: CryptoJS.SHA256(document.getElementById("senha").value).toString(),
            email: document.getElementById('email').value
        };

        var res = await criptografar(dados);

        fetch("/api/usuario.php?action=validar_token", {
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
                }else{
                    window.location.href = "login.html";
                }
            })
            .catch(error => console.error(error));
    } else {
        alert("Preencha todos os campos.");
    }
    document.getElementById("botaoToken").disabled = false;
}