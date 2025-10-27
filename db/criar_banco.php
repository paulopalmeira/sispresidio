<?php
// Caminho para o arquivo do banco de dados SQLite
$db_file = __DIR__ . '/sispresidio.db';

// Conexão PDO
try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão estabelecida com sucesso ao banco de dados SQLite.\n";

    // Script SQL completo
    $sql_script = <<<SQL
-- Tabela de Usuários (com papéis definidos)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(255) NOT NULL,
    matricula_funcional VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Armazenar com password_hash()
    tipo VARCHAR(50) NOT NULL -- Deve ser 'Diretor' ou 'Agente'
);

-- Tabela de Pavilhões
CREATE TABLE IF NOT EXISTS pavilhoes (
    id_pavilhao INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Ativo', -- 'Ativo' ou 'Inativo'
    observacoes TEXT
);

-- Tabela de Celas
CREATE TABLE IF NOT EXISTS celas (
    id_cela INTEGER PRIMARY KEY AUTOINCREMENT,
    numero INTEGER NOT NULL,
    capacidade INTEGER NOT NULL DEFAULT 6,
    id_pavilhao INTEGER NOT NULL,
    FOREIGN KEY (id_pavilhao) REFERENCES pavilhoes(id_pavilhao) ON DELETE CASCADE
);

-- Tabela de Presos
CREATE TABLE IF NOT EXISTS presos (
    id_preso INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(255) NOT NULL,
    matricula_preso VARCHAR(50) UNIQUE NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20) UNIQUE,
    data_nascimento DATE,
    sexo VARCHAR(10),
    data_entrada DATE NOT NULL,
    crime_cometido TEXT,
    observacoes TEXT,
    id_cela_atual INTEGER,
    FOREIGN KEY (id_cela_atual) REFERENCES celas(id_cela)
);

-- Tabela de Movimentações (Transferências)
CREATE TABLE IF NOT EXISTS movimentacoes (
    id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT,
    data_hora DATETIME NOT NULL,
    motivo TEXT NOT NULL,
    id_preso INTEGER NOT NULL,
    id_cela_origem INTEGER,
    id_cela_destino INTEGER NOT NULL,
    id_usuario_responsavel INTEGER NOT NULL, -- ID do Agente que fez a movimentação
    FOREIGN KEY (id_preso) REFERENCES presos(id_preso),
    FOREIGN KEY (id_cela_origem) REFERENCES celas(id_cela),
    FOREIGN KEY (id_cela_destino) REFERENCES celas(id_cela),
    FOREIGN KEY (id_usuario_responsavel) REFERENCES usuarios(id_usuario)
);
SQL;

    // Executa o script de criação de tabelas
    $pdo->exec($sql_script);
    echo "Tabelas criadas com sucesso.\n";

    // Verifica se os usuários padrão já existem para evitar duplicatas
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE matricula_funcional IN ('diretor123', 'agente123')");
    if ($stmt->fetchColumn() == 0) {
        // Inserir usuários padrão para teste
        // Senha 'senha_diretor' hasheada com password_hash('senha_diretor', PASSWORD_BCRYPT)
        $senha_diretor_hash = password_hash('senha_diretor', PASSWORD_BCRYPT);
        // Senha 'senha_agente' hasheada com password_hash('senha_agente', PASSWORD_BCRYPT)
        $senha_agente_hash = password_hash('senha_agente', PASSWORD_BCRYPT);

        // Os hashes fornecidos no requisito são inválidos ou incompletos, então estou gerando novos para garantir o funcionamento.
        // Hash do requisito: '$2y$10$E.qsl3Oa5B5g.oR2P6.e3.m.Qy.l8l1YgG3s7K5J7aP2R4oH6b' (senha_diretor)
        // Hash do requisito: '$2y$10$O.pql2Oa4B4g.oR1P5.e2.n.Qx.l7l0YfG2s6K4J6aP1R3oH5a' (senha_agente)
        // Vou usar os hashes gerados para garantir a segurança e o funcionamento.

        $sql_insert_usuarios = "INSERT INTO usuarios (nome, matricula_funcional, senha, tipo) VALUES
        ('Diretor Admin', 'diretor123', :senha_diretor, 'Diretor'),
        ('Agente Silva', 'agente123', :senha_agente, 'Agente')";
        
        $stmt = $pdo->prepare($sql_insert_usuarios);
        $stmt->execute([
            ':senha_diretor' => $senha_diretor_hash,
            ':senha_agente' => $senha_agente_hash
        ]);
        echo "Usuários padrão inseridos com sucesso (diretor123/senha_diretor e agente123/senha_agente).\n";
    } else {
        echo "Usuários padrão já existem.\n";
    }

    // Inserir Pavilhões e Celas de Exemplo (se não existirem)
    $stmt = $pdo->query("SELECT COUNT(*) FROM pavilhoes");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO pavilhoes (nome, status) VALUES ('Pavilhão A', 'Ativo'), ('Pavilhão B', 'Ativo'), ('Pavilhão C', 'Inativo')");
        echo "Pavilhões de exemplo inseridos.\n";
        
        // Inserir celas para Pavilhão A (id=1) e Pavilhão B (id=2)
        $pdo->exec("INSERT INTO celas (numero, id_pavilhao) VALUES (1, 1), (2, 1), (3, 2), (4, 2)");
        echo "Celas de exemplo inseridas.\n";
    } else {
        echo "Pavilhões e Celas de exemplo já existem.\n";
    }

} catch (PDOException $e) {
    die("ERRO: " . $e->getMessage());
}

// Para o usuário executar este script via shell: php sispresidio/db/criar_banco.php
// Isso criará o arquivo sispresidio/db/sispresidio.db
?>
