<?php
declare(strict_types=1);

function admin_topo(string $titulo, ?array $usuario = null): void
{
    cabecalhos_seguranca();
    header('Cache-Control: no-store');
    $f = flash();
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · Admin Pós-Graduação Univiçosa</title>
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php if ($usuario): ?>
<header class="top">
  <a class="brand" href="index.php"><span>UV</span> Admin · Pós-Graduação</a>
  <nav>
    <a href="index.php">Cursos</a>
    <a href="usuarios.php">Administradores</a>
    <a href="configuracoes.php">Configurações</a>
    <a href="auditoria.php">Histórico</a>
    <a href="../" target="_blank" rel="noopener">Ver site ↗</a>
  </nav>
  <form method="post" action="logout.php" class="sair">
    <?= csrf_campo() ?>
    <span><?= e($usuario['nome']) ?></span>
    <button class="btn btn-ghost">Sair</button>
  </form>
</header>
<?php endif; ?>
<main class="wrap">
<?php if ($f): ?><div class="alert alert-<?= e($f['tipo']) ?>" role="status"><?= e($f['msg']) ?></div><?php endif; ?>
<?php
}

function admin_rodape(): void
{
    echo "</main>\n</body>\n</html>";
}
