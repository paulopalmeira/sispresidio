<?php
// sispresidio/agente/editar_preso.php
// Redireciona para o script de cadastro/edição
$id_preso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_preso) {
    header("Location: cadastrar_preso.php?id=" . $id_preso);
    exit();
} else {
    header("Location: presos.php");
    exit();
}
?>
