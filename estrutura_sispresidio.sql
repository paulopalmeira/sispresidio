-- SQL Dump for SISPresidio Project
-- Target Server Type: SQLite
-- Generation Time: 27 de Outubro de 2025

-- Habilitar o uso de chaves estrangeiras no SQLite
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Estrutura da tabela `USUARIO`
-- Tabela para armazenar os usuários do sistema (Diretores e Agentes)
--

CREATE TABLE IF NOT EXISTS USUARIO (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    matricula_funcional TEXT NOT NULL UNIQUE,
    senha TEXT NOT NULL,
    tipo TEXT NOT NULL CHECK(tipo IN ('Diretor', 'Agente Penitenciário'))
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `PAVILHAO`
-- Tabela para armazenar os pavilhões da unidade
--

CREATE TABLE IF NOT EXISTS PAVILHAO (
    id_pavilhao INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    status TEXT NOT NULL CHECK(status IN ('Ativo', 'Inativo')),
    observacoes TEXT
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `CELA`
-- Tabela para armazenar as celas, relacionadas a um pavilhão
--

CREATE TABLE IF NOT EXISTS CELA (
    id_cela INTEGER PRIMARY KEY AUTOINCREMENT,
    numero INTEGER NOT NULL,
    capacidade INTEGER NOT NULL DEFAULT 6,
    id_pavilhao INTEGER NOT NULL,
    FOREIGN KEY (id_pavilhao) REFERENCES PAVILHAO (id_pavilhao)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `PRESO`
-- Tabela para armazenar os dados dos presos
--

CREATE TABLE IF NOT EXISTS PRESO (
    id_preso INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    matricula_preso TEXT NOT NULL UNIQUE,
    cpf TEXT UNIQUE,
    rg TEXT UNIQUE,
    data_nascimento DATE,
    sexo TEXT,
    data_entrada DATE NOT NULL,
    crime_cometido TEXT,
    observacoes TEXT,
    id_cela_atual INTEGER,
    FOREIGN KEY (id_cela_atual) REFERENCES CELA (id_cela)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `MOVIMENTACAO`
-- Tabela para registrar o histórico de movimentações dos presos
--

CREATE TABLE IF NOT EXISTS MOVIMENTACAO (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    data_hora DATETIME NOT NULL,
    motivo TEXT,
    id_preso INTEGER NOT NULL,
    id_cela_origem INTEGER NOT NULL,
    id_cela_destino INTEGER NOT NULL,
    id_usuario_responsavel INTEGER NOT NULL,
    FOREIGN KEY (id_preso) REFERENCES PRESO (id_preso),
    FOREIGN KEY (id_cela_origem) REFERENCES CELA (id_cela),
    FOREIGN KEY (id_cela_destino) REFERENCES CELA (id_cela),
    FOREIGN KEY (id_usuario_responsavel) REFERENCES USUARIO (id_usuario)
);
