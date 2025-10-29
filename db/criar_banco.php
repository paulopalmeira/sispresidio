<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
require_once __DIR__ . '/conexao.php';

try {
    echo "Conectado ao MySQL. Preparando para criar a estrutura do banco de dados...\n<br>";

    $sql_script = <<<SQL
    CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        matricula_funcional VARCHAR(50) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        tipo VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS pavilhoes (
        id_pavilhao INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Ativo',
        observacoes TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS celas (
        id_cela INT PRIMARY KEY AUTO_INCREMENT,
        numero INT NOT NULL,
        capacidade INT NOT NULL DEFAULT 6,
        id_pavilhao INT NOT NULL,
        FOREIGN KEY (id_pavilhao) REFERENCES pavilhoes(id_pavilhao) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS presos (
        id_preso INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        matricula_preso VARCHAR(50) NOT NULL UNIQUE,
        cpf VARCHAR(14) UNIQUE,
        rg VARCHAR(20) UNIQUE,
        data_nascimento DATE,
        sexo VARCHAR(10),
        data_entrada DATE NOT NULL,
        crime_cometido TEXT,
        observacoes TEXT,
        id_cela_atual INT,
        FOREIGN KEY (id_cela_atual) REFERENCES celas(id_cela)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS movimentacoes (
        id_movimentacao INT PRIMARY KEY AUTO_INCREMENT,
        data_hora DATETIME NOT NULL,
        motivo TEXT NOT NULL,
        id_preso INT NOT NULL,
        id_cela_origem INT,
        id_cela_destino INT NOT NULL,
        id_usuario_responsavel INT NOT NULL,
        FOREIGN KEY (id_preso) REFERENCES presos(id_preso),
        FOREIGN KEY (id_cela_origem) REFERENCES celas(id_cela),
        FOREIGN KEY (id_cela_destino) REFERENCES celas(id_cela),
        FOREIGN KEY (id_usuario_responsavel) REFERENCES usuarios(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

    $pdo->exec($sql_script);
    echo "Tabelas verificadas e/ou criadas com sucesso.\n<br>";

    $stmt_user = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE matricula_funcional IN ('diretor123', 'agente123')");
    if ($stmt_user->fetchColumn() == 0) {
        $senha_diretor_hash = password_hash('senha_diretor', PASSWORD_BCRYPT);
        $senha_agente_hash = password_hash('senha_agente', PASSWORD_BCRYPT);
        $sql_insert_usuarios = "INSERT INTO usuarios (nome, matricula_funcional, senha, tipo) VALUES ('Diretor Admin', 'diretor123', :senha_diretor, 'Diretor'), ('Agente Silva', 'agente123', :senha_agente, 'Agente')";
        $stmt_exec = $pdo->prepare($sql_insert_usuarios);
        $stmt_exec->execute([':senha_diretor' => $senha_diretor_hash, ':senha_agente' => $senha_agente_hash]);
        echo "Usuários padrão inseridos com sucesso.\n<br>";
    }

    $stmt_pavilhao_count = $pdo->query("SELECT COUNT(*) FROM pavilhoes");
    if ($stmt_pavilhao_count->fetchColumn() == 0) {
        $pavilhoes = [['nome' => 'Pavilhão A', 'status' => 'Ativo'], ['nome' => 'Pavilhão B', 'status' => 'Ativo'], ['nome' => 'Pavilhão C', 'status' => 'Ativo'], ['nome' => 'Pavilhão D', 'status' => 'Ativo'], ['nome' => 'Pavilhão E', 'status' => 'Ativo'], ['nome' => 'Pavilhão F', 'status' => 'Ativo'], ['nome' => 'Pavilhão G', 'status' => 'Ativo'], ['nome' => 'Pavilhão H', 'status' => 'Inativo'], ['nome' => 'Pavilhão de Seguro', 'status' => 'Ativo'], ['nome' => 'Pavilhão Disciplinar', 'status' => 'Ativo']];
        $sql_pavilhao = "INSERT INTO pavilhoes (nome, status) VALUES (:nome, :status)";
        $stmt_pavilhao = $pdo->prepare($sql_pavilhao);
        $sql_cela = "INSERT INTO celas (numero, id_pavilhao) VALUES (:numero, :id_pavilhao)";
        $stmt_cela = $pdo->prepare($sql_cela);

        echo "Inserindo pavilhões e 8 celas para cada um...\n<br>";
        foreach ($pavilhoes as $pavilhao) {
            $stmt_pavilhao->execute([':nome' => $pavilhao['nome'], ':status' => $pavilhao['status']]);
            $id_pavilhao_inserido = $pdo->lastInsertId();
            for ($i = 1; $i <= 8; $i++) {
                $stmt_cela->execute([':numero' => $i, ':id_pavilhao' => $id_pavilhao_inserido]);
            }
        }
        echo "Criação de pavilhões e celas concluída.\n<br>";
    }

    echo "\n<br><strong>CONFIGURAÇÃO DO BANCO DE DADOS CONCLUÍDA!</strong>\n<br>";

} catch (PDOException $e) {
    die("ERRO ao executar o script de criação: " . $e->getMessage());
}
?>