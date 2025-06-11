// Carregamento da chave pública e verificação de login
let chavePublica;
window.onload = function () {
    document.getElementById('tela_inicial').click();
    document.querySelector('body').style.display = 'none';

    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=verificar_login_principal", {
        credentials: 'include'
    })
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
                window.location.href = "view/administracao.html";
            }
            if (data.pubkey) {
                chavePublica = data.pubkey;
            } else {
                window.alert("Ocorreu um erro, reinicie a página!")
            }
            document.querySelector('body').style.display = 'flex';
        })
        .catch(error => console.error(error));
    // Carrega o número de vagas disponíveis
    carregarVagas();
}

// Função de criptografia de mensagens
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
let espera = false;
// Função para abrir telas da aplicação
function abrirTela(event) {
    if (espera) return;

    const elementoClicado = event.target.id;
    const conteudo = document.getElementById("conteudo");
    document.querySelectorAll('[id]').forEach(i => {
        i.classList.remove('desativado');
    });
    switch (elementoClicado) {
        case "tela_inicial":
            document.getElementById('tela_inicial').classList.add('desativado');
            conteudo.innerHTML = `
                <h2>Página Inicial</h2>
                <p>Bem-vindo ao VagaXpress! Escolha uma opção no menu lateral.</p>
                <br>
                <h1 class="info-container">Seja bem vindo!</h1>
                <br>
                <p>Vagas Disponíveis: <span id="vagasTotal"></span></p>
                <br>
                <div>
                    <h2>Como funciona o nosso estacionamento?</h2><br>
                    <p>
                        VagaXpress oferece agendamento fácil e rápido para garantir sua vaga com antecedência. 
                        Basta selecionar sua placa, escolher a data e horário desejados, e pronto! 
                        Você poderá acompanhar suas vagas agendadas e os registros de cobrança diretamente na sua conta.
                        Além disso, facilitamos o pagamento das suas dívidas para que você possa utilizar nossos serviços com tranquilidade.
                    </p>
                </div>
                <br>
                <img 
                    src="view/imgs/imagem_promocional.png" 
                    alt="Imagem de divulgação do estacionamento" 
                    class="imagem-divulgacao"
                />

            </body>
            </html>
            `;
            espera = true;
            setTimeout(() => {
                espera = false;

            }, 1000);
            break;

        case "agendamento":
            document.getElementById('agendamento').classList.add('desativado');
            conteudo.innerHTML = `
                <h2>Agendamento de Estacionamento</h2>
                <form id='formAgendamento' onsubmit="event.preventDefault();validarAgendamento();">
                    <label id="LabelCarros" for="carros">Escolha uma placa:</label>
                    <select id="carros">
                        <option value="">Carregando...</option>
                    </select>
                    <input id="data_agendamento" type="date" min="<?= date('Y-m-d') ?>" required>
                    <input id="hora_agendamento" type="time" min="<?= date('H:i') ?>" required>
                    <button id="botaoAgendar" type="submit">Agendar</button>
                    
                    </form>
                    <div id='divTelaPagamento'></div>
                    <p class="totalDivida">Total Dívida: R$ <span id="dividaTotal"></span></p>
                    <button id="pagarDivida" onclick="abrirTelaPagamento()">Pagar Divida</button>
                    <div class="tabelas-usuario">
                    <div class="tabela-container">
                        <h2>Vagas Agendadas</h2>
                        <table id="tabelaAgendadas">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Data</th>
                                    <th>Hora</th> 
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="tabela-container">
                        <h2>Registro de Cobranças</h2>
                        <table id="tabelaRegistros">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>DataEntrada</th>
                                    <th>HoraEntrada</th>
                                    <th>DataSaida</th>
                                    <th>HoraSaida</th>
                                    <th>Valor (R$)</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            `;
            espera = true;
            carregarPlacasPerfil();
            carregarDadosPagamento();

            setTimeout(() => {
                espera = false;

            }, 1000);

            break;

        case "perfil_usuario":
            document.getElementById('perfil_usuario').classList.add('desativado');
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
                        <input id="placa" placeholder="Placa" maxlength=7 required> <br>
                        <button class="botaoPlaca" type="submit">Cadastrar</button>
                    </form>
                </div>
                <div>
                    <p>Cadastrar Telegram</p>
                    <p>Para adicionar o Telegram Bot para notificações clique em "Adicionar". </p>
                    <p>Será enviado um email com o link do chat para você. </p>
                    <p>Envie o seu email cadastrado no chat do bot para ser adicionado e clique em "Buscar"!</p>
                    <button id='botaoEnviarEmail' class="botaoPlaca" onclick=enviarEmailTelegram()>Adicionar</button>
                    <button id='botaoBuscarChatId' class="botaoPlaca" onclick=buscarChatId()>Buscar</button>
                    <button id='botaoRemoverTelegram' class="botaoPlaca" style="background-color: red;" onclick="removerChatId()">Remover</button>
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
            espera = true;
            carregarInfosPerfil();

            setTimeout(() => {
                espera = false;
            }), 1000;
            break;

        case "notificacao":
            document.getElementById('notificacao').classList.add('desativado');
            conteudo.innerHTML = `
                <h2>Notificações</h2>
            `;
            espera = true;
            carregarNotificacoes();
            setTimeout(() => {
                espera = false;
            }, 2000);
            break;

        case "suporte":
            document.getElementById('suporte').classList.add('desativado');
            conteudo.innerHTML = `
                <div id="conteudoSuporte"></div>
            `;
            espera = true;
            carregarSuporte();
            setTimeout(() => {
                espera = false;
            }, 1000);

            break;

        case "chat":
            document.getElementById('chat').classList.add('desativado');
            conteudo.innerHTML = `
                <div id="conteudoChat"></div>
            `;
            carregarChat();
            break;

        case "login":
            window.location.href = "view/login.html";
            break;

        case "logout":
            espera = true;
            realizarLogout();
            setTimeout(() => {
                espera = false;
            }, 1000);

            break;

        default:
            conteudo.innerHTML = "<h2>Bem-vindo ao VagaXpress</h2>";
    }
}

// Função para realizar logout
async function realizarLogout() {
    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=logout", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.logout) {
                location.reload();
            } else {
                window.alert(data.msg);
            }

        })
        .catch(error => console.error(error));
}

// Carrega número de vagas disponíveis
function carregarVagas() {
    fetch("http://api.vagaxpress.com/gateway.php/api/vagaOcupada?action=retornar_vagas", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                window.alert(data.msg);
            } else {
                document.getElementById('vagasTotal').textContent = parseFloat(data.vagasLivres);
            }
        })
        .catch(error => console.error(error));
}

/* Scripts Página Notificação */

function carregarNotificacoes() {
    fetch("http://api.vagaxpress.com/gateway.php/api/mensagem?action=buscar_notificacoes", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            const conteudo = document.getElementById("conteudo");
            conteudo.innerHTML = "";

            const titulo = document.createElement("h2");
            titulo.textContent = "Notificações";

            const tabela = document.createElement("table");
            tabela.setAttribute("border", "1");

            const thead = document.createElement("thead");
            const tr = document.createElement("tr");

            const thId = document.createElement("th");
            thId.textContent = "ID";

            const thMensagem = document.createElement("th");
            thMensagem.textContent = "Mensagem";

            tr.appendChild(thId);
            tr.appendChild(thMensagem);
            thead.appendChild(tr);
            tabela.appendChild(thead);

            const tbody = document.createElement("tbody");

            data.forEach(notificacao => {
                const trData = document.createElement("tr");

                const tdId = document.createElement("td");
                tdId.textContent = notificacao.idMensagem;

                const tdMensagem = document.createElement("td");
                tdMensagem.textContent = notificacao.mensagem;

                trData.appendChild(tdId);
                trData.appendChild(tdMensagem);
                tbody.appendChild(trData);
            });

            tabela.appendChild(tbody);
            conteudo.appendChild(tabela);
            conteudo.prepend(titulo);
        })
        .catch(error => {
            console.error("Erro ao carregar notificações:", error);
            const conteudo = document.getElementById("conteudo");
            conteudo.textContent = "Erro ao carregar notificações.";
        });
}


/* Scripts Página Usuário */
let valorSaldo = '';

// Função para abrir tela de pagamento de saldo
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

// Função de cadastro de placas
async function gravarPlaca() {
    let placa = /^[A-Za-z0-9]+$/;
    var verificadorPlaca = placa.test(document.getElementById("placa").value);
    if (!verificadorPlaca) {
        alert("Placa inválida! Use apenas letras e números.");
        return;
    }

    var dados = { placa: document.getElementById("placa").value };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/veiculo?action=cadastrar_placa", {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            cript: res
        })
    })
        .then(response => response.json())
        .then(data => {
            alert(data.msg);
            carregaVeiculo();
        })
        .catch(error => {

            alert("Erro ao cadastrar veículo. Verifique a conexão.");
        });
}

// Função para formatar o saldo enquato é digitado
function formatarSaldo(input) {
    let valor = input.value.replace(/\D/g, "");
    if (valor.length > 10) {
        valor = valor.substring(0, 10);
    }
    valor = (parseFloat(valor) / 100).toFixed(2);

    if (valor > 9999.99) {
        valor = 9999.99;
    }

    input.value = "R$ " + valor.replace(".", ",");
}

// Função para adicionar saldo a conta do usuário
async function adicionarSaldo() {
    document.getElementById('botaoAdicionarSaldo').disabled = true;
    var dados = { saldo: valorSaldo };

    const res = await criptografar(dados);
    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=adicionar_saldo", {
        method: "POST",
        credentials: 'include',
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
    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=retornar_infos_perfil", {
        credentials: 'include'
    })
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
    fetch("http://api.vagaxpress.com/gateway.php/api/notaFiscal?action=retornar_notas_fiscais", {
        credentials: 'include'
    })
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

                    const tdData = document.createElement("td");
                    tdData.textContent = nf.data;

                    const tdValor = document.createElement("td");
                    tdValor.textContent = `R$ ${parseFloat(nf.valor).toFixed(2).replace(".", ",")}`;

                    const tdDetalhes = document.createElement("td");
                    const botao = document.createElement("button");
                    botao.textContent = "Detalhes";
                    botao.addEventListener("click", () => mostrarDetalhesNota(nf.id));

                    tdDetalhes.appendChild(botao);

                    tr.appendChild(tdData);
                    tr.appendChild(tdValor);
                    tr.appendChild(tdDetalhes);

                    tbody_notas.appendChild(tr);
                });
            } else {
                console.log("Nenhuma nota fiscal disponível");
            }
        })
        .catch(error => console.error(error));
}

async function carregaVeiculo() {
    fetch("http://api.vagaxpress.com/gateway.php/api/veiculo?action=retornar_placas", {
        credentials: 'include'
    })
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

                    const tdPlaca = document.createElement("td");
                    tdPlaca.textContent = placa;

                    const tdBotao = document.createElement("td");
                    const botao = document.createElement("button");
                    botao.textContent = "Deletar";
                    botao.addEventListener("click", () => deletarVeiculo(placa));

                    tdBotao.appendChild(botao);

                    tr.appendChild(tdPlaca);
                    tr.appendChild(tdBotao);

                    tbody_veiculos.appendChild(tr);
                });
            } else {
                const tr = document.createElement("tr");
                const td = document.createElement("td");
                td.colSpan = 2;
                td.textContent = "Nenhum veículo cadastrado.";
                tr.appendChild(td);
                tbody_veiculos.appendChild(tr);
            }
        })
        .catch(error => console.error(error));
}

async function deletarVeiculo(placa) {
    if (!confirm(`Tem certeza que deseja deletar o veículo com placa e seus agendamentos ${placa}?`)) {
        return;
    }

    document.getElementById('botaoDeletarPlaca').disabled = true;
    var dados = { placa: placa };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/vagaAgendada?action=deletar_agendamentos_placa", {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cript: res })
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.msg);
                return;
            } else {
                alert(data.msg);
                carregaVeiculo();
            }

        })
        .catch(error => {
            console.error("Erro:", error);
        })
        .finally(() => {
            document.getElementById('botaoDeletarPlaca').disabled = false;
        });
}

async function mostrarDetalhesNota(idNota) {

    var dados = { idNotaFiscal: idNota };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/notaFiscal?action=retornar_detalhes_nf", {
        method: "POST",
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cript: res })
    })
        .then(response => response.json())
        .then(data => {
            if (data.nota == null) {
                return;
            }

            const nota = data.nota;

            const container = document.getElementById("conteudoNotaFiscal");
            container.innerHTML = "";

            const span = document.createElement("span");
            span.className = "detalhesNF";
            span.textContent = "×";
            span.addEventListener("click", fecharModalNota());

            const h2 = document.createElement("h2");
            h2.textContent = "Detalhes da Nota Fiscal";

            const div = document.createElement("div");
            div.className = "detalhes-nota";

            const campos = [
                { label: "Data de Emissão", value: nota.dataEmissao },
                { label: "CPF", value: nota.cpf },
                { label: "Nome", value: nota.nome },
                { label: "Valor", value: `R$ ${parseFloat(nota.valor).toFixed(2).replace(".", ",")}` },
                { label: "Descrição", value: nota.descricao }
            ];

            campos.forEach(campo => {
                const p = document.createElement("p");
                const strong = document.createElement("strong");
                strong.textContent = campo.label + ": ";
                p.appendChild(strong);
                p.appendChild(document.createTextNode(campo.value));
                div.appendChild(p);
            })

            container.appendChild(span);
            container.appendChild(h2);
            container.appendChild(div);

            document.getElementById("modalDetalhesNF").classList.remove("hidden");
        })
        .catch(error => {
            console.error("Erro ao buscar detalhes da nota fiscal:", error);
        });
}

function fecharModalNota() {
    document.getElementById("modalDetalhesNF").classList.add("hidden");
}


async function enviarEmailTelegram() {
    document.getElementById('botaoAdicionarTelegram').classList.add('desativado');
    document.getElementById('botaoAdicionarTelegram').disabled = true;
    document.getElementById('botaoAdicionarTelegram').textContent = 'Adicionando...';

    var dados = { chatId: document.getElementById('chatId').value };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=adicionar_chat", {
        method: "POST",
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cript: res })
    })
        .then(response => response.json())
        .then(data => {
            window.alert(data.msg);
        })
        .catch(error => {
            console.error("Erro ao buscar detalhes da nota fiscal:", error);
        });

    setTimeout(() => {
        document.getElementById('botaoAdicionarTelegram').classList.remove('desativado');
        document.getElementById('botaoAdicionarTelegram').disabled = false;
        document.getElementById('botaoAdicionarTelegram').textContent = 'Adicionar';
    }, 1000);


}

function removerChatId() {
    document.getElementById('botaoRemoverTelegram').classList.add('desativado');
    document.getElementById('botaoRemoverTelegram').disabled = true;
    document.getElementById('botaoRemoverTelegram').textContent = 'Removendo...';

    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=remover_chat", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            window.alert(data.msg);
        })
        .catch(error => {
            console.error("Erro ao buscar detalhes da nota fiscal:", error);
        });
    setTimeout(() => {
        document.getElementById('botaoRemoverTelegram').classList.remove('desativado');
        document.getElementById('botaoRemoverTelegram').disabled = false;
        document.getElementById('botaoRemoverTelegram').textContent = 'Remover';
    }, 1000);

}

function buscarChatId() {

}

/* Página Agendamento/Pagamento */

function validarAgendamento() {
    const dataInput = document.getElementById("data_agendamento").value;
    const horaInput = document.getElementById("hora_agendamento").value;
    const agendamento = new Date(`${dataInput}T${horaInput}`);
    const agora = new Date();
    if (agendamento < agora) {
        window.alert("A data e hora do agendamento não podem ser no passado.");
        return;
    }

    document.getElementById('pagarDivida').disabled = true;
    document.getElementById('pagarDivida').classList.add('desativado');
    document.getElementById('botaoAgendar').classList.add('desativado');
    document.getElementById('botaoAgendar').disabled = true;
    document.getElementById('divTelaPagamento').innerHTML = `
        <div id="divPagamento" class="divPagamento">
            <h2>Informações de Pagamento</h2>
            <form onsubmit="event.preventDefault(); confirmarPagamento();">
                <label for="nome_pagador">Nome do Pagador:</label><br>
                <input type="text" id="nome_pagador" placeholder="Nome completo" required><br><br>

                <label for="cpf_pagador">CPF do Pagador:</label><br>
                <input type="text" id="cpf_pagador" placeholder="000.000.000-00" oninput="formatarCpf(this)" maxlength="14" required><br><br>

                <button id="botaoPagamento" type="submit">Confirmar Pagamento</button>
                <button id="botaoCancelarPagamento" onclick="cancelarPagamento()">Cancelar</button>
            </form>
        </div>
    `;

}
function cancelarPagamento() {
    document.getElementById('botaoAgendar').classList.remove('desativado');
    document.getElementById('botaoAgendar').disabled = false;
    document.getElementById('divPagamento').remove();
    document.getElementById('pagarDivida').disabled = false;
    document.getElementById('pagarDivida').classList.remove('desativado');

}




function carregarPlacasPerfil() {
    fetch("http://api.vagaxpress.com/gateway.php/api/veiculo?action=retornar_placas", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                window.alert(data.msg);
            } else {
                const select = document.getElementById("carros");
                select.innerHTML = "";
                data.placas.forEach(placa => {
                    const option = document.createElement("option");
                    option.value = placa;
                    option.textContent = placa;
                    select.appendChild(option);
                })
            }
        })
        .catch(error => console.error(error));

}

function formatarCpf(input) {
    let value = input.value;

    value = value.replace(/\D/g, '');

    if (value.length > 3) {
        value = value.replace(/^(\d{3})(\d)/, '$1.$2');
    }
    if (value.length > 6) {
        value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    }
    if (value.length > 9) {
        value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
    }

    input.value = value;
}


async function confirmarPagamento() {
    document.getElementById('botaoPagamento').disabled = true;
    document.getElementById('botaoPagamento').textContent = 'Validando...';

    let placa = /^[A-Za-z0-9]+$/;
    let nome = /^[\p{L} ]{3,}$/u;
    let cpf = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    var verificadorPlaca = placa.test(document.getElementById("carros").value);
    var verificadorNome = nome.test(document.getElementById("nome_pagador").value);
    var verificadorCpf = cpf.test(document.getElementById("cpf_pagador").value);
    if (!verificadorNome) {
        alert("Nome inválido! Use apenas letras e espaços, com no mínimo 3 caracteres.");
        document.getElementById('botaoPagamento').disabled = false;
        document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
        return;
    }
    if (!verificadorPlaca) {
        alert("Placa inválida! Use apenas letras e números.");
        document.getElementById('botaoPagamento').disabled = false;
        document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
        return;
    }
    if (!verificadorCpf) {
        alert("CPF inválido! Use o formato 000.000.000-00.");
        document.getElementById('botaoPagamento').disabled = false;
        document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
        return;
    }

    var dados = {
        placa: document.getElementById('carros').value,
        data: document.getElementById('data_agendamento').value,
        hora: document.getElementById('hora_agendamento').value
    };

    const res1 = await criptografar(dados);

    const validar = await fetch("http://api.vagaxpress.com/gateway.php/api/vagaAgendada?action=procurar_agendamento", {
        method: "POST",
        credentials: 'include',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            cript: res1
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                window.alert(data.msg);
                return false;
            } else {
                return true;
            }
        })
        .catch(error => console.error(error))
        .finally(() => {
            document.getElementById('botaoPagamento').disabled = false;
            document.getElementById('botaoAgendar').disabled = false;
            document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
        });
    if (validar == true) {
        dados.nome = document.getElementById('nome_pagador').value;
        dados.cpf = document.getElementById('cpf_pagador').value;

        const res2 = await criptografar(dados);
        fetch("http://api.vagaxpress.com/gateway.php/api/vagaAgendada?action=criar_agendamento", {
            method: "POST",
            credentials: 'include',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                cript: res2
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    window.alert(data.msg);
                }
                window.alert('Pagamento realizado com sucesso!');
                document.getElementById('formAgendamento').classList.remove('desativado');
                document.getElementById('divPagamento').remove();
                document.getElementById('botaoAgendar').disabled = false;
            })
            .catch(error => console.error(error))
            .finally(() => {
                document.getElementById('botaoPagamento').disabled = false;
                document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
            });
    }


}

function carregarDadosPagamento() {
    fetch("http://api.vagaxpress.com/gateway.php/api/vagaAgendada?action=dados_pagina_pagamento", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            const tabelaAgendadas = document.getElementById('tabelaAgendadas').querySelector('tbody');
            const tabelaRegistros = document.getElementById('tabelaRegistros').querySelector('tbody');
            const labelCarros = document.getElementById('LabelCarros');
            const botaoAgendar = document.getElementById('botaoAgendar');
            const pagarDivida = document.getElementById('pagarDivida');
            const dividaTotal = document.getElementById('dividaTotal');

            tabelaAgendadas.innerHTML = "";
            tabelaRegistros.innerHTML = "";
            labelCarros.innerHTML = "";

            if (data.error) {
                const p = document.createElement("p");
                p.textContent = data.msg;
                labelCarros.appendChild(p);

                botaoAgendar.disabled = true;
                botaoAgendar.classList.add('desativado');

                pagarDivida.disabled = true;
                pagarDivida.classList.add('desativado');
            }

            if (Array.isArray(data.agendamentos) && data.agendamentos.length > 0) {
                data.agendamentos.forEach(ag => {
                    const tr = document.createElement("tr");

                    const tdPlaca = document.createElement("td");
                    tdPlaca.textContent = ag.placa;

                    const tdData = document.createElement("td");
                    tdData.textContent = ag.data;

                    const tdHora = document.createElement("td");
                    tdHora.textContent = ag.hora;

                    const tdBtn = document.createElement("td");
                    const btn = document.createElement("button");
                    btn.textContent = "Cancelar";
                    btn.onclick = () => cancelarAgendamento(ag.id);
                    tdBtn.appendChild(btn);

                    tr.appendChild(tdPlaca);
                    tr.appendChild(tdData);
                    tr.appendChild(tdHora);
                    tr.appendChild(tdBtn);

                    tabelaAgendadas.appendChild(tr);
                });
            } else {
                const tr = document.createElement("tr");

                const td = document.createElement("td");
                td.colSpan = 4;
                td.textContent = "Nenhuma vaga agendada.";

                tr.appendChild(td);
                tabelaAgendadas.appendChild(tr);
            }


            if (Array.isArray(data.devedoras) && data.devedoras.length > 0) {
                data.devedoras.forEach(dev => {
                    const tr = document.createElement("tr");

                    const campos = [
                        dev.placa,
                        dev.dataEntrada,
                        dev.horaEntrada,
                        dev.dataSaida,
                        dev.horaSaida,
                        `R$ ${parseFloat(dev.valor).toFixed(2).replace(".", ",")}`
                    ];

                    campos.forEach(valor => {
                        const td = document.createElement("td");
                        td.textContent = valor;
                        tr.appendChild(td);
                    });

                    tabelaRegistros.appendChild(tr);
                });
            } else {
                const tr = document.createElement("tr");

                const td = document.createElement("td");
                td.colSpan = 6;
                td.textContent = "Nenhuma dívida encontrada.";

                tr.appendChild(td);
                tabelaRegistros.appendChild(tr);
            }

            const total = parseFloat(data.total || 0).toFixed(2).replace(".", ",");
            dividaTotal.textContent = total;

        })
        .catch(error => {
            console.error('Erro ao carregar dados de pagamento:', error);
        });
}

async function cancelarAgendamento($id) {
    var dados = {
        id: $id
    };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/vagaAgendada?action=cancelar_agendamento", {
        method: "POST",
        credentials: 'include',
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
                window.alert('Agendamento cancelado com sucesso!');
                carregarDadosPagamento();
            }
        })
        .catch(error => console.error(error));
}

function abrirTelaPagamento() {
    document.getElementById('botaoAgendar').disabled = true;
    document.getElementById('botaoAgendar').classList.add('desativado');
    document.getElementById('pagarDivida').disabled = true;
    document.getElementById('pagarDivida').classList.add('desativado');
    document.getElementById('divTelaPagamento').innerHTML = `
        <div id="divPagamento" class="divPagamento">
            <h2>Informações de Pagamento</h2>
            <form onsubmit="event.preventDefault(); pagarDivida();">
                <label for="nome_pagador">Nome do Pagador:</label><br>
                <input type="text" id="nome_pagador" placeholder="Nome completo" required><br><br>

                <label for="cpf_pagador">CPF do Pagador:</label><br>
                <input type="text" id="cpf_pagador" placeholder="000.000.000-00" oninput="formatarCpf(this)" maxlength="14" required><br><br>

                <button id="botaoPagamento" type="submit">Confirmar Pagamento</button>
                <button id="botaoCancelarPagamento" onclick="cancelarPagamento()">Cancelar</button>
            </form>
        </div>
    `;
}

async function pagarDivida() {
    document.getElementById('botaoPagamento').disabled = true;
    document.getElementById('botaoPagamento').textContent = 'Validando...';
    var dados = {
        nome: document.getElementById('nome_pagador').value,
        cpf: document.getElementById('cpf_pagador').value
    };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/registro?action=pagar_vagas", {
        method: "POST",
        credentials: 'include',
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
                window.alert('Pagamento realizado com sucesso!');
                carregarDadosPagamento();
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoPagamento').disabled = false;
    document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
    document.getElementById('pagarDivida').disabled = false;
    document.getElementById('divPagamento').remove();
    document.getElementById('botaoAgendar').disabled = false;
    document.getElementById('botaoAgendar').classList.remove('desativado');
    document.getElementById('pagarDivida').classList.remove('desativado');
}

/* Scripts Página Suporte */

function carregarSuporte() {
    fetch("http://api.vagaxpress.com/gateway.php/api/usuario?action=verificar_login_suporte", {
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.login == 0) {
                document.getElementById("conteudoSuporte").innerHTML = `
                <div class="divSuporte">
                    <h2>Suporte</h2>
                    <form class="formSuporte" onsubmit="event.preventDefault(); validarEmailSuporte();">
                        <label for="email">Email de contato:</label>
                        <input class="inputSuporte" id="email" placeholder="Email" required>

                        <label for="tipoMensagem">Tipo de mensagem:</label>
                        <select class="inputSuporte" id="tipoMensagem" required>
                            <option value="Problema">Problema</option>
                            <option value="Duvida">Dúvida</option>
                            <option value="Outro">Outro</option>
                        </select>

                        <label for="textoSuporte">Mensagem:</label>
                        <input class="inputSuporte" id="textoSuporte" placeholder="Mensagem" maxlength=500 required>

                        <button class="botaoSuporte" id="botaoEnviarSuporte" type="submit">Enviar</button>
                    </form>
                    <div id="conteudo_suporte"></div>
                </div>
                `;
            } else if (data.login == 1) {
                document.getElementById("conteudoSuporte").innerHTML = `
                <div class="divSuporte">
                    <h2>Suporte</h2>
                    <form class="formSuporte" onsubmit="event.preventDefault(); enviarSuporteLogado();">
                        <label for="tipoMensagem">Tipo de mensagem:</label>
                        <select class="inputSuporte" id="tipoMensagem" required>
                            <option value="Problema">Problema</option>
                            <option value="Duvida">Dúvida</option>
                            <option value="Colaborador">Se tornar afiliado</option>
                            <option value="Outro">Outro</option>
                        </select>

                        <label for="textoSuporte">Mensagem:</label>
                        <input class="inputSuporte" id="textoSuporte" placeholder="Mensagem" maxlength=500 required>

                        <button class="botaoSuporte" id="botaoEnviarSuporte" type="submit">Enviar</button>
                    </form>
                </div>
                `;
            }
        })
        .catch(error => console.error(error));
}

function carregarChat() {
    document.getElementById("conteudoChat").innerHTML = `
        <div class="divChat">
            <h2>Chat</h2>
            <hr>
            <h4> Tenha em mente que suas mensagens não são criptografadas.</h4><br>
            <form class="formChat" id="formChat">
                <input class="inputChat" id="mensagem" placeholder="Mensagem" required>
                <button class="botaoChat" id="botaoEnviarChat" type="submit">Enviar</button>
            </form>
            <br>
            <div id="conteudo_chat"></div>
        </div>
    `;

    const form = document.getElementById('formChat');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        const input = document.getElementById('mensagem');
        const mensagem = input.value.trim();

        if (mensagem === '') return;

        const chat = document.getElementById('conteudo_chat');

        const msgUsuario = document.createElement('div');
        msgUsuario.classList.add('mensagem-usuario');
        msgUsuario.textContent = 'Você: ' + mensagem;
        chat.appendChild(msgUsuario);

        input.value = '';

        const dados = { mensagem: mensagem };
        const res = await criptografar(dados);

        try {
            const resposta = await fetch("/gateway.php/api/chat?action=mensagem_ollama", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cript: res })
            });

            const dados = await resposta.json();

            const msgIA = document.createElement('div');
            msgIA.classList.add('mensagem-ia');
            msgIA.textContent = 'IA: ' + dados.resposta;
            chat.appendChild(msgIA);

            chat.scrollTop = chat.scrollHeight;

        } catch (erro) {
            console.error('Erro ao enviar mensagem:', erro);
        }
    });
}

async function validarEmailSuporte() {
    document.getElementById('botaoEnviarSuporte').disabled = true;
    document.getElementById('botaoEnviarSuporte').textContent = 'Validando...';
    let email = /^[A-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
    var verificadorEmail = email.test(document.getElementById("email").value);
    if (!verificadorEmail) {
        alert("Email inválido! Por favor, insira um email válido.");
        return;
    }
    var dados = { email: document.getElementById('email').value };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/suporte?action=validar_email", {
        method: "POST",
        credentials: 'include',
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
                document.getElementById('botaoEnviarSuporte').disabled = false;
                document.getElementById('botaoEnviarSuporte').textContent = 'Enviar';
            } else {
                document.getElementById("conteudo_suporte").innerHTML = `
                <form onsubmit="event.preventDefault(); enviarSuporteDeslogado();">
                    <h2>Um token foi enviado para o email: ${document.getElementById('email').value}</h2>
                    <label for="token">Token:</label>
                    <input id="token" placeholder="token" required>
                    <button id="botaoEnviarToken" type="submit">Enviar</button>
                </form>
                `
            }
        })
        .catch(error => console.error(error));
}

function sanitizarTexto(texto) {
    return str
        .replace(/&/g, " ")
        .replace(/</g, " ")
        .replace(/>/g, " ")
        .replace(/"/g, " ")
        .replace(/'/g, " ");
}

async function enviarSuporteDeslogado() {
    document.getElementById('botaoEnviarToken').disabled = true;
    document.getElementById('botaoEnviarToken').textContent = 'Validando...';
    let token = /^[0-9]{6,}$/;
    var verificadorToken = token.test(document.getElementById('token').value);

    if (!verificadorToken) {
        alert("Token inválido!");
        return;
    }
    var dados = {
        token: document.getElementById('token').value,
        email: document.getElementById('email').value,
        tipo: document.getElementById('tipoMensagem').value,
        texto: sanitizarTexto(document.getElementById('textoSuporte').value.trim())
    };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/suporte?action=enviar_suporte_deslogado", {
        method: "POST",
        credentials: 'include',
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
                window.alert('Mensagem enviada com sucesso!');
                document.getElementById("conteudo_suporte").innerHTML = ``
                document.getElementById('email').value = '';
                document.getElementById('textoSuporte').value = '';

            }
        })
        .catch(error => console.error(error));



    document.getElementById('botaoEnviarToken').disabled = false;
    document.getElementById('botaoEnviarToken').textContent = 'Enviar';
}

async function enviarSuporteLogado() {
    document.getElementById('botaoEnviarSuporte').disabled = true;
    document.getElementById('botaoEnviarSuporte').textContent = 'Validando...';

    var dados = {
        tipo: document.getElementById('tipoMensagem').value,
        texto: sanitizarTexto(document.getElementById('textoSuporte').value.trim())
    };

    const res = await criptografar(dados);

    fetch("http://api.vagaxpress.com/gateway.php/api/suporte?action=enviar_suporte_logado", {
        method: "POST",
        credentials: 'include',
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
                document.alert("Mensagem enviada com sucesso!");
                document.getElementById("conteudo_suporte").innerHTML = ``
                document.getElementById('textoSuporte').value = '';

            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoEnviarSuporte').disabled = false;
    document.getElementById('botaoEnviarSuporte').textContent = 'Enviar';
}