let chavePublica;
window.onload = function () {
    document.querySelector('body').style.display = 'none';

    fetch("/gateway.php/api/usuario?action=verificar_login_principal")
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                document.getElementById("agendamento").style.disabled = true;
                document.getElementById("agendamento").style.display = "none";
                document.getElementById("perfil_usuario").style.disabled = true;
                document.getElementById("perfil_usuario").style.display = "none";
                document.getElementById("logout").style.disabled = true;
                document.getElementById("logout").style.display = "none";
            } else if (data.login == 1) {
                document.getElementById("login").style.disabled = true;
                document.getElementById("login").style.display = "none";
            } else if (data.login == 2) {
                //encaminhar para página do adm
            }
            if (data.pubkey) {
                chavePublica = data.pubkey;
            } else {
                window.alert("Ocorreu um erro, reinicie a página!")
            }
            document.querySelector('body').style.display = 'flex';
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
            <h2>Perfil do Usuário</h2>
            <div class="divAdds">
                <div>
                    <p>Nome: <span id="nomeUsuario"></span></p>
                    <p>Saldo Total: R$ <span id="saldoTotal"></span></p>
                    <button id="abrir_adicionar" onclick="botaoSaldo(event)">Adicionar Saldo</button>
                </div>

                <div id="conteudo_saldo"></div>
                
                <div>
                    <p>Cadastrar Veículo</p>
                    <form onsubmit="event.preventDefault(); gravarPlaca();">
                        <input id="placa" placeholder="Placa" required> <br>
                        <button class="botaoPlaca" type="submit">Cadastrar</button>
                    </form>
                </div>
            </div>

            <div class="tabelas-usuario">
                <div class="tabela-container">
                    <h2>Veículos Cadastrados</h2>
                    <table id="tabelaVeiculos">
                        <thead>
                            <tr>
                                <th>Placa</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="tabela-container">
                    <h2>Histórico de Notas Fiscais</h2>
                    <table id="tabelaNotas">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="modalDetalhesNF" class="modal hidden">
                    <div class="modalConteudoNF" id="conteudoNotaFiscal">         
                    </div>
                </div>
            </div>
        </div>
        `;
            carregarInfosPerfil();
            break;

        case "notificacao":
            conteudo.innerHTML = `
                <h2>Notificações</h2>
        ;`
            carregarNotificacoes();
            break;

        case "suporte":
            conteudo.innerHTML = `
                <h2>Suporte</h2>
                <p>Entre em contato com o suporte se precisar de ajuda.</p>
            `;
            break;

        case "login":
            window.location.href = "view/login.html";
            break;

        case "logout":
            realizarLogout();
            break;


        default:
            conteudo.innerHTML = "<h2>Bem-vindo ao VagaXpress</h2>";
    }
}

async function realizarLogout() {
    fetch("/gateway.php/api/usuario?action=logout")
        .then(response => response.json())
        .then(data => {
            if (data.logout) {
                location.reload();
            }

        })
        .catch(error => console.error(error));
}

async function gravarPlaca() {

    var dados = { placa: document.getElementById("placa").value };

    res = await criptografar(dados);

    fetch("/gateway.php/api/veiculo?action=cadastrar_placa", {
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


/* Scripts Página Usuário */
let valorSaldo = '';
function botaoSaldo(event) {
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
            valorSaldo = document.getElementById("valorSaldo").value;
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
    fetch("/gateway.php/api/usuario?action=adicionar_saldo", {
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
            } else {
                window.alert(`O valor de R$ ${data.msg} foi adicionado!`);
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoAdicionarSaldo').disabled = false;
}

async function carregarInfosPerfil() {
    await carregaUsuario();
    await carregaNF();
    await carregaVeiculo();

}

async function carregaUsuario() {
    fetch("/gateway.php/api/usuario?action=retornar_infos_perfil")
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                window.alert(data.msg);
            }

            if (data.saldo > 0) {
                document.getElementById('saldoTotal').textContent = parseFloat(data.saldo).toFixed(2).replace(".", ",");
            } else {
                document.getElementById('saldoTotal').textContent = "0,00";
            }

            document.getElementById('nomeUsuario').textContent = data.nome;

        })
        .catch(error => console.error(error));
}

async function carregaNF() {
    fetch("/gateway.php/api/notaFiscal?action=retornar_notas_fiscais")
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                window.alert(data.msg);
            }
            const tbody_notas = document.querySelector("#tabelaNotas tbody");
            tbody_notas.innerHTML = "";

            if (Array.isArray(data.notas)) {
                data.notas.forEach(nf => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                    <td>${nf.dataEmissao}</td>
                    <td>R$ ${parseFloat(nf.valor).toFixed(2).replace(".", ",")}</td>
                    <td><button onclick='mostrarDetalhesNota(${JSON.stringify(nf.id)})'>Detalhes</button></td>
                `;
                    tbody_notas.appendChild(tr);
                });
            } else {
                console.log("Nenhuma nota fiscal disponível");
            }
        })
        .catch(error => console.error(error));
}

async function carregaVeiculo() {
    fetch("/gateway.php/api/veiculo?action=retornar_placas")
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                window.alert(data.msg);
            }

            const tbody_veiculos = document.querySelector("#tabelaVeiculos tbody");
            tbody_veiculos.innerHTML = "";

            if (Array.isArray(data.placas) && data.placas.length > 0) {
                data.placas.forEach(placa => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                    <td>${placa}</td>
                    <td><button onclick="deletarVeiculo('${placa}')">Deletar</button></td>
                `;
                    tbody_veiculos.appendChild(tr);
                });
            } else {
                console.log("Nenhum veículo cadastrado");
            }
        })
        .catch(error => console.error(error));
}

async function deletarVeiculo(placa) {
    if (!confirm(`Tem certeza que deseja deletar o veículo com placa e seus agendamentos ${placa}?`)) {
        return;
    }

    document.getElementById('botaoAdicionarSaldo').disabled = true;
    var dados = { placa: placa };

    res = await criptografar(dados);

    fetch("/gateway.php/api/vagaAgendada?action=deletar_agendamentos_placa", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cript: res })
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.msg);
                document.getElementById('botaoAdicionarSaldo').disabled = false;
                return;
            }

            return fetch(data.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cript: res })
            });
        })
        .then(response => {
            if (!response) return;
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.error) {
                alert(data.msg);
            } else {
                alert("Veículo e agendamentos deletados com sucesso!");
                carregarVeiculo();
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            alert("Erro ao deletar o veículo.");
        })
        .finally(() => {
            document.getElementById('botaoAdicionarSaldo').disabled = false;
        });


}

async function mostrarDetalhesNota(idNota) {

    var dados = { idNotaFiscal: idNota };

    res = await criptografar(dados);

    fetch("/gateway.php/api/notaFiscal?action=retornar_detalhes_nf", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cript: res })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.nota) {
                alert("Nota fiscal não encontrada.");
                return;
            }

            const nota = data.nota;

            const conteudo = `
            <span class="detalhesNF" onclick="fecharModalNota()">&times;</span>
            <h2>Detalhes da Nota Fiscal</h2>
            <div class="detalhes-nota">
                <p><strong>Data de Emissão:</strong> ${nota.dataEmissao}</p>
                <p><strong>CPF:</strong> ${nota.cpf}</p>
                <p><strong>Nome:</strong> ${nota.nome}</p>
                <p><strong>Valor:</strong> R$ ${parseFloat(nota.valor).toFixed(2).replace(".", ",")}</p>
                <p><strong>Descrição:</strong> ${nota.descricao}</p>
            </div>
        `;

            document.getElementById("conteudoNotaFiscal").innerHTML = conteudo;
            document.getElementById("modalNotaFiscal").classList.remove("hidden");
        })
        .catch(error => {
            console.error("Erro ao buscar detalhes da nota fiscal:", error);
            alert("Erro ao buscar os detalhes.");
        });
}

function fecharModalNota() {
    document.getElementById("modalNotaFiscal").classList.add("hidden");
}




/* Página Agendamento/Pagamento */

async function carregarInfosAgendamento() {
    fetch("/gateway.php/api/veiculo?action=retornar_infos_agendamento")
        .then(response => response.json())
        .then(data => {

        })
        .catch(error => console.error(error));

}