<?php
require_once __DIR__ . "/../dao/VagaAgendadaDAO.php";
require_once __DIR__ . "/../model/VagaAgendada.php";
require_once __DIR__ . "/../utils/crypt.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";
require_once __DIR__ . "/../utils/auth.php";
require_once __DIR__ . "/../controller/RegistroController.php";

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');


class VagaAgendadaController
{
    private $VagaAgendadaDAO;
    private $EstacionamentoController;
    private $Auth;
    private $RegistroController;

    public function __construct()
    {
        $this->VagaAgendadaDAO = new VagaAgendadaDAO();
        $this->EstacionamentoController = new EstacionamentoController();
        $this->Auth = new Auth();
        $this->RegistroController = new RegistroController();
    }
    
    public function retornarInfosAgendamento($placas)
    {

        $agendamentos = [];

        $agendamentos = $this->VagaAgendadaDAO->retornarAgendamentos($placas);
        return $agendamentos;

    }

    public function cancelarAgendamento($idAgendamento)
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $agendamento = new VagaAgendada();
        $agendamento->setidVagaAgendada($idAgendamento);

        $horaChegada = $this->VagaAgendadaDAO->retornarHoraChegada($agendamento);
        $dataChegada = $this->VagaAgendadaDAO->retornarDataChegada($agendamento);

        $dataHoraChegada = DateTime::createFromFormat('Y-m-d H:i:s', $dataChegada . ' ' . $horaChegada);

        $agora = new DateTime();
        $minutosParaChegada = ($dataHoraChegada->getTimestamp() - $agora->getTimestamp()) / 60;

        if ($minutosParaChegada < 30 && $minutosParaChegada >= 0) {
            echo json_encode(["error" => true, "msg" => "O cancelamento só pode ser feito com até 30 minutos de antecedência!"]);
            exit;
        }

        $removido = $this->VagaAgendadaDAO->removerAgendamento($agendamento);

        if ($removido) {
            echo json_encode(["error" => false]);
        } else {
            echo json_encode(["error" => true, "msg" => "Erro ao cancelar o agendamento, tente novamente!"]);
        }
    }

    public function cancelarTodosAgendamentos($placa)
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $url = "http://vagas-service:8883/registro.php?action=validar_remocao";
        $dados = [
            'placa' => htmlspecialchars($placa)
        ];
        $resposta = enviaDados($url, $dados);
        $status = json_decode($resposta);
        if ($status->error === true) {
            echo json_encode(htmlspecialchars($resposta));
            exit;
        }

        $agendamento = new VagaAgendada();
        $agendamento->setplaca($placa);
        $agendamentos = [];
        $agendamentos = $this->VagaAgendadaDAO->retornarAgendamentos($agendamento);
        if (count($agendamentos) > 0) {
            foreach ($agendamentos as $agdt) {
                $horaChegada = $agdt['hora'];
                $dataChegada = $agdt['data'];

                $dataHoraChegada = DateTime::createFromFormat('Y-m-d H:i:s', $dataChegada . ' ' . $horaChegada);

                $agora = new DateTime();
                $minutosParaChegada = ($dataHoraChegada->getTimestamp() - $agora->getTimestamp()) / 60;

                if ($minutosParaChegada < 30) {
                    echo json_encode(["error" => true, "msg" => "A placa não pode ser deletada, existe agendamentos incanceláveis!"]);
                    exit;
                }
            }

            $this->VagaAgendadaDAO->removerTodosAgendamentos($agendamento);
        }

        $url = "http://gestao-veiculos-service:8880/veiculo.php?action=deletar_placa";
        $dados = [
            'placa' => htmlspecialchars($placa),
            'id' => htmlspecialchars($_SESSION['usuario_id'])
        ];
        $resposta = enviaDados($url, $dados);
        $json = json_decode($resposta);
        if(isset($json['msg'])){
            $json['msg'] = htmlspecialchars($json['msg'], ENT_QUOTES, 'UTF-8');
        echo json_encode($json); 
        }else{
            echo json_encode(["error" => true, "msg" => "Erro ao deletar placa!"]);
            exit;
        }
        exit;
    }

    public function procurarAgendamento($placa, $dataEntrada, $horaEntrada)
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $dataHora = $dataEntrada . ' ' . $horaEntrada;
        $dataHora = DateTime::createFromFormat('Y-m-d H:i', $dataHora);

        $agora = new DateTime();
        if ($dataHora <= $agora) {
            echo json_encode(["error" => true, "msg" => "Agendamento deve ser feito para uma data e hora futuras!"]);
            exit;
        }

        $agendamento = new VagaAgendada(0, $placa, $dataEntrada, $horaEntrada);

        if ($this->VagaAgendadaDAO->procurarAgendamento($agendamento) === true) {
            echo json_encode(["error" => true, "msg" => "Agendamento já existe!"]);
            exit;
        }
        $valor = $this->EstacionamentoController->retornarValorHora();
        $url = "http://gestao-veiculos-service:8880/usuario.php?action=validar_pagamento_agendamento";
        $dados = [
            'placa' => htmlspecialchars($placa),
            'id' => htmlspecialchars($_SESSION['usuario_id']),
            'valor' => htmlspecialchars($valor)
        ];
        $resposta = enviaDados($url, $dados);
        $json = json_decode($resposta);
        if (isset($json['msg'])) {
            $json['msg'] = htmlspecialchars($json['msg'], ENT_QUOTES, 'UTF-8');
            echo json_encode($json); 
        }else{
            echo json_encode(["error" => true, "msg" => "Erro ao validar pagamento!"]);
            exit;
        }
        exit;
    }
    public function criarAgendamento($placa, $dataEntrada, $horaEntrada, $nome, $cpf)
    {

        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $valor = $this->EstacionamentoController->retornarValorHora();
        $url = "http://gestao-veiculos-service:8880/usuario.php?action=realizar_pagamento";
        $dados = [
            "valor" => htmlspecialchars($valor),
            "id" => htmlspecialchars($_SESSION['usuario_id'])
        ];
        $resposta = enviaDados($url, $dados);
        $statusPagamento = json_decode($resposta);
        if ($statusPagamento->error === true) {
            echo json_encode(htmlspecialchars($resposta));
            exit;
        }
        $cpf = preg_replace('/\D/', '', $cpf);
        $url = "http://pagamento-service:8882/notaFiscal.php?action=gerar_nota_fiscal";
        $dados = [
            "valor" => htmlspecialchars($valor),
            "id" => htmlspecialchars($_SESSION["usuario_id"]),
            "nome" => htmlspecialchars($nome),
            "cpf" => htmlspecialchars($cpf),
            "descricao" => htmlspecialchars("Pagamento agendamento de vaga $placa, $dataEntrada $horaEntrada")
        ];
        $resposta = enviaDados($url, $dados);
        $statusNF = json_decode($resposta);
        if ($statusNF->error === true) {
            echo json_encode(htmlspecialchars($resposta));
            exit;
        }

        $agendamento = new VagaAgendada(0, $placa, $dataEntrada, $horaEntrada);
        $this->VagaAgendadaDAO->criarAgendamento($agendamento);
        echo json_encode(['error' => false]);
        exit;


    }

    public function retornarDadosPaginaPagamento() {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        
        $url = "http://gestao-veiculos-service:8880/veiculo.php?action=retornar_placas";
        $dados = ["id" => $_SESSION["usuario_id"]];
        $resposta = enviaDados($url, $dados);  
        $resposta = json_decode($resposta);
        error_log(print_r($resposta, true));
        if (!empty($resposta->placas)) {
            $placas = $resposta->placas;

            $placasDevedoras = $this->RegistroController->procurarPlacasDevedoras($placas);
            $devedoras = [];
            $total = 0.0;
    
            if (isset($placasDevedoras['devedoras'])) {
                $devedoras = $placasDevedoras['devedoras'];
                $total = $placasDevedoras['total'];
            }
    
            $agendamentos = $this->retornarInfosAgendamento($placas);
    
            echo json_encode([
                "error" => false,
                "devedoras" => $devedoras,
                "total" => htmlspecialchars($total),
                "agendamentos" => $agendamentos
            ]);
        } else {
            echo json_encode(["error" => true, "msg" => "Nenhuma placa cadastrada encontrada!"]);
        }
    }
    

}

?>