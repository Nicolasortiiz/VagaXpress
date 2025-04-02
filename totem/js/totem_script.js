
let chavePublica;

window.onload = function () {
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


async function confirmarPlaca() {
    document.getElementById('confirmarPlaca').disabled = true
    var placa = document.getElementById('campoPlaca').value;
    placa = placa.replace(/[^A-Z0-9]/gi, '');

    var dados = {
        placa: placa
    };

    var res = await criptografar(dados);

    fetch('/php/confirmar_placa.php', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cript: res
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.telaPlaca').style.pointerEvents = 'none';
                document.querySelector('.telaPlaca').style.display = 'none';
                document.querySelector('.telaInfos').style.pointerEvents = 'auto';
                document.querySelector('.telaInfos').style.display = 'block';
                document.querySelector('.telaPlaca input').style.borderColor = 'black';
            } else {
                document.querySelector('.telaPlaca input').style.borderColor = 'red';
                window.alert("Placa inválida!");
                document.getElementById('confirmarPlaca').disabled = false
            }
        })
        .catch(error => console.error(error));


};

async function gerarValorTotal(event) {
    event.preventDefault();
    document.getElementById('gerarQr').disabled = true
    const regex = /^\d{11}$/;

    if (regex.test(document.getElementById('campoCpf').value)) {

        var placa = document.getElementById('campoPlaca').value;
        placa = placa.replace(/[^A-Z0-9]/gi, '');

        var dados = {
            placa: placa
        };

        var res = await criptografar(dados);

        fetch('/php/gerar_valor_total.php', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                cript: res
            })
        })
            .then(response => response.json())
            .then(data => {
                const valor = parseFloat(data.valorTotal).toFixed(2);
                document.getElementById('valor').value = `R$ ${valor.replace('.', ',')} - ${data.totalHoras} Horas`;
            })
            .catch(error => console.error(error));
        document.querySelector('.telaInfos').style.pointerEvents = 'none';
        document.querySelector('.telaInfos').style.display = 'none';
        document.querySelector('.telaQr').style.pointerEvents = 'auto';
        document.querySelector('.telaQr').style.display = 'block';
    } else {
        window.alert("CPF inválido!");
        document.getElementById('gerarQr').disabled = false
    }
};

function cancelarInfos() {
    document.getElementById('confirmarPlaca').disabled = false
    document.getElementById('campoNome').value = '';
    document.getElementById('campoCpf').value = '';
    document.getElementById('campoPlaca').value = '';

    document.querySelector('.telaInfos').style.pointerEvents = 'none';
    document.querySelector('.telaInfos').style.display = 'none';
    document.querySelector('.telaPlaca').style.pointerEvents = 'auto';
    document.querySelector('.telaPlaca').style.display = 'block';

};

function cancelarQr() {
    document.getElementById('gerarQr').disabled = false
    document.querySelector('.telaQr').style.pointerEvents = 'none';
    document.querySelector('.telaQr').style.display = 'none';
    document.querySelector('.telaInfos').style.pointerEvents = 'auto';
    document.querySelector('.telaInfos').style.display = 'block';
};

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function confirmarPagamento() {

    const formData = new FormData(document.getElementById('formInfos'));
    const placaFormatada = document.getElementById('campoPlaca').value.replace(/[^A-Z0-9]/gi, '');
    formData.append('placa', placaFormatada);
    const valor = document.getElementById('valor').value.replace('R$', '').trim();
    const valorFormatado = parseFloat(valor.replace(',', '.'));
    formData.append('valorTotal', valorFormatado);
    formData.get("placa").replace(/[^A-Z0-9]/gi, '');

    var dados = {
        placa: formData.get("placa"),
        valorTotal: formData.get("valorTotal"),
        cpf: formData.get("cpf"),
        nome: formData.get("nome")
    };

    var res = await criptografar(dados);

    fetch('/php/confirmar_pagamento.php', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cript: res
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.telaQr').style.pointerEvents = 'none';
                document.querySelector('.telaQr').style.display = 'none';
                document.querySelector('.telaConfirmacao').style.pointerEvents = 'auto';
                document.querySelector('.telaConfirmacao').style.display = 'block';
                document.getElementById('confirmacao').style.display = 'block';
            } else {
                document.querySelector('.telaQr').style.pointerEvents = 'none';
                document.querySelector('.telaQr').style.display = 'none';
                document.querySelector('.telaConfirmacao').style.pointerEvents = 'auto';
                document.querySelector('.telaConfirmacao').style.display = 'block';
                document.getElementById('erro').style.display = 'block';
            }
        })
        .catch(error => console.error(error));

    await sleep(15000)
    document.querySelectorAll('input').forEach(input => {
        input.value = ''; 
    });
    window.location.reload();

};