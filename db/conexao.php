<?php
/**
 * Arquivo de conexão com o banco de dados SQLite.
 * Utiliza PDO para garantir segurança e portabilidade.
 */

// Caminho absoluto para o arquivo do banco de dados
$db_path = __DIR__ . '/sispresidio.db';

try {
    // Cria a conexão PDO
    $pdo = new PDO('sqlite:' . $db_path);
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configura o PDO para retornar resultados como objetos por padrão (opcional, mas útil)
    // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    
} catch (PDOException $e) {
    // Em caso de erro na conexão, exibe uma mensagem e encerra
    // Em produção, esta mensagem deve ser mais genérica.
    die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
}

// A variável $pdo agora contém o objeto de conexão e pode ser usada em outros scripts.
?>
