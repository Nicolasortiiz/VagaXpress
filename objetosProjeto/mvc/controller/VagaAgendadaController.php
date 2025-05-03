<?php
require_once __DIR__ . "/../dao/VagaAgendadaDAO.php";
require_once __DIR__ . "/../model/VagaAgendada.php";
require_once __DIR__ . "/../utils/crypt.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";

header('Content-Type: application/json');
session_start();
date_default_timezone_set('America/Sao_Paulo');


class VagaAgendadaController
{
    private $VagaAgendadaDAO;
    private $EstacionamentoController;

    public function __construct()
    {
        $this->VagaAgendadaDAO = new VagaAgendadaDAO();
        $this->EstacionamentoController = new EstacionamentoController();
    }
    public function validarLogin()
    {
        if (isset($_SESSION["email"]) && isset($_SESSION["ultima_atividade"]) && isset($_SESSION["usuario_id"])) {
            $ultima_atividade = $_SESSION["ultima_atividade"];
            if (time() - $ultima_atividade > 3600) {
                session_unset();
                session_destroy();
                return false;
            }
            $_SESSION["ultima_atividade"] = time();
            return true;

        } else {
            return false;
        }
    }
    public function retornarInfosAgendamento($placas)
    {

        $agendamentos = [];

        $agendamentos = $this->VagaAgendadaDAO->retornarAgendamentos($placas);
        return $agendamentos;

    }

    public function cancelarAgendamento($idAgendamento)
    {
        if (!$this->validarLogin()) {
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
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
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

        $url = "http://localhost:8001/veiculo.php?action=deletar_placa";
        $dados = [
            'placa' => $placa,
            'id' => $_SESSION['usuario_id']
        ];
        $resposta = enviaDados($url, $dados);
        echo $resposta;
        exit;
    }

    public function procurarAgendamento($placa, $dataEntrada, $horaEntrada)
    {
        if (!$this->validarLogin()) {
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
        $url = "http://localhost:8001/usuario.php?action=validar_pagamento_agendamento";
        $dados = [
            'placa' => $placa,
            'id' => $_SESSION['usuario_id'],
            'valor' => $valor
        ];
        $resposta = enviaDados($url, $dados);
        error_log($resposta);
        echo $resposta;
        exit;
    }
    public function criarAgendamento($placa, $dataEntrada, $horaEntrada, $nome, $cpf)
    {

        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

      

        $valor = $this->EstacionamentoController->retornarValorHora();
        $url = "http://localhost:8001/usuario.php?action=realizar_pagamento_agendamento";
        $dados = [
            "valor" => $valor,
            "id" => $_SESSION['usuario_id']
        ];
        $resposta = enviaDados($url, $dados);
        $statusPagamento = json_decode($resposta);
        if ($statusPagamento->error === true) {
            echo $resposta;
            exit;
        }
        $cpf = preg_replace('/\D/', '', $cpf);
        $url = "http://localhost:8001/notaFiscal.php?action=gerar_nota_fiscal";
        $dados = [
            "valor" => $valor,
            "id" => $_SESSION["usuario_id"],
            "nome" => $nome,
            "cpf" => $cpf,
            "descricao" => "Pagamento agendamento de vaga $placa, $dataEntrada $horaEntrada"
        ];
        $resposta = enviaDados($url, $dados);
        $statusNF = json_decode($resposta);
        if ($statusNF->error === true) {
            echo $resposta;
            exit;
        }

        $agendamento = new VagaAgendada(0, $placa, $dataEntrada, $horaEntrada);
        $this->VagaAgendadaDAO->criarAgendamento($agendamento);
        echo json_encode(['error' => false]);
        exit;


    }

    public function retornarDadosPaginaPagamento(){
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $url = "http://localhost:8001/veiculo.php?action=retornar_placas";
        $dados = [
            "id" => $_SESSION["usuario_id"]
        ];
        $resposta = enviaDados($url, $dados);  
        $resposta = json_decode($resposta);

        if(!empty($resposta->placas)){
            $placas = $resposta->placas;
            $url = "http://localhost:8001/registro.php?action=retornar_vagas_devedoras";
            $dados = [
                "id" => $_SESSION["usuario_id"],
                "placas" => $placas
            ];
            $resposta = enviaDados($url, $dados);  
            $resposta = json_decode(printf($resposta));
            if($resposta->devedoras){
                $devedoras = $resposta->devedoras;
                $total = $resposta->total;        
            }
            $agendamentos = $this->retornarInfosAgendamento($placas);
            echo json_encode(["error" => false, "devedoras" => $devedoras, "total" => $total, "agendamentos" => $agendamentos]);
        }else{
            echo json_encode(["error" => true, "msg" => "Nenhuma placa cadastrada encontrada!"]);
        }
       
    }

}

?>