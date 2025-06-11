DROP DATABASE IF EXISTS NotificacaoDB;
CREATE DATABASE NotificacaoDB;

USE NotificacaoDB;


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

GRANT ALL PRIVILEGES ON PagamentoDB.* TO 'notificacao_dbuser'@'%';
FLUSH PRIVILEGES;