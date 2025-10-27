<?php
// sispresidio/diretor/pavilhoes.php

// Define o papel requerido antes de incluir o script de verificação de sessão
$required_role = 'Diretor';
require_once __DIR__ . '/../includes/verifica_sessao.php';

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';

// 1. Processa a requisição de mudança de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pavilhao']) && isset($_POST['status'])) {
    $id_pavilhao = filter_var($_POST['id_pavilhao'], FILTER_VALIDATE_INT);
    $novo_status = $_POST['status'] === 'Ativar' ? 'Ativo' : 'Inativo';

    if ($id_pavilhao) {
        try {
            $stmt = $pdo->prepare("UPDATE pavilhoes SET status = :status WHERE id_pavilhao = :id");
            $stmt->execute([
                ':status' => $novo_status,
                ':id' => $id_pavilhao
            ]);
            $mensagem = "<div class='alert alert-success'>Status do Pavilhão atualizado com sucesso para **{$novo_status}**!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger'>Erro ao atualizar status: " . $e->getMessage() . "</div>";
        }
    }
}

// 2. Busca a lista de pavilhões
try {
    $stmt = $pdo->query("SELECT id_pavilhao, nome, status, observacoes FROM pavilhoes ORDER BY nome ASC");
    $pavilhoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pavilhoes = [];
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar pavilhões: " . $e->getMessage() . "</div>";
}

// Inclui o cabeçalho
include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4">Gerenciamento de Pavilhões</h1>

<?php echo $mensagem; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Observações</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pavilhoes)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum pavilhão cadastrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pavilhoes as $pavilhao): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pavilhao['id_pavilhao']); ?></td>
                        <td><?php echo htmlspecialchars($pavilhao['nome']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $pavilhao['status'] == 'Ativo' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($pavilhao['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($pavilhao['observacoes']); ?></td>
                        <td>
                            <form method="POST" action="pavilhoes.php" style="display:inline;">
                                <input type="hidden" name="id_pavilhao" value="<?php echo $pavilhao['id_pavilhao']; ?>">
                                <?php if ($pavilhao['status'] == 'Ativo'): ?>
                                    <button type="submit" name="status" value="Desativar" class="btn btn-sm btn-danger">Desativar</button>
                                <?php else: ?>
                                    <button type="submit" name="status" value="Ativar" class="btn btn-sm btn-success">Ativar</button>
                                <?php endif; ?>
                            </form>
                        </td>
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
