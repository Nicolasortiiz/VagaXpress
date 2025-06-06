let chavePublica;
window.onload = function () {

    fetch("/api/usuario.php?action=verificar_login_principal")
        .then(response => response.json())
        .then(data => {
            if (data.login == 2) {
                document.getElementById("cadastro_veiculos").style.disabled = true;
                document.getElementById("cadastro_veiculos").style.display = "none";
                document.getElementById("agendamento").style.disabled = true;
                document.getElementById("agendamento").style.display = "none";
                document.getElementById("perfil_usuario").style.disabled = true;
                document.getElementById("perfil_usuario").style.display = "none";
                document.getElementById("logout").style.disabled = true;
                document.getElementById("logout").style.display = "none";
            } else if (data.login == 1) {
                document.getElementById("login").style.disabled = true;
                document.getElementById("login").style.display = "none";
            } else if (data.login == 0) {
                //encaminhar para index.html
                //window.location.href = "../index.html";
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
        case "tela_inicial":
            conteudo.innerHTML = `
                <h2>Página Inicial da Administração</h2>
                <p>Bem-vindo à página de administração do VagaXpress! Escolha uma opção no menu lateral.</p>
            `;
            break;

        case "excluir_usuario":
            conteudo.innerHTML = `
                <h2>Menu de exclusão de usuários</h2>
                <form onsubmit="event.preventDefault(); excluir_usuario();">
                    <input id="usuario" placeholder="Qual usuário deseja excluir?" required>
                    <button type="submit">Enviar</button>
                </form>
            `;
            break;

        case "verificar_conta":
            conteudo.innerHTML = `
                <h2>Visualização de solicitações de verificação de contas</h2>
            `;
            break;

        case "alterar_vagas":
            conteudo.innerHTML = `
            <h2>Alteração de numero de vagas</h2>
        `;
            break;

        case "alterar_valor":
            conteudo.innerHTML = `
                <h2>Alterar valores de vagas e outras cobranças</h2>
        `
            break;

        case "banir_placa":
            conteudo.innerHTML = `
                <h2>Banir placas cadastradas</h2>
            `;
            break;

        case "enviar_notificacao":
            conteudo.innerHTML = `
                <h2>Enviar notificações para todos os usuários</h2>
                <form onsubmit="event.preventDefault(); enviar_notificacao();">
                    <input id="notificacao" placeholder="Insira sua notificação aqui" required>
                    <button type="submit">Enviar</button>
                </form>
            `;
            break;

        case "logout":
            realizarLogout();
            break;


        default:
            conteudo.innerHTML = "<h2>Bem-vindo ao VagaXpress</h2>";
    }
}

async function realizarLogout() {
    fetch("/api/usuario.php?action=logout")
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
    fetch("/api/mensagem.php?action=buscar_notificacoes")
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

async function enviar_notificacao() {
    try {
        var notificacao = { placa: document.getElementById("notificacao").value };
        const dados = {notificacao: notificacao};
        const res = await criptografar(dados);

        const resposta = await fetch("/api/mensagem.php?action=enviar_notificacao", {
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