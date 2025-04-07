<?php
require_once __DIR__ . "/../dao/VeiculoDAO.php";
require_once __DIR__ . "/../model/Veiculo.php";
require_once __DIR__ . "/../model/Usuario.php";
require_once __DIR__ . "/../controller/UsuarioController.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');

class VeiculoController
{
    private $VeiculoDAO;
    private $UsuarioController;

    public function __construct()
    {
        $this->VeiculoDAO = new VeiculoDAO();
        $this->UsuarioController = new UsuarioController();
    }

    public function cadastrarPlaca($placa)
    {
        if (!$this->UsuarioController->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }


        $veiculo = new Veiculo();
        $usuario = new Usuario();
        $veiculo->setPlaca($placa);
        $usuario->setIdUsuario($_SESSION['usuario_id']);

        if (!$this->VeiculoDAO->procurarPlaca($veiculo)) {
            if ($this->VeiculoDAO->inserirPlaca($veiculo, $usuario)) {
                echo json_encode(['error' => false, 'msg' => 'Veículo cadastrado com sucesso!']);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao cadastrar placa, tente novamente!']);
            }
        } else {
            echo json_encode(['error' => true, 'msg' => 'Placa já foi cadastrada!']);
        }

    }

    public function retornarInfosAgendamento()
    {
        if (!$this->UsuarioController->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $usuario_id = intval($_SESSION["usuario_id"]);
        $usuario = new Usuario();
        $usuario->setIdUsuario($usuario_id);

        $resposta = [
            "error" => false,
            "msg" => "",
            "total" => null,
            "agendamentos" => null,
            "estacionamentos" => null
        ];


        

        $totalPagar = 0.0;





    }


}

?>