let chavePublica;

window.onload = function () {
    fetch("/api/chave_publica.php")
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
                    email: document.getElementById('email').value,
                    nome: document.getElementById("username").value
                };

                var res = await criptografar(dados);

                fetch("/api/usuario.php?action=encontrar_email", {
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

async function verificaToken() {
    document.getElementById("botaoToken").disabled = true;
    var dados = {
        token: document.getElementById('token').value,
        email: document.getElementById('email').value,
        nome: document.getElementById("username").value,
        senha: CryptoJS.SHA256(document.getElementById("senha").value).toString()
    };

    var res = await criptografar(dados);

    fetch("/api/usuario.php?action=cadastro", {
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
            } else{
                document.querySelectorAll('input').forEach(input => {
                    input.value = ''; 
                });
                window.location.href = "login.html";
            }
        })
        .catch(error => console.error(error));

    document.getElementById("botaoToken").disabled = false;
}

