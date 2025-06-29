DROP DATABASE IF EXISTS UsuarioDB;
CREATE DATABASE UsuarioDB;

USE UsuarioDB;

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

GRANT ALL PRIVILEGES ON PagamentoDB.* TO 'usuario_dbuser'@'%';
FLUSH PRIVILEGES;