DROP DATABASE IF EXISTS EstacionamentoDB;
CREATE DATABASE EstacionamentoDB;

USE EstacionamentoDB;


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


INSERT INTO Estacionamento (totalVagas, valorHora) VALUES (100, 5.00);