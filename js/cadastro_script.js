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

async function cadastrar(event) {
    event.preventDefault();
    document.getElementById("botaoCadastrar").disabled = true;

    if (document.getElementById("email").value != "" &&
        document.getElementById("username").value != "" &&
        document.getElementById("senha").value != "" &&
        document.getElementById("confirmar_senha").value != "") {

        if (document.getElementById("senha").value == document.getElementById("confirmar_senha").value) {

            let email = /^[A-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
            let senha = /^.{7,20}$/;

            var verificadorEmail = email.test(document.getElementById('email').value);
            var verificadorSenha = senha.test(document.getElementById('senha').value);

            if (verificadorEmail && verificadorSenha) {

                var dados = {
                    email: document.getElementById('email').value
                };

                var res = await criptografar(dados);

                fetch("../php/confirmar_email.php", {
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
                            alert("Email já cadastrado!");
                            
                        } else {
                            document.querySelector(".input_box").style.pointerEvents = 'none';
                            document.querySelector(".input_box").style.display = 'none';
                            document.querySelector(".divToken").style.display = 'flex';
                            enviarVerificacao();
                        }

                    })
                    .catch(error => console.error(error));

            }
            else {
                alert("Os dados registrados não estão de acordo com a expressão regular.")
            }
        }
        else {
            alert("As duas senhas não são iguais.");
        }
    }
    else {
        alert("Preencha todos os campos.");
    }
    document.getElementById("botaoCadastrar").disabled = false;
};

async function enviarVerificacao() {
    var dados = {
        email: document.getElementById("email").value,
        nome: document.getElementById("username").value
    };

    var res = await criptografar(dados);
    fetch("/php/enviar_email.php", {
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
                alert("Ocorreu um erro, reinicie a página!");
            }

        })
        .catch(error => console.error(error));
}
async function cadastrarConta() {
    var dados = {
        email: document.getElementById('email').value,
        nome: document.getElementById("username").value,
        senha: CryptoJS.SHA256(document.getElementById("senha").value).toString()
    };

    var res = await criptografar(dados);

    fetch("../php/cadastro.php", {
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
            if (data.error == 1) {
                alert("Conta já cadastrada!")
            } else if (data.error == 2) {
                alert("Ocorreu um erro, reinicie a página!");
            } else if (data.error) {
                alert("Ocorreu um erro, reinicie a página!");
            }
            if (data.success) {
                window.location.href = "/login.html";
            }

        })
        .catch(error => console.error(error));


}

async function verificaToken() {
    document.getElementById("botaoToken").disabled = true;
    if (document.getElementById('token').value != "") {
        var dados = {
            token: document.getElementById('token').value
        };
        var res = await criptografar(dados);
        fetch("../php/verifica_token.php", {
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
                    alert("Ocorreu um erro, reinicie a página!");
                }
                if (data.status == 1) {
                    cadastrarConta();
                    
                } else {
                    alert("Token inválido");
                }
            })
            .catch(error => console.error(error));
    } else {
        alert("Preencha todos os campos.");
    }
    document.getElementById("botaoToken").disabled = false;
}

