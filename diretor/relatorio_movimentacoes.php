<?php
// sispresidio/diretor/relatorio_movimentacoes.php

// Define o papel requerido antes de incluir o script de verificação de sessão
$required_role = 'Diretor';
require_once __DIR__ . '/../includes/verifica_sessao.php';

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';
$movimentacoes = [];
$filtro_nome = $_GET['nome'] ?? '';
$filtro_data = $_GET['data'] ?? '';

// Constrói a consulta SQL base
$sql = "
    SELECT 
        m.data_hora, 
        p.nome AS nome_preso, 
        coalesce(c_origem.numero, 'Saída') AS cela_origem, 
        c_destino.numero AS cela_destino, 
        m.motivo, 
        u.nome AS nome_agente,
        p_origem.nome AS pavilhao_origem,
        p_destino.nome AS pavilhao_destino
    FROM movimentacoes m
    JOIN presos p ON m.id_preso = p.id_preso
    LEFT JOIN celas c_origem ON m.id_cela_origem = c_origem.id_cela
    LEFT JOIN pavilhoes p_origem ON c_origem.id_pavilhao = p_origem.id_pavilhao
    JOIN celas c_destino ON m.id_cela_destino = c_destino.id_cela
    JOIN pavilhoes p_destino ON c_destino.id_pavilhao = p_destino.id_pavilhao
    JOIN usuarios u ON m.id_usuario_responsavel = u.id_usuario
    WHERE 1=1
";

$params = [];

// Adiciona filtro por nome do preso
if (!empty($filtro_nome)) {
    $sql .= " AND p.nome LIKE :nome";
    $params[':nome'] = '%' . $filtro_nome . '%';
}

// Adiciona filtro por data
if (!empty($filtro_data)) {
    $sql .= " AND DATE(m.data_hora) = :data";
    $params[':data'] = $filtro_data;
}

$sql .= " ORDER BY m.data_hora DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar movimentações: " . $e->getMessage() . "</div>";
}

// Inclui o cabeçalho
include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4">Relatório de Movimentações</h1>

<?php echo $mensagem; ?>

<div class="card mb-4">
    <div class="card-header">Filtros de Pesquisa</div>
    <div class="card-body">
        <form method="GET" action="relatorio_movimentacoes.php">
            <div class="form-row">
                <div class="col-md-5 mb-3">
                    <label for="nome">Nome do Preso</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Buscar por nome do preso">
                </div>
                <div class="col-md-5 mb-3">
                    <label for="data">Data da Movimentação</label>
                    <input type="date" class="form-control" id="data" name="data" value="<?php echo htmlspecialchars($filtro_data); ?>">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-green btn-block">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Data/Hora</th>
                <th>Preso</th>
                <th>Origem (Cela/Pavilhão)</th>
                <th>Destino (Cela/Pavilhão)</th>
                <th>Motivo</th>
                <th>Agente Responsável</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimentacoes)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma movimentação encontrada com os filtros aplicados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($movimentacoes as $mov): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($mov['data_hora'])); ?></td>
                        <td><?php echo htmlspecialchars($mov['nome_preso']); ?></td>
                        <td><?php echo htmlspecialchars($mov['cela_origem'] . (isset($mov['pavilhao_origem']) ? ' (' . $mov['pavilhao_origem'] . ')' : '')); ?></td>
                        <td><?php echo htmlspecialchars($mov['cela_destino'] . ' (' . $mov['pavilhao_destino'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars($mov['motivo']); ?></td>
                        <td><?php echo htmlspecialchars($mov['nome_agente']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Inclui o rodapé
include_once __DIR__ . '/../includes/rodape.php';
?>
