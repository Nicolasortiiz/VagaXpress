<?php
require_once __DIR__ . "/../dao/VeiculoDAO.php";
require_once __DIR__ . "/../model/Veiculo.php";
require_once __DIR__ . "/../model/Usuario.php";

session_start();
date_default_timezone_set('America/Sao_Paulo');

class VeiculoController
{
    private $VeiculoDAO;

    public function __construct()
    {
        $this->VeiculoDAO = new VeiculoDAO();
    }

    public function cadastrarPlaca($placa)
    {
        if (isset($_SESSION['usuario_id'])) {
            $veiculo = new Veiculo();
            $usuario = new Usuario();
            $veiculo->setPlaca($placa);
            $usuario->setIdUsuario($_SESSION['usuario_id']);

            if (!$this->VeiculoDAO->procurarPlaca($veiculo)) {
                if($this->VeiculoDAO->inserirPlaca($veiculo,$usuario)) {
                    echo json_encode(['error' => false, 'msg'=> 'Veículo cadastrado com sucesso!']);
                }else{
                    echo json_encode(['error' => true, 'msg' => 'Erro ao cadastrar placa, tente novamente!']);
                }
            }else{
                echo json_encode(['error' => true, 'msg' => 'Placa já foi cadastrada!']);
            }
        }else{
            echo json_encode(['error'=> true,'msg'=> 'Necessário realizar login!']);
        }
    }



}

?>