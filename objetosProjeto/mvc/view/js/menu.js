let chavePublica;
window.onload = function () {

    fetch("/api/usuario.php?action=verificar_login_principal")
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                document.getElementById("cadastro_veiculos").disabled = true;
                document.getElementById("cadastro_veiculos").display = "none";
                document.getElementById("agendamento").disabled = true;
                document.getElementById("agendamento").display = "none";
                document.getElementById("perfil_usuario").disabled = true;
                document.getElementById("perfil_usuario").display = "none";
                document.getElementById("logout").disabled = true;
                document.getElementById("logout").display = "none";
            }else if (data.login == 1) {
                document.getElementById("login").disabled = true;
                document.getElementById("login").display = "none";
            }else if (data.login == 2) {
                //encaminhar para página do adm
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

function abrirTela(event) {
    const elementoClicado = event.target.id;
    const conteudo = document.getElementById("conteudo");

    switch (elementoClicado) {
        case "tela_inicial":
            conteudo.innerHTML = `
                <h2>Página Inicial</h2>
                <p>Bem-vindo ao VagaXpress! Escolha uma opção no menu lateral.</p>
            `;
            break;

        case "cadastro_veiculos":
            conteudo.innerHTML = `
                <h2>Cadastro de Veículo</h2>
                <form onsubmit="event.preventDefault(); gravarPlaca();">
                    <input id="placa" placeholder="Placa" required>
                    <input id="usuario_id" type="hidden" value="1">
                    <button type="submit">Cadastrar</button>
                </form>
            `;
            break;

        case "agendamento":
            conteudo.innerHTML = `
                <h2>Agendamento de Estacionamento</h2>
                <form onsubmit="event.preventDefault();">
                    <input placeholder="Placa" required>
                    <input type="date" required>
                    <input type="time" required>
                    <button type="submit">Agendar</button>
                </form>
            `;
            break;

        case "perfil_usuario":
            conteudo.innerHTML = `
            <div class="divTelaUsuario">
                <h2>Pefil Usuário</h2>
                <div>
                    <p>Saldo Total: R$ <span id="saldoTotal"></span></p>
                    <button id="abrir_adicionar" onclick="botaoSaldo(event)">Adicionar Saldo</button>
                    
                </div>
                <div id="conteudo_saldo"></div>
                <div id="tabela_historico"></div>
                <div id="tabela_veiculos"></div>
            </div>
        `;
        carregarInfosPerfil();
        break;

        case "notificacao":
            conteudo.innerHTML = `
                <h2>Notificações</h2>
        ;`
            carregarNotificacoes();

        case "suporte":
            conteudo.innerHTML = `
                <h2>Suporte</h2>
                <p>Entre em contato com o suporte se precisar de ajuda.</p>
            `;
            break;
            
        default:
            conteudo.innerHTML = "<h2>Bem-vindo ao VagaXpress</h2>";
    }
}

async function gravarPlaca() {

    var dados = { placa: document.getElementById("placa").value };

    res = await criptografar(dados);

    fetch("/api/veiculo.php?action=cadastrar_placa", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            cript: res
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Erro na resposta do servidor: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        alert(data.msg);
    })
    .catch(error => {
        
        alert("Erro ao cadastrar veículo. Verifique a conexão.");
    });
}

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


/* Scripts Página Usuário */
let valorSaldo='';
function botaoSaldo(event){
    const elementoClicado = event.target.id;
    const conteudo = document.getElementById("conteudo_saldo");

    switch (elementoClicado) {
        case "abrir_adicionar":
            conteudo.innerHTML = `
            <div class="divAddSaldo">
                <h2>Adicionar Saldo</h2>
                <input type="text" id="valorSaldo" placeholder="R$ 0,00" oninput="formatarSaldo(this)"><br>
                <button id="mudar_qr" onclick="botaoSaldo(event)">Gerar QR Code</button>
                <button id="fechar_adicionar" onclick="botaoSaldo(event)">Cancelar</button>
            </div>
            `;
            break;

        case "mudar_qr":
            valorSaldo = document.getElementById("valorSaldo");
            conteudo.innerHTML = `
            <div class="divQr">
                <h2>QR Code</h2>
                <img src="view/imgs/qrCodeExemplo.png"><br>
                <button id="botaoAdicionarSaldo"  onclick="adicionarSaldo()">Confirmar</button>
                <button id="abrir_adicionar" onclick="botaoSaldo(event)">Cancelar</button>
            </div>
            `;
            break;
        case "fechar_adicionar":
            conteudo.innerHTML = ``;
            break;
    }
}

function formatarSaldo(input) {
    let valor = input.value.replace(/\D/g, "");
    if (valor.length > 10) {
        valor = valor.substring(0, 10); 
    }
    valor = (parseFloat(valor) / 100).toFixed(2);

    if (valor > 99999999.99) {
        valor = 99999999.99;
    }

    input.value = "R$ " + valor.replace(".", ",");
}


async function adicionarSaldo() {
    document.getElementById('botaoAdicionarSaldo').disabled = true;
    var dados = { saldo: valorSaldo };

    res = await criptografar(dados);
    fetch("/api/usuario.php?action=adicionar_saldo", {
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
                window.alert(data.msg);
            }else{
                window.alert(`O valor de R$ ${data.msg} foi adicionado!`);
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoAdicionarSaldo').disabled = false;
}

async function carregarInfosPerfil() {
    fetch("/api/usuario.php?action=retornar_saldo")
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            window.alert(data.msg);
        }else{
            document.getElementById('saldoTotal').textContent = parseFloat(data.saldo).toFixed(2).replace(".", ",");
        }
    })
    .catch(error => console.error(error));
}

async function carregarVeiculo(){
    fetch("php/retorna_veiculos.php")
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            window.alert(data.erro);
        }else{
            if(data.placa){
                console.log(data.placa);
            }else{
                console.log("Nenhum veículo cadastrado");
            }
        }
    })
    .catch(error => console.error(error));
}

async function carregarHistoricoNF(){
    fetch("php/retorna_historicoNF.php")
    .then(response => response.json())
    .then(data => {
        if (data.erro) {
            window.alert(data.erro);
        }else{
            if(data.idNotaFiscal){
                console.log(data.idNotaFiscal);
                console.log(data.dataEmissao);
            }else{
                console.log("Nenhuma nota fiscal disponível!");
            }
        }
    })
    .catch(error => console.error(error));
}
