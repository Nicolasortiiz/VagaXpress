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
    document.querySelectorAll('[id]').forEach(i => {
        i.classList.remove('desativado');
    });
    switch (elementoClicado) {
        case "tela_inicial":
            conteudo.innerHTML = `
                <h2>Página Inicial</h2>
                <p>Bem-vindo ao VagaXpress! Escolha uma opção no menu lateral.</p>

                <h1>Seja bem vindo!</h1>

                <div class="info-container">
                    <h2>Como funciona o nosso estacionamento?</h2>
                    <p>
                        Nosso estacionamento é lindo e icrível blablabla
                    </p>
                </div>

                <img 
                    src="foto do nicolas" 
                    alt="Imagem de divulgação do estacionamento" 
                    class="imagem-divulgacao"
                />

</body>
</html>
            `;
            break;

        case "agendamento":
            document.getElementById('agendamento').classList.add('desativado');
            conteudo.innerHTML = `
                <h2>Agendamento de Estacionamento</h2>
                <form id='formAgendamento' onsubmit="event.preventDefault();validarAgendamento();">
                    <label for="carros">Escolha uma placa:</label>
                    <select id="carros">
                        <option value="">Carregando...</option>
                    </select>
                    <input id="data_agendamento" type="date" required>
                    <input id="hora_agendamento" type="time" required>
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
            carregarPlacasPerfil();
            carregarDadosPagamento();


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
            document.getElementById('suporte').classList.add('desativado');
            conteudo.innerHTML = `
                <div id="conteudoSuporte"></div>
            `;
            carregarSuporte();
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

    const res = await criptografar(dados);

    fetch("/gateway.php/api/veiculo?action=cadastrar_placa", {
        method: "POST",
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

    const res = await criptografar(dados);
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
                    <td>${nf.data}</td>
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
                    <td><button id="botaoDeletarPlaca" onclick="deletarVeiculo('${placa}')">Deletar</button></td>
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

    document.getElementById('botaoDeletarPlaca').disabled = true;
    var dados = { placa: placa };

    const res = await criptografar(dados);

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
            document.getElementById("modalDetalhesNF").classList.remove("hidden");
        })
        .catch(error => {
            console.error("Erro ao buscar detalhes da nota fiscal:", error);
        });
}

function fecharModalNota() {
    document.getElementById("modalDetalhesNF").classList.add("hidden");
}




/* Página Agendamento/Pagamento */

function validarAgendamento() {
    document.getElementById('formAgendamento').classList.add('desativado');
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
    document.getElementById('formAgendamento').classList.remove('desativado');
    document.getElementById('divPagamento').remove();
    document.getElementById('botaoAgendar').disabled = false;
}



function carregarPlacasPerfil() {
    fetch("/gateway.php/api/veiculo?action=retornar_placas")
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


    var dados = {
        placa: document.getElementById('carros').value,
        data: document.getElementById('data_agendamento').value,
        hora: document.getElementById('hora_agendamento').value
    };

    const res1 = await criptografar(dados);

    const validar = await fetch("/gateway.php/api/vagaAgendada?action=procurar_agendamento", {
        method: "POST",
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
        fetch("/gateway.php/api/vagaAgendada?action=criar_agendamento", {
            method: "POST",
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
    fetch("/gateway.php/api/vagaAgendada?action=dados_pagina_pagamento")
        .then(response => response.json())
        .then(data => {
            const tabelaAgendadas = document.getElementById('tabelaAgendadas').querySelector('tbody');
            const tabelaRegistros = document.getElementById('tabelaRegistros').querySelector('tbody');
            const divPagamento = document.getElementById('divTelaPagamento');

            tabelaAgendadas.innerHTML = "";
            tabelaRegistros.innerHTML = "";
            divPagamento.innerHTML = "";

            if (data.error) {
                divPagamento.innerHTML = `<p>${data.msg}</p>`;
                return;
            }

            if (!data.agendamentos || data.agendamentos.length === 0) {
                tabelaAgendadas.innerHTML = `
                <tr>
                    <td colspan="3">Nenhuma vaga agendada.</td>
                </tr>
            `;
            } else {
                data.agendamentos.forEach(ag => {
                    tabelaAgendadas.innerHTML += `
                    <tr>
                        <td>${ag.placa}</td>
                        <td>${ag.data}</td>
                        <td>${ag.hora}</td>
                        <td><button onclick='cancelarAgendamento(${JSON.stringify(ag.id)})'>Cancelar</button></td>
                    </tr>
                `;
                });
            }


            if (!data.devedoras || data.devedoras.length === 0) {
                tabelaRegistros.innerHTML = `
                <tr>
                    <td colspan="4">Nenhuma dívida encontrada.</td>
                </tr>
            `;
            } else {
                data.devedoras.forEach(dev => {
                    tabelaRegistros.innerHTML += `
                    <tr>
                        <td>${dev.placa}</td>
                        <td>${dev.dataEntrada}</td>
                        <td>${dev.horaEntrada}</td>
                        <td>${dev.dataSaida}</td>
                        <td>${dev.horaSaida}</td>
                        <td>R$ ${parseFloat(dev.valor).toFixed(2)}</td>
                    </tr>
                `;
                });



            }
            if (data.total != null) {
                document.getElementById('dividaTotal').textContent = parseFloat(data.saldo).toFixed(2).replace(".", ",");
            } else {
                document.getElementById('dividaTotal').textContent = "0,00";
            }

        })
        .catch(error => {
            console.error('Erro ao carregar dados de pagamento:', error);
            const divPagamento = document.getElementById('divTelaPagamento');
            divPagamento.innerHTML = `<p>Erro ao carregar dados. Tente novamente.</p>`;
        });
}

async function cancelarAgendamento($id) {
    var dados = {
        id: $id
    };

    const res = await criptografar(dados);

    fetch("/gateway.php/api/vagaAgendada?action=cancelar_agendamento", {
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
                window.alert('Agendamento cancelado com sucesso!');
                carregarDadosPagamento();
            }
        })
        .catch(error => console.error(error));
}

function abrirTelaPagamento() {
    document.getElementById('pagarDivida').disabled = true;
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

    fetch("/gateway.php/api/registro?action=pagar_vagas", {
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
                window.alert('Pagamento realizado com sucesso!');
                carregarDadosPagamento();
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoPagamento').disabled = false;
    document.getElementById('botaoPagamento').textContent = 'Confirmar Pagamento';
}

/* Scripts Página Suporte */

function carregarSuporte() {
    fetch("/gateway.php/api/usuario?action=verificar_login_suporte")
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
                        <input class="inputSuporte" id="textoSuporte" placeholder="Mensagem" required>

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
                        <input class="inputSuporte" id="textoSuporte" placeholder="Mensagem" required>

                        <button class="botaoSuporte" id="botaoEnviarSuporte" type="submit">Enviar</button>
                    </form>
                </div>
                `;
            }
        })
        .catch(error => console.error(error));
}


async function validarEmailSuporte(){
    var dados = {email: document.getElementById('email').value};

    const res = await criptografar(dados);

    fetch("/gateway.php/api/suporte?action=validar_email", {
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
                document.getElementById("conteudo_suporte").innerHTML = `
                <form onsubmit="event.preventDefault(); enviarSuporteDeslogado();">
                    <h2>Um token foi enviado para o email: ${document.getElementById('email').value}</h2>
                    <label for="token">Token:</label>
                    <input id="token" placeholder="token" required>
                    <button id="botaoEnviarSuporte" type="submit">Enviar</button>
                </form>
                `
            }
        })
        .catch(error => console.error(error));
}

async function enviarSuporteDeslogado() {
    document.getElementById('botaoEnviarSuporte').disabled = true;
    document.getElementById('botaoEnviarSuporte').textContent = 'Validando...';
    var dados = {
        token: document.getElementById('token').value,
        email: document.getElementById('email').value,
        tipo: document.getElementById('tipoMensagem').value,
        texto: document.getElementById('textoSuporte').value
    };

    const res = await criptografar(dados);

    fetch("/gateway.php/api/suporte?action=enviar_suporte_deslogado", {
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
            }
        })
        .catch(error => console.error(error));



    document.getElementById('botaoEnviarSuporte').disabled = false;
    document.getElementById('botaoEnviarSuporte').textContent = 'Enviar';
}

async function enviarSuporteLogado() {
    document.getElementById('botaoEnviarSuporte').disabled = true;
    document.getElementById('botaoEnviarSuporte').textContent = 'Validando...';
    var dados = {
        tipo: document.getElementById('tipoMensagem').value,
        texto: document.getElementById('textoSuporte').value
    };

    const res = await criptografar(dados);

    fetch("/gateway.php/api/suporte?action=enviar_suporte_logado", {
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
            }
        })
        .catch(error => console.error(error));

    document.getElementById('botaoEnviarSuporte').disabled = false;
    document.getElementById('botaoEnviarSuporte').textContent = 'Enviar';
}