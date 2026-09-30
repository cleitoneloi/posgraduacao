<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';

if (usuario_logado()) {
    redirecionar('index.php');
}
if (db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() == 0) {
    redirecionar('setup.php');
}

const MAX_TENTATIVAS = 5;
const JANELA_SEG = 900; // 15 min

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0';
    $pdo = db();
    $pdo->prepare('DELETE FROM tentativas_login WHERE quando < ?')->execute([time() - JANELA_SEG]);
    $st = $pdo->prepare('SELECT COUNT(*) FROM tentativas_login WHERE ip = ?');
    $st->execute([$ip]);
    if ((int) $st->fetchColumn() >= MAX_TENTATIVAS) {
        $erro = 'Muitas tentativas. Aguarde 15 minutos e tente novamente.';
    } else {
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ?');
        $st->execute([strtolower(trim((string) ($_POST['usuario'] ?? '')))]);
        $u = $st->fetch();
        // verifica sempre um hash para não vazar se o usuário existe (timing)
        $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyz0123';
        $ok = password_verify((string) ($_POST['senha'] ?? ''), $hash) && $u;
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int) $u['id'];
            $pdo->prepare('DELETE FROM tentativas_login WHERE ip = ?')->execute([$ip]);
            auditar('login');
            redirecionar('index.php');
        }
        $pdo->prepare('INSERT INTO tentativas_login (ip, quando) VALUES (?,?)')->execute([$ip, time()]);
        $erro = 'Usuário ou senha inválidos.';
    }
}
admin_topo('Entrar');
?>
<section class="card narrow">
  <h1>Acesso administrativo</h1>
  <?php if ($erro): ?><div class="alert alert-erro"><?= e($erro) ?></div><?php endif; ?>
  <form method="post" autocomplete="on">
    <?= csrf_campo() ?>
    <label>Usuário <input name="usuario" required autofocus autocomplete="username"></label>
    <label>Senha <input type="password" name="senha" required autocomplete="current-password"></label>
    <button class="btn btn-primary">Entrar</button>
  </form>
</section>
<?php admin_rodape();
