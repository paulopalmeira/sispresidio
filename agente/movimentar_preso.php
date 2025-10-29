<?php
require_once __DIR__ . '/../includes/verifica_sessao.php';
require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';
$preso = null;
$id_preso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$celas_destino = [];

function buscarCelasDestino($pdo) {
    $sql = "
        SELECT 
            c.id_cela, 
            c.numero, 
            c.capacidade, 
            pa.nome AS pavilhao_nome,
            (SELECT COUNT(*) FROM presos WHERE id_cela_atual = c.id_cela) AS ocupacao
        FROM celas c
        JOIN pavilhoes pa ON c.id_pavilhao = pa.id_pavilhao
        WHERE 
            pa.status = 'Ativo' AND
            c.capacidade > (SELECT COUNT(*) FROM presos WHERE id_cela_atual = c.id_cela)
        ORDER BY pa.nome, c.numero
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($id_preso) {
    try {
        $sql_preso = "
            SELECT 
                p.id_preso, p.nome, p.matricula_preso, p.id_cela_atual,
                c.numero AS cela_origem_numero, pa.nome AS pavilhao_origem_nome
            FROM presos p
            LEFT JOIN celas c ON p.id_cela_atual = c.id_cela
            LEFT JOIN pavilhoes pa ON c.id_pavilhao = pa.id_pavilhao
            WHERE p.id_preso = :id_preso
        ";
        $stmt = $pdo->prepare($sql_preso);
        $stmt->execute([':id_preso' => $id_preso]);
        $preso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preso) {
            $mensagem = "<div class='alert alert-danger'>Preso não encontrado.</div>";
            $id_preso = null;
        } else {
            $celas_destino = buscarCelasDestino($pdo);
        }
    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao carregar dados do preso: " . $e->getMessage() . "</div>";
        $id_preso = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_preso_movimentar'])) {
    $id_preso_movimentar = filter_input(INPUT_POST, 'id_preso_movimentar', FILTER_VALIDATE_INT);
    $id_cela_destino = filter_input(INPUT_POST, 'id_cela_destino', FILTER_VALIDATE_INT);
    $motivo = trim($_POST['motivo']);
    $id_usuario_responsavel = $_SESSION['id_usuario'];
    $id_cela_origem = filter_input(INPUT_POST, 'id_cela_origem', FILTER_VALIDATE_INT);

    if (!$id_preso_movimentar || !$id_cela_destino || empty($motivo)) {
        $mensagem = "<div class='alert alert-danger'>Todos os campos obrigatórios (Preso, Cela de Destino e Motivo) devem ser preenchidos.</div>";
    } else {
        try {
            $pdo->beginTransaction();
            $sql_mov = "
                INSERT INTO movimentacoes 
                    (data_hora, motivo, id_preso, id_cela_origem, id_cela_destino, id_usuario_responsavel)
                VALUES
                    (NOW(), :motivo, :id_preso, :id_cela_origem, :id_cela_destino, :id_usuario_responsavel)
            ";
            $stmt_mov = $pdo->prepare($sql_mov);
            $stmt_mov->execute([
                ':motivo' => $motivo,
                ':id_preso' => $id_preso_movimentar,
                ':id_cela_origem' => $id_cela_origem ?: null,
                ':id_cela_destino' => $id_cela_destino,
                ':id_usuario_responsavel' => $id_usuario_responsavel
            ]);
            $sql_preso_upd = "UPDATE presos SET id_cela_atual = :id_cela_destino WHERE id_preso = :id_preso";
            $stmt_preso_upd = $pdo->prepare($sql_preso_upd);
            $stmt_preso_upd->execute([':id_cela_destino' => $id_cela_destino, ':id_preso' => $id_preso_movimentar]);
            $pdo->commit();
            header("Location: presos.php?movimentado=success");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagem = "<div class='alert alert-danger'>Erro ao realizar a movimentação: " . $e->getMessage() . "</div>";
            $id_preso = $id_preso_movimentar;
        }
    }
}

try {
    $stmt_todos_presos = $pdo->query("SELECT id_preso, nome, matricula_preso FROM presos ORDER BY nome ASC");
    $todos_presos = $stmt_todos_presos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todos_presos = [];
    $mensagem = "<div class='alert alert-danger'>Erro ao carregar lista de presos: " . $e->getMessage() . "</div>";
}

include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4">Movimentação de Presos</h1>
<?php echo $mensagem; ?>
<form method="POST" action="movimentar_preso.php">
    <div class="card mb-4">
        <div class="card-header">Seleção do Preso</div>
        <div class="card-body">
            <div class="form-group">
                <label for="id_preso_selecao">Selecione o Preso a ser Movimentado</label>
                <select id="id_preso_selecao" name="id_preso_selecao" class="form-control" onchange="window.location.href='movimentar_preso.php?id=' + this.value">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($todos_presos as $p): ?>
                        <option value="<?php echo $p['id_preso']; ?>" <?php echo $id_preso == $p['id_preso'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars("{$p['nome']} ({$p['matricula_preso']})"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php if ($preso): ?>
    <input type="hidden" name="id_preso_movimentar" value="<?php echo $preso['id_preso']; ?>">
    <input type="hidden" name="id_cela_origem" value="<?php echo $preso['id_cela_atual']; ?>">
    <div class="card mb-4">
        <div class="card-header">Detalhes da Movimentação para: **<?php echo htmlspecialchars($preso['nome']); ?>**</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Cela de Origem Atual</label>
                    <p class="form-control-static">
                        **<?php echo htmlspecialchars($preso['cela_origem_numero'] ?? 'N/A'); ?>** (Pavilhão: **<?php echo htmlspecialchars($preso['pavilhao_origem_nome'] ?? 'N/A'); ?>**)
                    </p>
                </div>
                <div class="form-group col-md-6">
                    <label for="id_cela_destino">Nova Cela de Destino *</label>
                    <select id="id_cela_destino" name="id_cela_destino" class="form-control" required>
                        <option value="">-- Selecione a Cela de Destino --</option>
                        <?php foreach ($celas_destino as $cela): ?>
                            <?php 
                                $vagas = $cela['capacidade'] - $cela['ocupacao'];
                                if ($cela['id_cela'] != $preso['id_cela_atual']): 
                            ?>
                                <option value="<?php echo $cela['id_cela']; ?>">
                                    <?php echo htmlspecialchars("Pavilhão: {$cela['pavilhao_nome']} | Cela: {$cela['numero']} (Vagas: {$vagas})"); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="motivo">Motivo da Movimentação *</label>
                <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-green btn-lg">Realizar Movimentação</button>
    <a href="presos.php" class="btn btn-secondary btn-lg">Voltar para a Lista</a>
    <?php endif; ?>
</form>
<?php
include_once __DIR__ . '/../includes/rodape.php';
?>