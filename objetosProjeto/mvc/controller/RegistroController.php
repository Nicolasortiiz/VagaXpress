<?php
require_once __DIR__ . "/../dao/RegistroDAO.php";
require_once __DIR__ . "/../model/Registro.php";
require_once __DIR__ . "/../controller/EstacionamentoController.php";
require_once __DIR__ . "/../utils/auth.php";
require_once __DIR__ . "/../utils/crypt.php";

header('Content-Type: application/json');

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
            return ['error' => false];
        }

        $valorHora = $this->EstacionamentoController->retornarValorHora();
        $total = 0.0;

        foreach ($devedoras as &$devedora) {
            $entradaStr = $devedora['dataEntrada'] . ' ' . $devedora['horaEntrada'];
            $saidaStr = $devedora['dataSaida'] . ' ' . $devedora['horaSaida'];

            $entrada = new DateTime($entradaStr);
            $saida = new DateTime($saidaStr);

            $intervalo = $saida->diff($entrada);
            $diferencaHoras = ($intervalo->days * 24) + $intervalo->h + ($intervalo->i > 0 ? 1 : 0);

            $valorEstacionamento = $diferencaHoras * $valorHora;
            $devedora['valor'] = number_format($valorEstacionamento, 2, '.', '');

            $total += $valorEstacionamento;
        }
        
        return [
            'error' => false,
            'total' => htmlspecialchars(number_format($total, 2, '.', '')),
            'devedoras' => $devedoras
        ];
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

        $url = "http://gestao-veiculos-service:8880/veiculo.php?action=retornar_placas";
        $dados = [
            "id" => $_SESSION["usuario_id"]
        ];
        $resposta = enviaDados($url, $dados);
        $resposta = json_decode($resposta);
        if (!empty($resposta->placas)) {
            $placas = $resposta->placas;
            $devedoras = $this->procurarPlacasDevedoras($placas);

            if (!isset($devedoras['devedoras']) || empty($devedoras['devedoras'])) {
                echo json_encode(["error" => true, "msg" => "Nenhuma placa devedora encontrada, erro no pagamento!"]);
                exit;
            }

            $url = "http://gestao-veiculos-service:8880/usuario.php?action=realizar_pagamento";
            $dados = [
                "valor" => $devedoras['total'],
                "id" => $_SESSION["usuario_id"]
            ];
            $resposta = enviaDados($url, $dados);
            $resposta = json_decode($resposta);

            if ($resposta->error == true) {
                echo json_encode($resposta);
                exit;
            }

            $url = "http://pagamento-service:8882/notaFiscal.php?action=gerar_nota_fiscal";

            $placas = array_map(function($registro) {
                return $registro['placa'];
            }, $devedoras['devedoras']);
            
            $dados = [
                "valor" => htmlspecialchars($devedoras['total']),
                "id" => htmlspecialchars($_SESSION["usuario_id"]),
                "nome" => htmlspecialchars($nome),
                "cpf" => htmlspecialchars($cpf),
                "descricao" => htmlspecialchars("Pagamento placas devedoras: " . implode(", ", $placas))
            ];
            $resposta = enviaDados($url, $dados);
            $resposta = json_decode($resposta);
   
            if ($resposta->error === true) {
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