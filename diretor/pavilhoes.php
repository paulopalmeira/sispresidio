<?php

$required_role = 'Diretor';
require_once __DIR__ . '/../includes/verifica_sessao.php';

require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pavilhao = filter_input(INPUT_POST, 'id_pavilhao', FILTER_VALIDATE_INT);
    $novo_status_acao = trim($_POST['status'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    
    if (!$id_pavilhao) {
        $mensagem = "<div class='alert alert-danger'>Erro: ID do pavilhão inválido.</div>";
    } else if (empty($motivo)) {
        $mensagem = "<div class='alert alert-danger'>A motivação é obrigatória para alterar o status do pavilhão.</div>";
    } else {
        $novo_status = $novo_status_acao === 'Ativar' ? 'Ativo' : 'Inativo';
        try {
            $stmt = $pdo->prepare("UPDATE pavilhoes SET status = :status, observacoes = :observacoes WHERE id_pavilhao = :id");
            $stmt->execute([
                ':status' => $novo_status,
                ':observacoes' => $motivo,
                ':id' => $id_pavilhao
            ]);
            $mensagem = "<div class='alert alert-success'>Status do Pavilhão atualizado com sucesso para '{$novo_status}'!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger'>Erro ao atualizar status: " . $e->getMessage() . "</div>";
        }
    }
}

try {
    $stmt = $pdo->query("SELECT id_pavilhao, nome, status, observacoes FROM pavilhoes ORDER BY nome ASC");
    $pavilhoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pavilhoes = [];
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar pavilhões: " . $e->getMessage() . "</div>";
}

include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4">Gerenciamento de Pavilhões</h1>

<?php echo $mensagem; ?>

<div class="table-container">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-fixed">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center" style="width: 5%;">ID</th>
                    <th style="width: 30%;">Nome</th>
                    <th class="text-center" style="width: 10%;">Status</th>
                    <th style="width: 45%;">Última Observação/Motivação</th>
                    <th class="text-center" style="width: 10%;">Ação</th>
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
                            <td class="text-center" style="vertical-align: middle;"><?php echo htmlspecialchars($pavilhao['id_pavilhao']); ?></td>
                            <td style="vertical-align: middle;"><?php echo htmlspecialchars($pavilhao['nome']); ?></td>
                            <td class="text-center" style="vertical-align: middle;">
                                <span class="badge badge-<?php echo $pavilhao['status'] == 'Ativo' ? 'success' : 'danger'; ?>">
                                    <?php echo htmlspecialchars($pavilhao['status']); ?>
                                </span>
                            </td>
                            <td title="<?php echo htmlspecialchars($pavilhao['observacoes'] ?? ''); ?>" data-toggle="tooltip" style="vertical-align: middle;">
                                <?php echo htmlspecialchars($pavilhao['observacoes'] ?? ''); ?>
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                <?php if ($pavilhao['status'] == 'Ativo'): ?>
                                    <button type="button" class="btn btn-sm btn-danger acao-btn" data-toggle="modal" data-target="#acaoPavilhaoModal" 
                                            data-id="<?php echo $pavilhao['id_pavilhao']; ?>" 
                                            data-acao="Desativar" 
                                            data-nome="<?php echo htmlspecialchars($pavilhao['nome']); ?>">
                                        Desativar
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-success acao-btn" data-toggle="modal" data-target="#acaoPavilhaoModal" 
                                            data-id="<?php echo $pavilhao['id_pavilhao']; ?>" 
                                            data-acao="Ativar" 
                                            data-nome="<?php echo htmlspecialchars($pavilhao['nome']); ?>">
                                        Ativar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="acaoPavilhaoModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="pavilhoes.php">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">Ação no Pavilhão</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Você está prestes a <strong id="modal-acao-texto"></strong> o pavilhão <strong id="modal-nome-pavilhao"></strong>.</p>
          <input type="hidden" name="id_pavilhao" id="modal-id_pavilhao">
          <input type="hidden" name="status" id="modal-status">
          <div class="form-group">
            <label for="motivo-textarea">Por favor, informe a motivação:*</label>
            <textarea class="form-control" id="motivo-textarea" name="motivo" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Confirmar Ação</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
include_once __DIR__ . '/../includes/rodape.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var acaoButtons = document.querySelectorAll('.acao-btn');
    
    acaoButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var pavilhaoId = this.getAttribute('data-id');
            var acao = this.getAttribute('data-acao');
            var nomePavilhao = this.getAttribute('data-nome');

            var modal = document.getElementById('acaoPavilhaoModal');
            var modalTitle = modal.querySelector('.modal-title');
            var modalAcaoTexto = modal.querySelector('#modal-acao-texto');
            var modalNomePavilhao = modal.querySelector('#modal-nome-pavilhao');
            var modalInputId = modal.querySelector('#modal-id_pavilhao');
            var modalInputStatus = modal.querySelector('#modal-status');
            var modalTextareaMotivo = modal.querySelector('#motivo-textarea');
            var modalSubmitButton = modal.querySelector('.btn-primary');

            modalTextareaMotivo.value = '';

            modalTitle.textContent = acao + ' Pavilhão';
            modalAcaoTexto.textContent = acao.toLowerCase();
            modalNomePavilhao.textContent = nomePavilhao;
            modalInputId.value = pavilhaoId;
            modalInputStatus.value = acao;
            
            if (acao === 'Desativar') {
                modalSubmitButton.classList.remove('btn-success');
                modalSubmitButton.classList.add('btn-danger');
            } else {
                modalSubmitButton.classList.remove('btn-danger');
                modalSubmitButton.classList.add('btn-success');
            }
        });
    });
});
</script>