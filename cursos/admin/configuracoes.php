<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';
$u = exigir_login();
$erro = '';
$cfg = config();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $novo = [
        'whatsapp' => preg_replace('/\D+/', '', (string) $_POST['whatsapp']),
        'email' => trim((string) $_POST['email']),
        'titulo' => mb_substr(trim((string) $_POST['titulo']), 0, 160),
        'subtitulo' => mb_substr(trim((string) $_POST['subtitulo']), 0, 300),
    ];
    if ($novo['email'] !== '' && !filter_var($novo['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($novo['whatsapp'] !== '' && strlen($novo['whatsapp']) < 10) {
        $erro = 'WhatsApp inválido (use DDI+DDD+número, ex.: 5531999999999).';
    } else {
        $st = db()->prepare('INSERT INTO configuracoes (chave, valor) VALUES (?,?) ON CONFLICT(chave) DO UPDATE SET valor = excluded.valor');
        foreach ($novo as $k => $v) {
            $st->execute([$k, $v]);
        }
        auditar('alterou configurações');
        flash('Configurações salvas.');
        redirecionar('configuracoes.php');
    }
    $cfg = $novo;
}
admin_topo('Configurações', $u);
?>
<div class="head"><h1>Configurações do site</h1></div>
<?php if ($erro): ?><div class="alert alert-erro"><?= e($erro) ?></div><?php endif; ?>
<form method="post" class="card form-grid">
  <?= csrf_campo() ?>
  <label class="full">Título principal <input name="titulo" maxlength="160" value="<?= e($cfg['titulo'] ?? '') ?>"></label>
  <label class="full">Subtítulo <textarea name="subtitulo" rows="2" maxlength="300"><?= e($cfg['subtitulo'] ?? '') ?></textarea></label>
  <label>WhatsApp da secretaria (DDI+DDD+número) <input name="whatsapp" value="<?= e($cfg['whatsapp'] ?? '') ?>" placeholder="5531999999999"></label>
  <label>E-mail de contato <input type="email" name="email" value="<?= e($cfg['email'] ?? '') ?>"></label>
  <div class="full"><button class="btn btn-primary">Salvar</button></div>
</form>
<?php admin_rodape();
