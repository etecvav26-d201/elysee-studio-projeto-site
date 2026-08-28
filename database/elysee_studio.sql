CREATE DATABASE IF NOT EXISTS elysee_studio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elysee_studio;

CREATE TABLE funcionarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(80) NOT NULL DEFAULT 'Funcionário',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_funcionarios_email (email),
    KEY idx_funcionarios_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NULL,
    telefone VARCHAR(30) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_clientes_nome (nome),
    KEY idx_clientes_telefone (telefone),
    KEY idx_clientes_email (email)
) ENGINE=InnoDB;

CREATE TABLE agendamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id INT UNSIGNED NOT NULL,
    funcionario_id INT UNSIGNED NULL,
    atendimento VARCHAR(150) NOT NULL,
    observacoes TEXT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    status ENUM('pendente','confirmado','concluido','cancelado') NOT NULL DEFAULT 'pendente',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_agendamentos_data (data_agendamento),
    KEY idx_agendamentos_status (status),
    KEY idx_agendamentos_cliente (cliente_id),
    KEY idx_agendamentos_funcionario (funcionario_id),
    CONSTRAINT fk_agendamento_cliente FOREIGN KEY (cliente_id)
        REFERENCES clientes (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_funcionario FOREIGN KEY (funcionario_id)
        REFERENCES funcionarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- Exemplo de criação de funcionário após gerar o hash:
-- INSERT INTO funcionarios (nome, email, senha, cargo)
-- VALUES ('Administrador', 'admin@elyseestudio.com.br', 'HASH_GERADO_PELO_PHP', 'Administrador');
