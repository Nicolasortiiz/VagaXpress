let chavePublica;

window.onload = function () {
    fetch("/php/verificar_login.php", {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                //desativar funções para usuario nao logado
            } else if (data.login == 2){
                //encaminhar para página do adm
            }
        })
        .catch(error => console.error(error));

    fetch("/php/chave_pub.php", {
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
};

function verificarLogin(){
    fetch("/php/verificar_login.php", {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                window.location.href = "/paginas/login.html";
            }
        })
        .catch(error => console.error(error));

};

function formatarSaldo(input) {
    let valor = input.value.replace(/\D/g, ""); 
    valor = (parseFloat(valor) / 100).toFixed(2); 

    input.value = "R$ " + valor.replace(".", ",");
};

async function adicionarSaldo() {
    verificarLogin();
    document.getElementById('botaoAdicionarSaldo').disabled = true;
    var valor = document.getElementById('valorSaldo').value;

    fetch("/php/adicionar_saldo.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: valor
    })
        .then(response => response.json())
        .then(data => {
            if(data.error){
                window.alert(data.erro);
            }
            if(data.success){
                window.alert(`O valor de ${data.success} foi adicionado!`);
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoAdicionarSaldo').disabled = false;
};