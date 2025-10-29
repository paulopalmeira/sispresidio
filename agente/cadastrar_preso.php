<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
$required_role = 'Agente';
require_once __DIR__ . '/../includes/verifica_sessao.php';
require_once __DIR__ . '/../db/conexao.php';

$mensagem = '';
$preso = [
    'nome' => '', 'matricula_preso' => '', 'cpf' => '', 'rg' => '',
    'data_nascimento' => '', 'sexo' => '', 'data_entrada' => date('Y-m-d'),
    'crime_cometido' => '', 'observacoes' => '', 'id_cela_atual' => null
];
$celas_disponiveis = [];

function buscarCelasDisponiveis($pdo, $cela_atual_id = null) {
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
            (c.capacidade > (SELECT COUNT(*) FROM presos WHERE id_cela_atual = c.id_cela) OR c.id_cela = :cela_atual_id)
        ORDER BY pa.nome, c.numero
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cela_atual_id' => $cela_atual_id ?? 0]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$is_edit = false;
$id_preso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_preso) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM presos WHERE id_preso = :id");
        $stmt->execute([':id' => $id_preso]);
        $preso_db = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($preso_db) {
            $preso = array_merge($preso, $preso_db);
        } else {
            $mensagem = "<div class='alert alert-danger'>Preso não encontrado.</div>";
            $is_edit = false;
        }
    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao carregar dados do preso: " . $e->getMessage() . "</div>";
        $is_edit = false;
    }
}

$celas_disponiveis = buscarCelasDisponiveis($pdo, $preso['id_cela_atual']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $matricula_preso = trim($_POST['matricula_preso']);
    $cpf = trim($_POST['cpf']);
    $rg = trim($_POST['rg']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $sexo = trim($_POST['sexo']);
    $data_entrada = trim($_POST['data_entrada']);
    $crime_cometido = trim($_POST['crime_cometido']);
    $observacoes = trim($_POST['observacoes']);
    $id_cela_atual = filter_input(INPUT_POST, 'id_cela_atual', FILTER_VALIDATE_INT);

    if (empty($nome) || empty($matricula_preso) || empty($data_entrada)) {
        $mensagem = "<div class='alert alert-danger'>Os campos Nome, Matrícula e Data de Entrada são obrigatórios.</div>";
    } else {
        try {
            if ($is_edit) {
                $sql = "
                    UPDATE presos SET 
                        nome = :nome, matricula_preso = :matricula_preso, cpf = :cpf, rg = :rg,
                        data_nascimento = :data_nascimento, sexo = :sexo, data_entrada = :data_entrada,
                        crime_cometido = :crime_cometido, observacoes = :observacoes, id_cela_atual = :id_cela_atual
                    WHERE id_preso = :id_preso
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':matricula_preso' => $matricula_preso,
                    ':cpf' => $cpf ?: null,
                    ':rg' => $rg ?: null,
                    ':data_nascimento' => $data_nascimento ?: null,
                    ':sexo' => $sexo ?: null,
                    ':data_entrada' => $data_entrada,
                    ':crime_cometido' => $crime_cometido,
                    ':observacoes' => $observacoes,
                    ':id_cela_atual' => $id_cela_atual,
                    ':id_preso' => $id_preso
                ]);
                $mensagem = "<div class='alert alert-success'>Preso atualizado com sucesso!</div>";
                $preso = array_merge($preso, $_POST);

            } else {
                $sql = "
                    INSERT INTO presos 
                        (nome, matricula_preso, cpf, rg, data_nascimento, sexo, data_entrada, crime_cometido, observacoes, id_cela_atual)
                    VALUES
                        (:nome, :matricula_preso, :cpf, :rg, :data_nascimento, :sexo, :data_entrada, :crime_cometido, :observacoes, :id_cela_atual)
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':matricula_preso' => $matricula_preso,
                    ':cpf' => $cpf ?: null,
                    ':rg' => $rg ?: null,
                    ':data_nascimento' => $data_nascimento ?: null,
                    ':sexo' => $sexo ?: null,
                    ':data_entrada' => $data_entrada,
                    ':crime_cometido' => $crime_cometido,
                    ':observacoes' => $observacoes,
                    ':id_cela_atual' => $id_cela_atual
                ]);
                header("Location: presos.php?msg=success");
                exit();
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $mensagem = "<div class='alert alert-danger'>Erro: Matrícula, CPF ou RG já cadastrado.</div>";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro no banco de dados: " . $e->getMessage() . "</div>";
            }
            $celas_disponiveis = buscarCelasDisponiveis($pdo, $preso['id_cela_atual']);
        }
    }
}

include_once __DIR__ . '/../includes/cabecalho.php';
?>

<h1 class="mb-4"><?php echo $is_edit ? 'Editar Preso' : 'Cadastrar Novo Preso'; ?></h1>

<?php echo $mensagem; ?>

<form method="POST" action="<?php echo $is_edit ? 'cadastrar_preso.php?id=' . $id_preso : 'cadastrar_preso.php'; ?>">
    
    <div class="card mb-4">
        <div class="card-header">Dados Pessoais</div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($preso['nome']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="matricula_preso">Matrícula do Preso *</label>
                    <input type="text" class="form-control" id="matricula_preso" name="matricula_preso" value="<?php echo htmlspecialchars($preso['matricula_preso']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="data_entrada">Data de Entrada *</label>
                    <input type="date" class="form-control" id="data_entrada" name="data_entrada" value="<?php echo htmlspecialchars($preso['data_entrada']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="cpf">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo htmlspecialchars($preso['cpf']); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="rg">RG</label>
                    <input type="text" class="form-control" id="rg" name="rg" value="<?php echo htmlspecialchars($preso['rg']); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($preso['data_nascimento']); ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" class="form-control">
                        <option value="">Selecione</option>
                        <option value="MASCULINO" <?php echo $preso['sexo'] == 'MASCULINO' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="FEMININO" <?php echo $preso['sexo'] == 'FEMININO' ? 'selected' : ''; ?>>Feminino</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">Informações Prisionais</div>
        <div class="card-body">
            <div class="form-group">
                <label for="id_cela_atual">Alocação Inicial/Atual</label>
                <select id="id_cela_atual" name="id_cela_atual" class="form-control">
                    <option value="">Não Alocado (Aguardando)</option>
                    <?php foreach ($celas_disponiveis as $cela): ?>
                        <option value="<?php echo $cela['id_cela']; ?>" 
                                <?php echo $preso['id_cela_atual'] == $cela['id_cela'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars("Pavilhão: {$cela['pavilhao_nome']} | Cela: {$cela['numero']} (Vagas: " . ($cela['capacidade'] - $cela['ocupacao']) . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Apenas celas em pavilhões ativos e com vagas disponíveis são listadas. Se estiver editando, a cela atual é sempre listada.</small>
            </div>
            <div class="form-group">
                <label for="crime_cometido">Crime Comentido</label>
                <textarea class="form-control" id="crime_cometido" name="crime_cometido" rows="3"><?php echo htmlspecialchars($preso['crime_cometido']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($preso['observacoes']); ?></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-green btn-lg"><?php echo $is_edit ? 'Salvar Alterações' : 'Cadastrar Preso'; ?></button>
    <a href="presos.php" class="btn btn-secondary btn-lg">Voltar para a Lista</a>

</form>
<br>
<br>
<br>
<?php
$scripts_adicionais = "
<script>
$(document).ready(function(){
  $('#matricula_preso').mask('000.000-0');
  $('#cpf').mask('000.000.000-00');
  $('#rg').mask('00.000.000-A', {
    translation: {
      'A': {pattern: /[0-9A-Za-z]/}
    }
  });
});
</script>
";

include_once __DIR__ . '/../includes/rodape.php';
?>