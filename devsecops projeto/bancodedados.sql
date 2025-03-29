CREATE DATABASE EstacionamentoDB;

USE EstacionamentoDB;


CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);
CREATE TABLE Veiculo (
    idVeiculo INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    placa VARCHAR(10) NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario) ON DELETE CASCADE
);
CREATE TABLE NotaFiscal (
    idNotaFiscal INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    dataEmissao DATETIME NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario) ON DELETE CASCADE
);
CREATE TABLE Estacionamento (
    idEstacionamento INT AUTO_INCREMENT PRIMARY KEY,
    totalVagas NOT NULL
);
CREATE TABLE VeiculoEstacionado (
    idVeiculoEstacionado INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    dataEntrada DATE NOT NULL,
    dataSaida DATE,
    horaEntrada TIME NOT NULL,
    horaSaida TIME
);
CREATE TABLE EstacionamentoReservado (
    idEstacionamentoReservado INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idVeiculo INT NOT NULL,
    dataEntrada DATE NOT NULL,
    horaEntrada TIME NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario) ON DELETE CASCADE,
    FOREIGN KEY (idVeiculo) REFERENCES Veiculo(idVeiculo) ON DELETE CASCADE
);
