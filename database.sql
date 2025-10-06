CREATE DATABASE esmalteria_db;
USE esmalteria_db;

CREATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 senha VARCHAR(255) NOT NULL
);

CREATE TABLE esmaltes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cores TEXT NOT NULL,
  preco DECIMAL(8,2) NOT NULL,
  categorias ENUM('Cremoso', 'Metalico', 'Glitter', 'Perolado', 'fosco') NOT NULL,
  marcas VARCHAR(50) NOT NULL,
  estoque_minimo INT DEFAULT 5,
  ativo BOOLEAN DEFAULT TRUE
  );
  
CREATE TABLE movimentacoes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 esmalte_id INT NOT NULL,
 usuario_id INT NOT NULL,
 data_hora DATETIME NOT NULL,
 tipo ENUM('entrada', 'saida') NOT NULL,
 quantidade INT NOT NULL,
 observacoes TEXT,
 FOREIGN KEY (esmalte_id) REFERENCES esmaltes(id),
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
 );
 
 INSERT INTO usuarios (nome, senha) VALUES
 ('Admin', 'admin123'),
 ('Gerente', 'gerente123'),
 ('Atendente', 'atendente123');
 
 INSERT INTO esmaltes (nome, cores, preco, categorias, marcas)VALUES
 ('Leo mandou flores', 'Rosa, Fucsia, Magenta, Roxo', 4.50, 'Cremoso', 'Risque'),
 ('As mil purpurinas', 'Prata', 5.0, 'Glitter', 'Risque'),
 ('Red velvet', 'Vermelho', 7.0, 'Cremoso', 'Dailus'),
 ('Sem limites', 'Roxo', 5.0, 'Metalico', 'Impala'),
 ('Bem casado', 'Barnco, Bege', 8.0, 'Perolado', 'Vult'),
 ('Top coat', 'Transparente', 7.0, 'Fosco', 'Anita');
 
 INSERT INTO movimentacoes (esmalte_id, usuario_id, data_hora, tipo, quantidade, observacoes) VALUES
(1, 1, '2025-01-15 10:30:00', 'entrada', 5, 'Estoque inicial'),
(2, 1, '2025-01-15 10:35:00', 'entrada', 3, 'Estoque inicial'),
(3, 2, '2025-01-15 11:00:00', 'saida', 2, 'Venda para cliente'),
(4, 2, '2025-01-15 11:15:00', 'entrada', 4, 'Reposição de estoque');


