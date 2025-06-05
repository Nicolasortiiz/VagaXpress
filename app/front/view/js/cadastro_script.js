let chavePublica;

window.onload = function () {
    fetch("/gateway.php/api/usuario?action=verificar_login_autenticacao")
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                if (data.pubkey) {
                    chavePublica = data.pubkey;
                } else {
                    window.alert("Ocorreu um erro, reinicie a página!")
                }
            } else if (data.login == 1) {
                window.alert(data.msg);
                window.location.href = "../index.html";
            } else if (data.login == 2) {
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

    var key = {
        k: k.toString(CryptoJS.enc.Base64),
        iv: iv.toString(CryptoJS.enc.Base64),
    };
    var keyString = JSON.stringify(key);

    const publicKey = await openpgp.readKey({ armoredKey: chavePublica });
    const message = await openpgp.createMessage({ text: keyString });
    const encryptedKey = await openpgp.encrypt({
        message: message,
        encryptionKeys: publicKey
    });
    
    const encryptedData = {
        key: encryptedKey,
        data: resultado
    };
    return encryptedData;
}


async function cadastrar(event) {
    event.preventDefault();
    document.getElementById("botaoCadastrar").disabled = true;

    if (document.getElementById("email").value != "" &&
        document.getElementById("username").value != "" &&
        document.getElementById("senha").value != "" &&
        document.getElementById("confirmar_senha").value != "") {

        if (document.getElementById("senha").value == document.getElementById("confirmar_senha").value) {

            let email = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            let senha = /^(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*\d)(?=.*[A-Z])(?=.*[a-z]).{8,}$/;
            let nome = /^[\p{L}]{3,}$/u;

            var verificadorEmail = email.test(document.getElementById('email').value);
            var verificadorSenha = senha.test(document.getElementById('senha').value);
            var verificadorNome = nome.test(document.getElementById('username').value);

            if (verificadorEmail && verificadorSenha) {

                var dados = {
                    email: document.getElementById('email').value,
                    nome: document.getElementById("username").value
                };

                var res = await criptografar(dados);

                fetch("/gateway.php/api/usuario?action=encontrar_email", {
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
                            document.querySelector(".input_box").style.pointerEvents = 'none';
                            document.querySelector(".input_box").style.display = 'none';
                            document.querySelector(".divToken").style.display = 'flex';
                        }
                    })
                    .catch(error => console.error(error));
            } else if (!verificadorEmail) {
                alert("Insira um email valido.");
            } else if (!verificadorSenha) {
                alert("Insira uma senha com uma letras maiúsculas e minúsculas, um número e um caractere especial (tamanho minimo 8).");
            } else if (!verificadorNome) {
                alert("Nome de usuário apenas pode conter letras (tamnho minimo 3).");
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

async function verificaToken() {
    document.getElementById("botaoToken").disabled = true;
    let token = /^[0-9]{6,}$/;
    var verificadorToken = token.test(document.getElementById('token').value);
    if (!verificadorToken) {
        alert("Token inválido!");
        return;
    }
    var dados = {
        token: document.getElementById('token').value,
        email: document.getElementById('email').value,
        nome: document.getElementById("username").value,
        senha: CryptoJS.SHA256(document.getElementById("senha").value).toString()
    };

    var res = await criptografar(dados);

    fetch("/gateway.php/api/usuario?action=cadastro", {
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
                window.location.href = "login.html";
            }
        })
        .catch(error => console.error(error));

    document.getElementById("botaoToken").disabled = false;
}

