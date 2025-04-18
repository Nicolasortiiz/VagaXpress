<?php
require_once __DIR__ . "/../dao/VagaAgendadaDAO.php";
require_once __DIR__ . "/../model/VagaAgnendada.php";

header('Content-Type: application/json');
session_start();
date_default_timezone_set('America/Sao_Paulo');


class VagaAgendadaController
{
    private $VagaAgendadaDAO;

    public function __construct()
    {
        $this->VagaAgendadaDAO = new VagaAgendadaDAO();
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
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $resposta = [
            "agendamentos" => null
        ];

        $resposta['agendamentos'] = $this->VagaAgendadaDAO->retornarAgendamentos($placas);
        echo json_encode($resposta);

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

        if ($minutosParaChegada < 30) {
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
        
        
        //mudar url
        json_encode(["error" => false, "url" => "/gateway.php/api/veiculo?action=deletar_placa"]);
    }

    public function criarAgendamento($placa, $dataEntrada, $horaEntrada)
    {

    if(!$this->validarLogin()){
        echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
        exit;
    }
    $agendamento = new VagaAgendada(0, $placa, $dataEntrada, $horaEntrada);


    if($this->VagaAgendadaDAO->procurarAgendamento($agendamento)){
        json_encode(["error"=> true, "msg"=> "Agendamento já existe!"]);
        exit;
    }

    if ($this->VagaAgendadaDAO->criarAgendamento($agendamento)) {
        echo json_encode(['error' => false]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Erro ao agendar vaga, tente novamente!']);
    }

    }
}

?>