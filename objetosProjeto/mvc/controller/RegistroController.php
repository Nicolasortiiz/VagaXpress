<?php
require_once __DIR__ . "/../dao/RegistroDAO.php";
require_once __DIR__ . "/../model/Registro.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";
require_once __DIR__ . "/../utils/auth.php";

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');


class RegistroController
{
    private $RegistroDAO;
    private $EstacionamentoController;
    private $Auth;

    public function __construct()
    {
        $this->RegistroDAO = new RegistroDAO();
        $this->EstacionamentoController = new EstacionamentoController();
        $this->Auth = new Auth();
    }

    public function procurarPlacasDevedoras($placas)
    {
        $devedoras = $this->RegistroDAO->procurarPlacasDevedoras($placas);

        if (empty($devedoras)) {
            return json_encode(['error' => false, 'msg' => 'Nenhuma placa devedora encontrada!']);
        }

        $valorHora = $this->EstacionamentoController->retornarValorHora();
        $total = 0.0;

        foreach ($devedoras as &$devedora) {
            $entrada = strtotime($devedora['horaEntrada']);
            $saida = strtotime($devedora['horaSaida']);

            $diferencaHoras = ($saida - $entrada) / 3600;
            $diferencaHoras = ceil($diferencaHoras);

            $valorEstacionamento = $diferencaHoras * $valorHora;
            $devedora['valor'] = number_format($valorEstacionamento, 2, '.', '');

            $total += $valorEstacionamento;
        }

        return json_encode([
            'error' => false,
            'total' => number_format($total, 2, '.', ''),
            'devedoras' => $devedoras
        ]);
    }

    public function validarExcluir($placa)
    {
        if ($this->RegistroDAO->validarPlaca($placa)) {
            echo json_encode(['error' => false]);
            exit;
        }
        echo json_encode(['error' => true, 'msg' => 'Pague o estacionamento para excluir a placa!']);
        exit;
    }

    public function pagarVagas($nome, $cpf)
    {
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }
        $cpf = preg_replace('/\D/', '', $cpf);

        $url = "http://localhost:8001/veiculo.php?action=retornar_placas";
        $dados = [
            "id" => $_SESSION["usuario_id"]
        ];
        $resposta = enviaDados($url, $dados);
        $resposta = json_decode($resposta);
        if (!empty($resposta->placas)) {
            $placas = $resposta->placas;
            $devedoras = $this->procurarPlacasDevedoras($placas);
            $devedoras = json_decode($devedoras);

            if ($devedoras->error == true) {
                echo json_encode(["error" => true, "msg" => "Erro no pagamento, tente novamente mais tarde!"]);
                exit;
            } else if ($devedoras->total == 0.0) {
                echo json_encode(["error" => true, "msg" => "Nenhuma placa devedora encontrada!"]);
                exit;
            }

            $url = "http://localhost:8001/usuario.php?action=realizar_pagamento";
            $dados = [
                "valor" => $devedoras->total,
                "id" => $_SESSION["usuario_id"]
            ];
            $resposta = enviaDados($url, $dados);
            $resposta = json_decode($resposta);

            if ($resposta->error == true) {
                echo json_encode($resposta);
                exit;
            }

            $url = "http://localhost:8001/notaFiscal.php?action=gerar_nota_fiscal";
            $dados = [
                "valor" => $devedoras->total,
                "id" => $_SESSION["usuario_id"],
                "nome" => $nome,
                "cpf" => $cpf,
                "descricao" => "Pagamento placas devedoras: $placas"
            ];
            $resposta = enviaDados($url, $dados);
            $resposta = json_decode($resposta);
            $statusNF = json_decode($resposta);
            if ($statusNF->error === true) {
                echo json_encode($resposta);
                exit;
            }

            if($this->RegistroDAO->atualizarStatusPagamento($placas)){
                echo json_encode(["error" => false, "msg" => "Pagamento realizado com sucesso!"]);
                exit;
            }else{
                echo json_encode(["error" => true, "msg" => "Erro ao realizar pagamento, contate o suporte!"]);
                exit;
            }


        } else {
            echo json_encode(["error" => true, "msg" => "Error ao retornar placas!"]);
            exit;
        }
    }


}

?>