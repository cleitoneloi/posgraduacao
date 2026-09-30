<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';

// Só funciona enquanto NÃO existe nenhum administrador.
if (db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0) {
    redirecionar('login.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $usuario = strtolower(trim((string) $_POST['usuario']));
    $nome = trim((string) $_POST['nome']);
    $senha = (string) $_POST['senha'];
    if (!preg_match('/^[a-z0-9._-]{3,40}$/', $usuario)) {
        $erro = 'Usuário: 3 a 40 caracteres (letras minúsculas, números, ponto, hífen ou sublinhado).';
    } elseif ($nome === '') {
        $erro = 'Informe o nome.';
    } elseif ($senha !== (string) $_POST['senha2']) {
        $erro = 'As senhas não conferem.';
    } elseif ($m = senha_valida($senha)) {
        $erro = $m;
    } else {
        db()->prepare('INSERT INTO usuarios (usuario, nome, senha_hash, criado_em) VALUES (?,?,?,?)')
            ->execute([$usuario, $nome, password_hash($senha, PASSWORD_DEFAULT), agora()]);
        flash('Administrador criado. Faça login.');
        redirecionar('login.php');
    }
}
admin_topo('Configuração inicial');
?>
<section class="card narrow">
  <h1>Configuração inicial</h1>
  <p class="muted">Crie o primeiro administrador. Esta tela deixa de existir assim que ele for criado — faça isso logo após publicar o site.</p>
  <?php if ($erro): ?><div class="alert alert-erro"><?= e($erro) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_campo() ?>
    <label>Nome <input name="nome" required value="<?= e($_POST['nome'] ?? '') ?>"></label>
    <label>Usuário <input name="usuario" required value="<?= e($_POST['usuario'] ?? '') ?>" autocomplete="username"></label>
    <label>Senha (mín. 10, letras e números) <input type="password" name="senha" required autocomplete="new-password"></label>
    <label>Repita a senha <input type="password" name="senha2" required autocomplete="new-password"></label>
    <button class="btn btn-primary">Criar administrador</button>
  </form>
</section>
<?php admin_rodape();
