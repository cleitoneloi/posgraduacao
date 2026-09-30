<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';
$u = exigir_login();
$pdo = db();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    switch ($_POST['acao'] ?? '') {
        case 'novo':
            $usuario = strtolower(trim((string) $_POST['usuario']));
            $nome = trim((string) $_POST['nome']);
            $senha = (string) $_POST['senha'];
            if (!preg_match('/^[a-z0-9._-]{3,40}$/', $usuario) || $nome === '') {
                $erro = 'Informe nome e usuário válidos (3-40 caracteres: a-z, 0-9, . _ -).';
            } elseif ($m = senha_valida($senha)) {
                $erro = $m;
            } elseif ($pdo->query('SELECT COUNT(*) FROM usuarios WHERE usuario = ' . $pdo->quote($usuario))->fetchColumn()) {
                $erro = 'Este usuário já existe.';
            } else {
                $pdo->prepare('INSERT INTO usuarios (usuario, nome, senha_hash, criado_em) VALUES (?,?,?,?)')
                    ->execute([$usuario, $nome, password_hash($senha, PASSWORD_DEFAULT), agora()]);
                auditar('criou administrador', $usuario);
                flash('Administrador criado.');
                redirecionar('usuarios.php');
            }
            break;
        case 'senha':
            $id = (int) $_POST['id'];
            $senha = (string) $_POST['senha'];
            if ($m = senha_valida($senha)) {
                $erro = $m;
            } else {
                $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($senha, PASSWORD_DEFAULT), $id]);
                auditar('alterou senha', "id $id");
                flash('Senha alterada.');
                redirecionar('usuarios.php');
            }
            break;
        case 'excluir':
            $id = (int) $_POST['id'];
            if ($id === (int) $u['id']) {
                $erro = 'Você não pode excluir a si mesmo.';
            } else {
                $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
                auditar('excluiu administrador', "id $id");
                flash('Administrador removido.');
                redirecionar('usuarios.php');
            }
            break;
    }
}
$lista = $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();
admin_topo('Administradores', $u);
?>
<div class="head"><h1>Administradores</h1></div>
<?php if ($erro): ?><div class="alert alert-erro"><?= e($erro) ?></div><?php endif; ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>Nome</th><th>Usuário</th><th>Nova senha</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($lista as $a): ?>
    <tr>
      <td><?= e($a['nome']) ?><?= $a['id'] == $u['id'] ? ' <span class="pill pill-ok">você</span>' : '' ?></td>
      <td><?= e($a['usuario']) ?></td>
      <td>
        <form method="post" class="inline"><?= csrf_campo() ?><input type="hidden" name="acao" value="senha"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
          <input type="password" name="senha" placeholder="Nova senha" autocomplete="new-password" required>
          <button class="btn btn-sm btn-ghost">Alterar</button></form>
      </td>
      <td><?php if ($a['id'] != $u['id']): ?>
        <form method="post" class="inline" onsubmit="return confirm('Remover este administrador?')"><?= csrf_campo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
          <button class="btn btn-sm btn-danger">Remover</button></form><?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>

<h2>Novo administrador</h2>
<form method="post" class="card form-grid">
  <?= csrf_campo() ?><input type="hidden" name="acao" value="novo">
  <label>Nome <input name="nome" required></label>
  <label>Usuário <input name="usuario" required autocomplete="off"></label>
  <label class="full">Senha (mín. 10, letras e números) <input type="password" name="senha" required autocomplete="new-password"></label>
  <div class="full"><button class="btn btn-primary">Criar</button></div>
</form>
<?php admin_rodape();
