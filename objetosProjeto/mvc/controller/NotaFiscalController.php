<?php
require_once __DIR__ . "/../dao/NotaFiscalDAO.php";
require_once __DIR__ . "/../model/NotaFiscal.php";
require_once __DIR__ . "/../utils/auth.php";

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo');


class NotaFiscalController
{
    private $NotaFiscalDAO;
    private $Auth;

    public function __construct()
    {
        $this->NotaFiscalDAO = new NotaFiscalDAO();
        $this->Auth = new Auth();
    }

    

    public function retornarInfosNotasFiscaisUsuario(){
        if (!$this->Auth->verificarLogin()) {
            echo json_encode(["error" => true, "msg" => "Necessário realizar login!"]);
            exit;
        }

        $nf = new NotaFiscal();
        $nf->setIdUsuario($_SESSION['usuario_id']);
        $notas = [];
        $notas = $this->NotaFiscalDAO->retornarInfosNotasFiscais($nf);

        $resposta = [
            "notas" => null
        ];

        $resposta["notas"] = $notas;

        echo json_encode($resposta);

    }

    public function retornarDetalhesNotaFiscal($idNotaFiscal){
        $nf = new NotaFiscal();
        $nf->setIdNotaFiscal($idNotaFiscal);
        
        $nota = [];
        $nota = $this->NotaFiscalDAO->retornarNotaFiscal($nf);

        $resposta = [
            "nota" => null
        ];

        $resposta["nota"] = $nota;

        echo json_encode($resposta);
        
    }

    public function gerarNotaFiscal($id, $cpf, $nome, $valor, $descricao){
        $nf = new NotaFiscal();
        $nf->setCpf($cpf);
        $nf->setNome($nome);
        $nf->setValor($valor);
        $nf->setDescricao($descricao);
        $nf->setDataEmissao(date('Y-m-d'));
        $nf->setIdUsuario($id);

        if($this->NotaFiscalDAO->gerarNotaFiscal($nf)){
            echo json_encode(['error' => false]);
        }else{
            echo json_encode(['error'=> true, 'msg' => 'Erro ao gerar nota fiscal, contate o suporte!']);
        }

    }
}

?>