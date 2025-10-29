<?php
// sispresidio/agente/presos.php

// Define o papel requerido antes de incluir o script de verificação de sessão
$required_role = 'Agente';
require_once __DIR__ . '/../includes/verifica_sessao.php';

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';
if (isset($_GET['msg'])) {
    $mensagem = "<div class='alert alert-success'>Preso cadastrado/atualizado com sucesso!</div>";
}
if (isset($_GET['movimentado'])) {
    $mensagem = "<div class='alert alert-success'>Preso movimentado com sucesso!</div>";
}


// 1. Busca a lista de presos com suas celas e pavilhões
try {
    $sql = "
        SELECT 
            p.id_preso, 
            p.nome, 
            p.matricula_preso, 
            p.data_entrada, 
            c.numero AS cela_numero, 
            pa.nome AS pavilhao_nome
        FROM presos p
        LEFT JOIN celas c ON p.id_cela_atual = c.id_cela
        LEFT JOIN pavilhoes pa ON c.id_pavilhao = pa.id_pavilhao
        ORDER BY p.nome ASC
    ";
    $stmt = $pdo->query($sql);
    $presos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $presos = [];
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar presos: " . $e->getMessage() . "</div>";
}

// Inclui o cabeçalho
include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4">Gerenciamento de Presos</h1>

<a href="cadastrar_preso.php" class="btn btn-green mb-3">Cadastrar Novo Preso</a>

<?php echo $mensagem; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Data de Entrada</th>
                <th>Cela Atual</th>
                <th>Pavilhão</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($presos)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum preso cadastrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($presos as $preso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($preso['matricula_preso']); ?></td>
                        <td><?php echo htmlspecialchars($preso['nome']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($preso['data_entrada'])); ?></td>
                        <td><?php echo htmlspecialchars($preso['cela_numero'] ?? 'Não Alocado'); ?></td>
                        <td><?php echo htmlspecialchars($preso['pavilhao_nome'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="editar_preso.php?id=<?php echo $preso['id_preso']; ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="movimentar_preso.php?id=<?php echo $preso['id_preso']; ?>" class="btn btn-sm btn-warning">Movimentar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<br>
<br>
<br>
<?php
// Inclui o rodapé
include_once __DIR__ . '/../includes/rodape.php';
?>
