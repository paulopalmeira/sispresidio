<?php
// sispresidio/public/index.php

// Inclui o script de verificação de sessão.
// Se não estiver logado, redireciona para login.php
require_once __DIR__ . '/../includes/verifica_sessao.php';

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../db/conexao.php';

// Inclui o cabeçalho (que contém o HTML inicial e o menu)
include_once __DIR__ . '/../includes/cabecalho.php';

$tipo_usuario = $_SESSION['tipo'];
$nome_usuario = $_SESSION['nome'];

// Variáveis para indicadores (exemplo)
$total_presos = 0;
$total_pavilhoes = 0;
$total_celas = 0;
$celas_ocupadas = 0;

try {
    // Indicador 1: Total de Presos
    $stmt = $pdo->query("SELECT COUNT(id_preso) FROM presos");
    $total_presos = $stmt->fetchColumn();

    // Indicador 2: Total de Pavilhões Ativos
    $stmt = $pdo->query("SELECT COUNT(id_pavilhao) FROM pavilhoes WHERE status = 'Ativo'");
    $total_pavilhoes = $stmt->fetchColumn();

    // Indicador 3: Total de Celas
    $stmt = $pdo->query("SELECT COUNT(id_cela) FROM celas");
    $total_celas = $stmt->fetchColumn();

    // Indicador 4: Celas Ocupadas (onde há pelo menos 1 preso)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id_cela_atual) FROM presos WHERE id_cela_atual IS NOT NULL");
    $celas_ocupadas = $stmt->fetchColumn();

} catch (PDOException $e) {
    // Em um ambiente real, você registraria este erro.
    echo "<div class='alert alert-danger'>Erro ao carregar indicadores: " . $e->getMessage() . "</div>";
}

?>

<h1 class="mb-4">Dashboard</h1>

<div class="alert alert-success" role="alert">
    Bem-vindo ao Sispresidio, **<?php echo htmlspecialchars($nome_usuario); ?>**! Seu nível de acesso é: **<?php echo htmlspecialchars($tipo_usuario); ?>**.
</div>

<div class="row">
    <!-- Indicador 1: Total de Presos -->
    <div class="col-md-3">
        <div class="card card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Total de Presos</h5>
                <p class="card-text display-4"><?php echo $total_presos; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Indicador 2: Pavilhões Ativos -->
    <div class="col-md-3">
        <div class="card card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Pavilhões Ativos</h5>
                <p class="card-text display-4"><?php echo $total_pavilhoes; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Indicador 3: Total de Celas -->
    <div class="col-md-3">
        <div class="card card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Total de Celas</h5>
                <p class="card-text display-4"><?php echo $total_celas; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Indicador 4: Celas Ocupadas -->
    <div class="col-md-3">
        <div class="card card-dashboard">
            <div class="card-body">
                <h5 class="card-title">Celas Ocupadas</h5>
                <p class="card-text display-4"><?php echo $celas_ocupadas; ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($tipo_usuario == 'Diretor'): ?>
    <h2 class="mt-5">Ações Rápidas - Diretor</h2>
    <div class="list-group">
        <a href="/diretor/pavilhoes.php" class="list-group-item list-group-item-action">Gerenciar Status dos Pavilhões</a>
        <a href="/diretor/relatorio_movimentacoes.php" class="list-group-item list-group-item-action">Visualizar Relatório de Movimentações</a>
    </div>
<?php elseif ($tipo_usuario == 'Agente'): ?>
    <h2 class="mt-5">Ações Rápidas - Agente</h2>
    <div class="list-group">
        <a href="/agente/presos.php" class="list-group-item list-group-item-action">Gerenciar e Cadastrar Presos</a>
        <a href="/agente/movimentar_preso.php" class="list-group-item list-group-item-action">Realizar Movimentação de Presos</a>
    </div>
<?php endif; ?>

<?php
// Inclui o rodapé
include_once __DIR__ . '/../includes/rodape.php';
?>
