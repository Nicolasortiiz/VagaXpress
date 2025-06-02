DROP DATABASE IF EXISTS EstacionamentoDB;
CREATE DATABASE EstacionamentoDB;

USE EstacionamentoDB;

CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    saldo DECIMAL(10,2),
    segredo VARCHAR(255),
    chatId VARCHAR(255) UNIQUE
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
    descricao VARCHAR(255) NOT NULL,
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
    horaEntrada TIME NOT NULL
);

CREATE TABLE VagaOcupada (
    idVagas INT AUTO_INCREMENT PRIMARY KEY,
    idRegistro INT NOT NULL,
    FOREIGN KEY (idRegistro) REFERENCES Registro(idRegistro) ON DELETE CASCADE
);

CREATE TABLE Mensagem (
    idMensagem INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL
);

CREATE TABLE Suporte (
    idSuporte INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL,
    email VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL
);

INSERT INTO Usuario (nome, email) VALUES ('Admin', 'admin@vagaxpress.com');
INSERT INTO Usuario (nome, email) VALUES ('teste', 'teste@mail.com');
INSERT INTO Usuario (nome, email, segredo) VALUES ('teste', 'nicolasortiz2003@gmail.com', '7KI7FG56KT46JH5V62R24ZVYTEGTKJHWAQHYE5YWU623G6OECIAHS6BWG4DUV6FNPAYJBBX7SYZYFDOVFMAPXAPMJLS4PLBTHAS2QTQ');
INSERT INTO Estacionamento (totalVagas, valorHora) VALUES (100, 5.00);
 