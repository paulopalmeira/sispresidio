<?php
// Caminho para o arquivo do banco de dados SQLite
$db_file = __DIR__ . '/sispresidio.db';

// Conexão PDO
try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão estabelecida com sucesso ao banco de dados SQLite.\n";

    // Script SQL completo para criar as tabelas
    $sql_script = <<<SQL
    CREATE TABLE IF NOT EXISTS usuarios ( id_usuario INTEGER PRIMARY KEY AUTOINCREMENT, nome VARCHAR(255) NOT NULL, matricula_funcional VARCHAR(50) UNIQUE NOT NULL, senha VARCHAR(255) NOT NULL, tipo VARCHAR(50) NOT NULL );
    CREATE TABLE IF NOT EXISTS pavilhoes ( id_pavilhao INTEGER PRIMARY KEY AUTOINCREMENT, nome VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'Ativo', observacoes TEXT );
    CREATE TABLE IF NOT EXISTS celas ( id_cela INTEGER PRIMARY KEY AUTOINCREMENT, numero INTEGER NOT NULL, capacidade INTEGER NOT NULL DEFAULT 6, id_pavilhao INTEGER NOT NULL, FOREIGN KEY (id_pavilhao) REFERENCES pavilhoes(id_pavilhao) ON DELETE CASCADE );
    CREATE TABLE IF NOT EXISTS presos ( id_preso INTEGER PRIMARY KEY AUTOINCREMENT, nome VARCHAR(255) NOT NULL, matricula_preso VARCHAR(50) UNIQUE NOT NULL, cpf VARCHAR(14) UNIQUE, rg VARCHAR(20) UNIQUE, data_nascimento DATE, sexo VARCHAR(10), data_entrada DATE NOT NULL, crime_cometido TEXT, observacoes TEXT, id_cela_atual INTEGER, FOREIGN KEY (id_cela_atual) REFERENCES celas(id_cela) );
    CREATE TABLE IF NOT EXISTS movimentacoes ( id_movimentacao INTEGER PRIMARY KEY AUTOINCREMENT, data_hora DATETIME NOT NULL, motivo TEXT NOT NULL, id_preso INTEGER NOT NULL, id_cela_origem INTEGER, id_cela_destino INTEGER NOT NULL, id_usuario_responsavel INTEGER NOT NULL, FOREIGN KEY (id_preso) REFERENCES presos(id_preso), FOREIGN KEY (id_cela_origem) REFERENCES celas(id_cela), FOREIGN KEY (id_cela_destino) REFERENCES celas(id_cela), FOREIGN KEY (id_usuario_responsavel) REFERENCES usuarios(id_usuario) );
SQL;

    // Executa o script de criação de tabelas
    $pdo->exec($sql_script);
    echo "Tabelas criadas com sucesso.\n";

    // Verifica se os usuários padrão já existem
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE matricula_funcional IN ('diretor123', 'agente123')");
    if ($stmt->fetchColumn() == 0) {
        $senha_diretor_hash = password_hash('senha_diretor', PASSWORD_BCRYPT);
        $senha_agente_hash = password_hash('senha_agente', PASSWORD_BCRYPT);
        $sql_insert_usuarios = "INSERT INTO usuarios (nome, matricula_funcional, senha, tipo) VALUES ('Diretor Admin', 'diretor123', :senha_diretor, 'Diretor'), ('Agente Silva', 'agente123', :senha_agente, 'Agente')";
        $stmt_user = $pdo->prepare($sql_insert_usuarios);
        $stmt_user->execute([':senha_diretor' => $senha_diretor_hash, ':senha_agente' => $senha_agente_hash]);
        echo "Usuários padrão inseridos com sucesso.\n";
    } else {
        echo "Usuários padrão já existem.\n";
    }

    // --- LÓGICA ATUALIZADA PARA CRIAR PAVILHÕES E CELAS ---
    $stmt_pavilhoes = $pdo->query("SELECT COUNT(*) FROM pavilhoes");
    if ($stmt_pavilhoes->fetchColumn() == 0) {
        $pavilhoes = [
            ['nome' => 'Pavilhão A', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão B', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão C', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão D', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão E', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão F', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão G', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão H', 'status' => 'Inativo'],
            ['nome' => 'Pavilhão de Seguro', 'status' => 'Ativo'],
            ['nome' => 'Pavilhão Disciplinar', 'status' => 'Ativo']
        ];

        $sql_pavilhao = "INSERT INTO pavilhoes (nome, status) VALUES (:nome, :status)";
        $stmt_pavilhao = $pdo->prepare($sql_pavilhao);

        $sql_cela = "INSERT INTO celas (numero, id_pavilhao) VALUES (:numero, :id_pavilhao)";
        $stmt_cela = $pdo->prepare($sql_cela);

        echo "Inserindo pavilhões e 8 celas para cada um...\n";
        foreach ($pavilhoes as $pavilhao) {
            $stmt_pavilhao->execute([':nome' => $pavilhao['nome'], ':status' => $pavilhao['status']]);
            $id_pavilhao_inserido = $pdo->lastInsertId();

            // Cria 8 celas para o pavilhão recém-criado
            for ($i = 1; $i <= 8; $i++) {
                $stmt_cela->execute([':numero' => $i, ':id_pavilhao' => $id_pavilhao_inserido]);
            }
            echo "- Pavilhão '{$pavilhao['nome']}' e suas 8 celas foram criados.\n";
        }
        echo "Criação de pavilhões e celas concluída.\n";
    } else {
        echo "Pavilhões e celas de exemplo já existem.\n";
    }

} catch (PDOException $e) {
    die("ERRO: " . $e->getMessage());
}
?>