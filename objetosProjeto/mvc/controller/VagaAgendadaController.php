<?php
require_once __DIR__ . "/../dao/VagaAgendadaDAO.php";
require_once __DIR__ . "/../model/VagaAgnendada.php";

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
    public function retornarInfosAgendamento()
    {
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $usuario_id = intval($_SESSION["usuario_id"]);

        $resposta = [
            "agendamentos" => null
        ];    

        $resposta['agendamentos'] = $this->VagaAgendadaDAO->retornarAgendamentos($usuario_id);
        echo json_encode($resposta);

    }

    public function criarAgendamento($placa, $dataEntrada, $horaEntrada){
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        
        $vagaAgendada = new VagaAgendada();
        $vagaAgendada->setDataEntrada($dataEntrada);
        $vagaAgendada->setHoraEntrada($horaEntrada);
        $vagaAgendada->setPlaca($placa);
        if($this->VagaAgendadaDAO->criarAgendamento($vagaAgendada)){
            echo json_encode(['error'=> false]);
        }else{
            echo json_encode(['error'=> true, 'msg' => 'Erro ao agendar vaga, tente novamente!']);
        }

    }
}

?>