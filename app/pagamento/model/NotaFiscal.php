<?php
class NotaFiscal {
    private $idNotaFiscal;
    private $idUsuario;
    private $dataEmissao;
    private $cpf;
    private $nome;
    private $valor;
    private $descricao;

    public function __construct($idNotaFiscal = null, $idUsuario = null, $dataEmissao = null, $cpf = null, $nome = null, $valor = null, $descricao = null) {
        $this->idNotaFiscal = $idNotaFiscal;
        $this->idUsuario = $idUsuario;
        $this->dataEmissao = $dataEmissao;
        $this->cpf = $cpf;
        $this->nome = $nome;
        $this->valor = $valor;
        $this->descricao = $descricao;
    }

    public function getIdNotaFiscal() {
        return $this->idNotaFiscal;
    }

    public function setIdNotaFiscal($idNotaFiscal) {
        $this->idNotaFiscal = $idNotaFiscal;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getDataEmissao() {
        return $this->dataEmissao;
    }

    public function setDataEmissao($dataEmissao) {
        $this->dataEmissao = $dataEmissao;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getValor() {
        return $this->valor;
    }

    public function setValor($valor) {
        $this->valor = $valor;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
}
?>
