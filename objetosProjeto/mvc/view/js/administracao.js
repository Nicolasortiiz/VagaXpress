let chavePublica;
window.onload = function () {

    fetch("/gateway.php/api/usuario?action=verificar_login_principal")
        .then(response => response.json())
        .then(data => {
            if (data.login == 1) {
                window.location.href = "../index.html";
            } else if (data.login == 0) {
                window.location.href = "../index.html";
            }
            if (data.pubkey) {
                chavePublica = data.pubkey;
            } else {
                window.alert("Ocorreu um erro, reinicie a página!")
            }
        })
        .catch(error => console.error(error));
}

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


function abrirTela(event) {
    const elementoClicado = event.target.id;
    const conteudo = document.getElementById("conteudo");

    switch (elementoClicado) {

        case "alterar_vagas":
            conteudo.innerHTML = `
                <h2>Alterar número de vagas</h2>
                <form onsubmit="event.preventDefault(); alterar_numero_vagas();">
                    <input id="numero_novo" class="gerenciar_vagas_text" placeholder="Insira o novo número de vagas">
                    <button type="submit" class="gerenciar_vagas_button">Enviar</button>
                </from>
                <br>
                <p> *Envios vazios zeram o campo </p>
                <br><br>
                <h2>Alterar valores de vagas</h2>
                <form onsubmit="event.preventDefault(); alterar_valor();">
                    <input id="valor" class="gerenciar_vagas_text" placeholder="Insira o novo valor">
                    <button type="submit" class="gerenciar_vagas_button">Enviar</button>
                </from>
                <br>
                <p> *Envios vazios zeram o campo </p>
        `;
            break;

        case "enviar_notificacao":
            conteudo.innerHTML = `
                <h2>Enviar notificações para todos os usuários</h2>
                <form onsubmit="event.preventDefault(); enviar_notificacao();">
                    <input id="notificacao" class="inputChat" placeholder="Insira sua notificação aqui" required>
                    <button type="submit" class="botaoChat">Enviar</button>
                </form>
            `;
            break;

        case "logout":
            realizarLogout();
            break;

        default:
            conteudo.innerHTML = `
                <h2>Página Inicial da Administração</h2>
                <p>Bem-vindo(a) à página de administração do VagaXpress! Escolha uma opção no menu lateral.</p>
            `;
    }
}

async function realizarLogout() {
    fetch("/gateway.php/api/usuario?action=logout")
        .then(response => response.json())
        .then(data => {
            if (data.logout) {
                window.location.href = "../index.html";
            }

        })
        .catch(error => console.error(error));
}

/* Scripts Página Notificação */

function carregarNotificacoes() {
    fetch("/gateway.php/api/mensagem?action=buscar_notificacoes")
        .then(response => response.json())
        .then(data => {
            const conteudo = document.getElementById("conteudo");
            let tabela = `
                <h2>Notificações</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>`;

            data.forEach(notificacao => {
                tabela += `
                    <tr>
                        <td>${notificacao.idMensagem}</td>
                        <td>${notificacao.mensagem}</td>
                    </tr>`;
            });

            tabela += `</tbody></table>`;
            conteudo.innerHTML = tabela;
        })
        .catch(error => {
            console.error("Erro ao carregar notificações:", error);
            document.getElementById("conteudo").innerHTML = "<p>Erro ao carregar notificações.</p>";
        });
}

async function alterar_numero_vagas() {
    try {
        var numero_novo = { numero_novo: document.getElementById("numero_novo").value };
        const res = await criptografar(numero_novo);

        const resposta = await fetch("/api/estacionamento.php?action=altera_numero_vagas", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cript: res })
            
        });
        const textoBruto = await resposta.text();
        console.log("Resposta bruta:", textoBruto);
        alert('Número de vagas alterado com sucesso');

        const data = JSON.parse(textoBruto);
    } catch (error) {
        console.error("Erro ao enviar alterar número de vagas do estacionamento:", error);
        alert("Erro ao alterar número de vagas do estacionamento: " + error.message);
    }
}

async function alterar_valor() {
    try {
        var valor_novo = { valor_novo: document.getElementById("valor").value };
        const res = await criptografar(valor_novo);

        const resposta = await fetch("/api/estacionamento.php?action=altera_valor_vaga", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cript: res })
            
        });
        const textoBruto = await resposta.text();
        console.log("Resposta bruta:", textoBruto);
        alert('Valor/hora da vaga alterado com sucesso');

        const data = JSON.parse(textoBruto);
    } catch (error) {
        console.error("Erro ao enviar alterar valor do estacionamento:", error);
        alert("Erro ao alterar valor do estacionamento: " + error.message);
    }
}

async function enviar_notificacao() {
    try {
        var notificacao = { placa: document.getElementById("notificacao").value };
        const dados = {notificacao: notificacao};
        const res = await criptografar(dados);

        const resposta = await fetch("/gateway.php/api/mensagem?action=enviar_notificacao", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cript: res })
        });
        const data = await resposta.json();
        console.log("Resposta do servidor:", data);
        alert("Notificação enviada com sucesso!");

    } catch (error) {
        console.error("Erro ao enviar notificação:", error);
        alert("Erro ao enviar notificação: " + error.message);
    }
}