<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
cabecalhos_seguranca();

$cfg = config();
$cursos = db()->query('SELECT * FROM cursos WHERE ativo = 1 ORDER BY destaque DESC, ordem, id')->fetchAll();
$wa = preg_replace('/\D+/', '', $cfg['whatsapp'] ?? '');
function link_whats(string $wa, string $curso = ''): string
{
    $msg = $curso !== '' ? "Olá! Quero saber mais sobre a pós-graduação em $curso na Univiçosa." : 'Olá! Quero saber mais sobre a pós-graduação da Univiçosa.';
    return 'https://wa.me/' . $wa . '?text=' . rawurlencode($msg);
}
$areasUsadas = array_values(array_unique(array_column($cursos, 'area')));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pós-Graduação Univiçosa | Cursos de Especialização e MBA</title>
<meta name="description" content="<?= e($cfg['subtitulo'] ?? '') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<header class="hd">
  <div class="c hd-in">
    <a href="#topo" class="logo"><span>UV</span> Univiçosa <b>Pós-Graduação</b></a>
    <nav><a href="#cursos">Cursos</a><a href="#contato">Contato</a></nav>
  </div>
</header>

<section class="hero" id="topo">
  <div class="c">
    <span class="badge"><?= count($cursos) ?> cursos com inscrições abertas</span>
    <h1><?= e($cfg['titulo'] ?? '') ?></h1>
    <p><?= e($cfg['subtitulo'] ?? '') ?></p>
    <div class="cta">
      <a class="btn btn-gold" href="#cursos">Ver cursos</a>
      <?php if ($wa): ?><a class="btn btn-line" href="<?= e(link_whats($wa)) ?>" target="_blank" rel="noopener">Falar no WhatsApp</a><?php endif; ?>
    </div>
  </div>
</section>

<section class="sec" id="cursos">
  <div class="c">
    <h2>Escolha a sua especialização</h2>
    <?php if (count($areasUsadas) > 1): ?>
    <div class="filters" id="filters">
      <button class="on" data-f="todos">Todos</button>
      <?php foreach ($areasUsadas as $a): ?><button data-f="<?= e($a) ?>"><?= e(AREAS[$a] ?? $a) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid" id="grid">
    <?php foreach ($cursos as $c): ?>
      <article class="card<?= $c['destaque'] ? ' destaque' : '' ?>" data-area="<?= e($c['area']) ?>">
        <div class="tags">
          <span class="tag"><?= e(AREAS[$c['area']] ?? $c['area']) ?></span>
          <?php if ($c['destaque']): ?><span class="tag gold">Destaque</span><?php endif; ?>
        </div>
        <h3><?= e($c['nome']) ?></h3>
        <?php if ($c['descricao']): ?><p><?= e($c['descricao']) ?></p><?php endif; ?>
        <ul class="meta">
          <li>💻 <?= e($c['modalidade']) ?></li>
          <?php if ($c['duracao']): ?><li>⏱ <?= e($c['duracao']) ?></li><?php endif; ?>
          <?php if ($c['carga_horaria']): ?><li>📚 <?= e($c['carga_horaria']) ?></li><?php endif; ?>
          <?php if ($c['inicio']): ?><li>📅 Início: <?= e($c['inicio']) ?></li><?php endif; ?>
          <?php if ($c['investimento']): ?><li>💰 <?= e($c['investimento']) ?></li><?php endif; ?>
        </ul>
        <?php if ($c['publico_alvo']): ?><p class="alvo"><b>Para quem:</b> <?= e($c['publico_alvo']) ?></p><?php endif; ?>
        <?php
          $href = $c['link_inscricao'] !== '' && preg_match('#^https?://#i', $c['link_inscricao']) ? $c['link_inscricao'] : ($wa ? link_whats($wa, $c['nome']) : '#contato');
        ?>
        <a class="btn btn-navy" href="<?= e($href) ?>" target="_blank" rel="noopener">Quero me inscrever</a>
      </article>
    <?php endforeach; ?>
    </div>
    <p class="vazio" id="vazio" <?= $cursos ? 'hidden' : '' ?>>Em breve novos cursos. Fale com a nossa secretaria para saber mais.</p>
  </div>
</section>

<footer class="ft" id="contato">
  <div class="c">
    <h2>Fale com a Secretaria de Pós-Graduação</h2>
    <p>
      <?php if ($wa): ?><a href="<?= e(link_whats($wa)) ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?>
      <?php if (!empty($cfg['email'])): ?> · <a href="mailto:<?= e($cfg['email']) ?>"><?= e($cfg['email']) ?></a><?php endif; ?>
    </p>
    <small>© <?= date('Y') ?> Centro Universitário de Viçosa — Univiçosa. Viçosa/MG.</small>
  </div>
</footer>

<script>
(function(){
  var f=document.getElementById('filters'); if(!f) return;
  f.addEventListener('click',function(e){
    var b=e.target.closest('button'); if(!b) return;
    f.querySelectorAll('button').forEach(function(x){x.classList.toggle('on',x===b)});
    document.querySelectorAll('#grid .card').forEach(function(c){
      c.hidden = b.dataset.f!=='todos' && c.dataset.area!==b.dataset.f;
    });
  });
})();
</script>
</body>
</html>
