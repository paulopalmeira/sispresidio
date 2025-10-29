<?php
// sispresidio/includes/rodape.php
?>
</div> <footer class="footer text-center">
    <div class="container">
        <span class="text-muted">Sispresidio &copy; Atividade de Entrega da matéria <strong>PIT - Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025</strong></span>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>

<?php
// Se a página que incluiu o rodapé definir scripts adicionais, eles serão impressos aqui.
if (isset($scripts_adicionais)) {
    echo $scripts_adicionais;
}
?>

</body>
</html>