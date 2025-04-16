<?php
require_once __DIR__ . "/../dao/VeiculoDAO.php";
require_once __DIR__ . "/../model/Veiculo.php";

header('Content-Type: application/json');
session_start();
date_default_timezone_set('America/Sao_Paulo');

class VeiculoController
{
    private $VeiculoDAO;

    public function __construct()
    {
        $this->VeiculoDAO = new VeiculoDAO();
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

    public function cadastrarPlaca($placa)
    {
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }


        $veiculo = new Veiculo();
        $veiculo->setPlaca($placa);
        $veiculo->setIdUsuario($_SESSION['usuario_id']);

        if (!$this->VeiculoDAO->procurarPlaca($veiculo)) {
            if ($this->VeiculoDAO->inserirPlaca($veiculo)) {
                echo json_encode(['error' => false, 'msg' => 'Veículo cadastrado com sucesso!']);
            } else {
                echo json_encode(['error' => true, 'msg' => 'Erro ao cadastrar placa, tente novamente!']);
            }
        } else {
            echo json_encode(['error' => true, 'msg' => 'Placa já foi cadastrada!']);
        }

    }

    public function retornarPlacas(){
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $resposta = [
            "placas" => null
        ];

        $veiculo = new Veiculo();
        $veiculo->setIdUsuario($_SESSION['usuario_id']);

        $placas = $this->VeiculoDAO->retornarPlacas($veiculo);
        $resposta["placas"] = $placas;
        echo json_encode($resposta);
    }

    public function deletarPlaca($id){
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $veiculo = new Veiculo();
        $veiculo->setIdVeiculo($id);
        if($this->VeiculoDAO->deletarPlaca($veiculo)){
            echo json_encode(['error' => false]);
        }else{
            echo json_encode(['error' => true, 'msg' => 'Erro ao deletar a placa!']);
        }

    }

    public function validarCadastroPlaca($placa)
    {
        if (!$this->validarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        } 

        $veiculo = new Veiculo();
        $veiculo->setIdVeiculo($placa);
        $veiculo->setIdUsuario($_SESSION['usuario_id']);

        if($this->VeiculoDAO->procurarCadastroPlaca($veiculo)){
            echo json_encode(['error'=> false]);
        }else{
            echo json_encode(['error'=> true, 'msg'=> 'Placa nao cadastrada!']);
        }
    }
}

?>