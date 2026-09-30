<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';
$u = exigir_login();
$log = db()->query('SELECT * FROM auditoria ORDER BY id DESC LIMIT 200')->fetchAll();
admin_topo('Histórico', $u);
?>
<div class="head"><h1>Histórico de alterações</h1><p class="muted">Últimas 200 ações</p></div>
<div class="tabela-wrap"><table>
  <thead><tr><th>Quando</th><th>Usuário</th><th>Ação</th><th>Detalhe</th></tr></thead>
  <tbody>
  <?php foreach ($log as $l): ?>
    <tr><td><?= e(date('d/m/Y H:i', strtotime($l['quando']))) ?></td><td><?= e($l['usuario']) ?></td><td><?= e($l['acao']) ?></td><td><?= e($l['detalhe']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php admin_rodape();
