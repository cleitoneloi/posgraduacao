<?php
declare(strict_types=1);
require __DIR__ . '/../lib/bootstrap.php';
require __DIR__ . '/../lib/layout.php';
$u = exigir_login();
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
$c = ['nome' => '', 'area' => 'saude', 'modalidade' => 'Híbrido', 'duracao' => '', 'carga_horaria' => '',
      'descricao' => '', 'publico_alvo' => '', 'investimento' => '', 'inicio' => '', 'link_inscricao' => '',
      'cod_rm' => '', 'destaque' => 0, 'ativo' => 1];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
    $st->execute([$id]);
    $c = $st->fetch() ?: redirecionar('index.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $t = fn(string $k, int $max = 200) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $novo = [
        'nome' => $t('nome', 150), 'area' => $t('area'), 'modalidade' => $t('modalidade'),
        'duracao' => $t('duracao', 60), 'carga_horaria' => $t('carga_horaria', 60),
        'descricao' => $t('descricao', 2000), 'publico_alvo' => $t('publico_alvo', 500),
        'investimento' => $t('investimento', 200), 'inicio' => $t('inicio', 100),
        'link_inscricao' => $t('link_inscricao', 500), 'cod_rm' => $t('cod_rm', 40),
        'destaque' => isset($_POST['destaque']) ? 1 : 0, 'ativo' => isset($_POST['ativo']) ? 1 : 0,
    ];
    if ($novo['nome'] === '') {
        $erro = 'Informe o nome do curso.';
    } elseif (!isset(AREAS[$novo['area']]) || !in_array($novo['modalidade'], MODALIDADES, true)) {
        $erro = 'Área ou modalidade inválida.';
    } elseif ($novo['link_inscricao'] !== '' && !preg_match('#^https?://#i', $novo['link_inscricao'])) {
        $erro = 'O link de inscrição deve começar com http:// ou https://.';
    }
    if (!$erro) {
        $slug = slug_unico(slugify($novo['nome']), $id);
        if ($id) {
            $pdo->prepare('UPDATE cursos SET nome=:nome, slug=:slug, area=:area, modalidade=:modalidade, duracao=:duracao,
                carga_horaria=:carga_horaria, descricao=:descricao, publico_alvo=:publico_alvo, investimento=:investimento,
                inicio=:inicio, link_inscricao=:link_inscricao, cod_rm=:cod_rm, destaque=:destaque, ativo=:ativo,
                atualizado_em=:agora WHERE id=:id')
                ->execute($novo + ['slug' => $slug, 'agora' => agora(), 'id' => $id]);
            auditar('editou curso', $novo['nome']);
            flash('Curso atualizado.');
        } else {
            $ordem = (int) $pdo->query('SELECT COALESCE(MAX(ordem),0)+1 FROM cursos')->fetchColumn();
            $pdo->prepare('INSERT INTO cursos (nome, slug, area, modalidade, duracao, carga_horaria, descricao, publico_alvo,
                investimento, inicio, link_inscricao, cod_rm, destaque, ativo, ordem, criado_em, atualizado_em)
                VALUES (:nome,:slug,:area,:modalidade,:duracao,:carga_horaria,:descricao,:publico_alvo,:investimento,
                :inicio,:link_inscricao,:cod_rm,:destaque,:ativo,:ordem,:agora,:agora2)')
                ->execute($novo + ['slug' => $slug, 'ordem' => $ordem, 'agora' => agora(), 'agora2' => agora()]);
            auditar('criou curso', $novo['nome']);
            flash('Curso cadastrado.');
        }
        redirecionar('index.php');
    }
    $c = $novo + ['id' => $id];
}

$campo = fn(string $k) => e((string) ($c[$k] ?? ''));
admin_topo($id ? 'Editar curso' : 'Novo curso', $u);
?>
<div class="head"><h1><?= $id ? 'Editar curso' : 'Novo curso' ?></h1><a class="btn btn-ghost" href="index.php">← Voltar</a></div>
<?php if ($erro): ?><div class="alert alert-erro"><?= e($erro) ?></div><?php endif; ?>
<form method="post" class="card form-grid">
  <?= csrf_campo() ?>
  <label class="full">Nome do curso * <input name="nome" required maxlength="150" value="<?= $campo('nome') ?>"></label>
  <label>Área *
    <select name="area"><?php foreach (AREAS as $k => $l): ?><option value="<?= $k ?>" <?= $c['area'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
  </label>
  <label>Modalidade *
    <select name="modalidade"><?php foreach (MODALIDADES as $m): ?><option <?= $c['modalidade'] === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select>
  </label>
  <label>Duração <input name="duracao" maxlength="60" placeholder="18 meses" value="<?= $campo('duracao') ?>"></label>
  <label>Carga horária <input name="carga_horaria" maxlength="60" placeholder="360 horas" value="<?= $campo('carga_horaria') ?>"></label>
  <label>Previsão de início <input name="inicio" maxlength="100" placeholder="Fevereiro/2027" value="<?= $campo('inicio') ?>"></label>
  <label>Investimento <input name="investimento" maxlength="200" placeholder="18x de R$ 299,00" value="<?= $campo('investimento') ?>"></label>
  <label class="full">Descrição <textarea name="descricao" rows="4" maxlength="2000"><?= $campo('descricao') ?></textarea></label>
  <label class="full">Público-alvo <textarea name="publico_alvo" rows="2" maxlength="500"><?= $campo('publico_alvo') ?></textarea></label>
  <label>Link de inscrição (opcional) <input type="url" name="link_inscricao" placeholder="https://…" value="<?= $campo('link_inscricao') ?>"></label>
  <label>Código RM (turma/curso) <input name="cod_rm" maxlength="40" placeholder="PG-XXX-01" value="<?= $campo('cod_rm') ?>"></label>
  <div class="full checks">
    <label class="check"><input type="checkbox" name="ativo" <?= $c['ativo'] ? 'checked' : '' ?>> Ativo (visível no site)</label>
    <label class="check"><input type="checkbox" name="destaque" <?= $c['destaque'] ? 'checked' : '' ?>> Destacar no site</label>
  </div>
  <div class="full"><button class="btn btn-primary">Salvar curso</button></div>
</form>
<?php admin_rodape();
