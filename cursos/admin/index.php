<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';
$u = exigir_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $id = (int) ($_POST['id'] ?? 0);
    $st = $pdo->prepare('SELECT nome, ativo FROM cursos WHERE id = ?');
    $st->execute([$id]);
    $c = $st->fetch();
    if ($c) {
        switch ($_POST['acao'] ?? '') {
            case 'alternar':
                $novo = $c['ativo'] ? 0 : 1;
                $pdo->prepare('UPDATE cursos SET ativo = ?, atualizado_em = ? WHERE id = ?')->execute([$novo, agora(), $id]);
                auditar($novo ? 'ativou curso' : 'desativou curso', $c['nome']);
                flash($novo ? "«{$c['nome']}» ativado e visível no site." : "«{$c['nome']}» desativado — saiu do site.");
                break;
            case 'excluir':
                $pdo->prepare('DELETE FROM cursos WHERE id = ?')->execute([$id]);
                auditar('excluiu curso', $c['nome']);
                flash("«{$c['nome']}» excluído definitivamente.");
                break;
            case 'subir':
            case 'descer':
                // normaliza a ordem (0..n) e troca com o vizinho
                $ids = $pdo->query('SELECT id FROM cursos ORDER BY ordem, id')->fetchAll(PDO::FETCH_COLUMN);
                $i = array_search($id, array_map('intval', $ids), true);
                $j = $_POST['acao'] === 'subir' ? $i - 1 : $i + 1;
                if ($i !== false && isset($ids[$j])) {
                    [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                }
                $up = $pdo->prepare('UPDATE cursos SET ordem = ? WHERE id = ?');
                foreach ($ids as $pos => $cid) {
                    $up->execute([$pos + 1, $cid]);
                }
                break;
        }
    }
    redirecionar('index.php' . (isset($_GET['f']) ? '?f=' . urlencode($_GET['f']) : ''));
}

$filtro = $_GET['f'] ?? 'todos';
$busca = trim((string) ($_GET['q'] ?? ''));
$where = [];
$args = [];
if ($filtro === 'ativos') {
    $where[] = 'ativo = 1';
} elseif ($filtro === 'inativos') {
    $where[] = 'ativo = 0';
}
if ($busca !== '') {
    $where[] = '(nome LIKE ? OR cod_rm LIKE ?)';
    $args[] = $args[] = "%$busca%";
}
$st = $pdo->prepare('SELECT * FROM cursos' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ordem, id');
$st->execute($args);
$cursos = $st->fetchAll();
$tot = $pdo->query('SELECT COUNT(*) total, SUM(ativo) ativos FROM cursos')->fetch();

admin_topo('Cursos', $u);
?>
<div class="head">
  <div>
    <h1>Cursos de Pós-Graduação</h1>
    <p class="muted"><?= (int) $tot['ativos'] ?> ativos no site · <?= (int) $tot['total'] - (int) $tot['ativos'] ?> desativados · <?= (int) $tot['total'] ?> no total</p>
  </div>
  <a class="btn btn-primary" href="curso.php">+ Novo curso</a>
</div>

<form class="filtros" method="get">
  <input type="search" name="q" placeholder="Buscar por nome ou código RM" value="<?= e($busca) ?>">
  <select name="f" onchange="this.form.submit()">
    <?php foreach (['todos' => 'Todos', 'ativos' => 'Ativos', 'inativos' => 'Desativados'] as $k => $l): ?>
      <option value="<?= $k ?>" <?= $filtro === $k ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-ghost">Filtrar</button>
</form>

<div class="tabela-wrap">
<table>
  <thead><tr><th>Curso</th><th>Área</th><th>Modalidade</th><th>Duração</th><th>Status</th><th class="acoes-col">Ações</th></tr></thead>
  <tbody>
  <?php foreach ($cursos as $c): ?>
    <tr class="<?= $c['ativo'] ? '' : 'inativo' ?>">
      <td>
        <strong><?= e($c['nome']) ?></strong><?= $c['destaque'] ? ' <span class="pill pill-gold">Destaque</span>' : '' ?>
        <?php if ($c['cod_rm']): ?><br><small class="muted">RM: <?= e($c['cod_rm']) ?></small><?php endif; ?>
      </td>
      <td><?= e(AREAS[$c['area']] ?? $c['area']) ?></td>
      <td><?= e($c['modalidade']) ?></td>
      <td><?= e($c['duracao']) ?></td>
      <td><span class="pill <?= $c['ativo'] ? 'pill-ok' : 'pill-off' ?>"><?= $c['ativo'] ? 'Ativo' : 'Desativado' ?></span></td>
      <td class="acoes">
        <form method="post">
          <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <button name="acao" value="subir" class="icone" title="Mover para cima" aria-label="Mover para cima">↑</button>
          <button name="acao" value="descer" class="icone" title="Mover para baixo" aria-label="Mover para baixo">↓</button>
          <a class="btn btn-sm btn-ghost" href="curso.php?id=<?= (int) $c['id'] ?>">Editar</a>
          <button name="acao" value="alternar" class="btn btn-sm <?= $c['ativo'] ? 'btn-warn' : 'btn-ok' ?>"
            <?= $c['ativo'] ? 'onclick="return confirm(\'Desativar este curso? Ele sai do site, mas os dados são mantidos.\')"' : '' ?>>
            <?= $c['ativo'] ? 'Desativar' : 'Ativar' ?></button>
          <button name="acao" value="excluir" class="btn btn-sm btn-danger"
            onclick="return confirm('Excluir DEFINITIVAMENTE? Prefira desativar para manter o histórico.')">Excluir</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$cursos): ?><tr><td colspan="6" class="muted">Nenhum curso encontrado.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php admin_rodape();
