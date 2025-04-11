DROP DATABASE EstacionamentoDB;
CREATE DATABASE EstacionamentoDB;

USE EstacionamentoDB;

CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    saldo DECIMAL(10,2),
    senha VARCHAR(255) NOT NULL,
    segredo VARCHAR(255)
);

CREATE TABLE Veiculo (
    idVeiculo INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    placa VARCHAR(10) NOT NULL UNIQUE,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario) 
);

CREATE TABLE NotaFiscal (
    idNotaFiscal INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    dataEmissao DATE NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
);

CREATE TABLE Estacionamento (
    idEstacionamento INT AUTO_INCREMENT PRIMARY KEY,
    totalVagas INT NOT NULL,
    valorHora DECIMAL(10,2) NOT NULL
);

CREATE TABLE Registro (
    idRegistro INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    dataEntrada DATE NOT NULL,
    dataSaida DATE NULL,
    horaEntrada TIME NOT NULL,
    horaSaida TIME NULL,
    statusPagamento BOOLEAN NOT NULL
);

CREATE TABLE VagaAgendada (
    idVagaAgendada INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    dataEntrada DATE NOT NULL,
    horaEntrada TIME NOT NULL,
);

CREATE TABLE VagasOcupada (
    idVagas INT AUTO_INCREMENT PRIMARY KEY,
    idVeiculoEstacionado INT NOT NULL,
    FOREIGN KEY (idRegistro) REFERENCES VagaHistorico(idRegistro) ON DELETE CASCADE
);

CREATE TABLE Mensagem (
    idMensagem INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL
);

INSERT INTO Usuario (nome, email, senha) VALUES ('Admin', 'admin@vagaxpress.com', 'admin.senha123');
INSERT INTO Usuario (nome, email, senha) VALUES ('teste', 'teste@mail.com', 'teste');
INSERT INTO Estacionamento (totalVagas, valorHora) VALUES (100, 5.00);
