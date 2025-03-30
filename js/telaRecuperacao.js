function enviaEmail() {
    document.getElementById("botaoEmail").disabled = true;
    let email = /^[A-z0-9\.]+@[a-z]+\.com[a-z\.]{0,3}$/;

    var verificadorEmail = email.test(document.getElementById('email').value);
    if (document.getElementById('email').value != "") {
        if (verificadorEmail) {
            verificaEmail();
        } else {
            alert("Email inválido!");
            document.getElementById("botaoEmail").disabled = false;
        }
    } else {
        alert("Preencha todos os campos.");
        document.getElementById("botaoEmail").disabled = false;
    }
}

function verificaSenha() {
    document.getElementById("botaoSenha").disabled = true;
    let senha = /^.{7,20}$/;

    var verificadorSenha = senha.test(document.getElementById('senha').value);
    if (document.getElementById('senha').value != "" &&
        document.getElementById('confirmar_senha').value != "") {

        if (verificadorSenha) {

            if (document.getElementById('senha').value == document.getElementById('confirmar_senha').value) {

                document.querySelector(".divSenha").style.display = 'none';
                document.querySelector(".divToken").style.display = 'flex';
                enviarVerificacao();

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

function validarToken() {
    document.getElementById("botaoToken").disabled = true;
    if (document.getElementById('token').value != "") {
        verificaToken();

    } else {

        alert("Preencha todos os campos.");
        document.getElementById("botaoToken").disabled = false;

    }

}

async function enviarVerificacao() {
    var valores = [document.getElementById('email').value];
    fetch("../php/enviaChavePub.php")
        .then(async function (response) {
            let data = await response.json();

            var k = CryptoJS.lib.WordArray.random(16);

            var arr = {};
            for (var i = 0; i < valores.length; i++) {
                var valor = valores[i];
                var iv = CryptoJS.lib.WordArray.random(16);
                var resultado = CryptoJS.AES.encrypt(valor, k, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.ZeroPadding
                }).toString();
                arr['dado' + (i + 1)] = resultado;
                arr['iv' + (i + 1)] = iv.toString();
            }
            var cript = new JSEncrypt();
            cript.setPublicKey(data.pub);
            var res = cript.encrypt(k.toString());

            var dados = new FormData();
            for (var key in arr) {
                dados.append(key, arr[key]);
            }
            dados.append('len', valores.length);
            dados.append('k', res);
            fetch("../php/validar_conta.php", {
                method: "POST",
                body: dados
            });
        });
}


async function mudarSenha() {
    var valores = [document.getElementById('email').value, CryptoJS.SHA256(document.getElementById("senha").value).toString()];
    fetch("../php/enviaChavePub.php")
        .then(response => response.json())
        .then(data => {

            var k = CryptoJS.lib.WordArray.random(16);

            var arr = {};
            for (var i = 0; i < valores.length; i++) {
                var valor = valores[i];
                var iv = CryptoJS.lib.WordArray.random(16);
                var resultado = CryptoJS.AES.encrypt(valor, k, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.ZeroPadding
                }).toString();
                arr['dado' + (i + 1)] = resultado;
                arr['iv' + (i + 1)] = iv.toString();
            }
            var cript = new JSEncrypt();
            cript.setPublicKey(data.pub);
            var res = cript.encrypt(k.toString());

            var dados = new FormData();
            for (var key in arr) {
                dados.append(key, arr[key]);
            }
            dados.append('len', valores.length);
            dados.append('k', res);
            fetch("../php/mudar_senha.php", {
                method: "POST",
                body: dados
            });
        })
        .catch(error => console.error(error));;
    alert("Senha atualizada com sucesso!");
    location.href = "../index.html";
}

async function verificaEmail() {
    var valores = [document.getElementById('email').value];
    fetch("../php/enviaChavePub.php")
        .then(async function (response) {
            let data = await response.json();

            var k = CryptoJS.lib.WordArray.random(16);

            var arr = {};
            for (var i = 0; i < valores.length; i++) {
                var valor = valores[i];
                var iv = CryptoJS.lib.WordArray.random(16);
                var resultado = CryptoJS.AES.encrypt(valor, k, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.ZeroPadding
                }).toString();
                arr['dado' + (i + 1)] = resultado;
                arr['iv' + (i + 1)] = iv.toString();
            }
            var cript = new JSEncrypt();
            cript.setPublicKey(data.pub);
            var res = cript.encrypt(k.toString());

            var dados = new FormData();
            for (var key in arr) {
                dados.append(key, arr[key]);
            }
            dados.append('len', valores.length);
            dados.append('k', res);
            fetch("../php/verifica_email.php", {
                method: "POST",
                body: dados
            })
                .then(async function (response) {
                    let data = await response.json();
                    if (data.autenticacao != 1) {
                        alert("Email não cadastrado!");
                        document.getElementById("botaoEmail").disabled = false;
                    } else {
                        document.querySelector(".divEmail").style.display = 'none';
                        document.querySelector(".divSenha").style.display = 'flex';
                    }
                });
        });
}

async function verificaToken() {
    var valores = [document.getElementById('token').value];
    fetch("../php/enviaChavePub.php")
        .then(async function (response) {
            let data = await response.json();

            var k = CryptoJS.lib.WordArray.random(16);

            var arr = {};
            for (var i = 0; i < valores.length; i++) {
                var valor = valores[i];
                var iv = CryptoJS.lib.WordArray.random(16);
                var resultado = CryptoJS.AES.encrypt(valor, k, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.ZeroPadding
                }).toString();
                arr['dado' + (i + 1)] = resultado;
                arr['iv' + (i + 1)] = iv.toString();
            }
            var cript = new JSEncrypt();
            cript.setPublicKey(data.pub);
            var res = cript.encrypt(k.toString());

            var dados = new FormData();
            for (var key in arr) {
                dados.append(key, arr[key]);
            }
            dados.append('len', valores.length);
            dados.append('k', res);
            fetch("../php/verifica_token.php", {
                method: "POST",
                body: dados
            })
                .then(async function (response) {
                    let data = await response.json();
                    if (data.status == 1) {
                        mudarSenha();
                    } else {
                        alert("Token inválido");
                        document.getElementById("botaoToken").disabled = false;
                    }
                });
        });
}