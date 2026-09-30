<?php
declare(strict_types=1);

/* =========================================================
   Univiçosa Pós-Graduação — núcleo (banco, sessão, CSRF, helpers)
   Armazenamento: SQLite em cursos/data/pos.sqlite (criado na 1ª execução).
========================================================= */

const AREAS = [
    'saude'   => 'Saúde',
    'exatas'  => 'Exatas & Tecnologia',
    'humanas' => 'Humanas & Direito',
    'gestao'  => 'Gestão & Negócios',
];
const MODALIDADES = ['Presencial', 'Híbrido', 'EAD'];

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    // Recomendado em produção: defina POS_DB_DIR para uma pasta FORA do DocumentRoot.
    $dir = rtrim(getenv('POS_DB_DIR') ?: __DIR__ . '/../data', '/');
    $novo = !file_exists($dir . '/pos.sqlite');
    $pdo = new PDO('sqlite:' . $dir . '/pos.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS cursos (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nome TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  area TEXT NOT NULL,
  modalidade TEXT NOT NULL,
  duracao TEXT NOT NULL DEFAULT '',
  carga_horaria TEXT NOT NULL DEFAULT '',
  descricao TEXT NOT NULL DEFAULT '',
  publico_alvo TEXT NOT NULL DEFAULT '',
  investimento TEXT NOT NULL DEFAULT '',
  inicio TEXT NOT NULL DEFAULT '',
  link_inscricao TEXT NOT NULL DEFAULT '',
  cod_rm TEXT NOT NULL DEFAULT '',
  destaque INTEGER NOT NULL DEFAULT 0,
  ativo INTEGER NOT NULL DEFAULT 1,
  ordem INTEGER NOT NULL DEFAULT 0,
  criado_em TEXT NOT NULL,
  atualizado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS usuarios (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  usuario TEXT NOT NULL UNIQUE,
  nome TEXT NOT NULL,
  senha_hash TEXT NOT NULL,
  criado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS configuracoes (
  chave TEXT PRIMARY KEY,
  valor TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS tentativas_login (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip TEXT NOT NULL,
  quando INTEGER NOT NULL
);
CREATE TABLE IF NOT EXISTS auditoria (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  usuario TEXT NOT NULL,
  acao TEXT NOT NULL,
  detalhe TEXT NOT NULL DEFAULT '',
  quando TEXT NOT NULL
);
SQL);
    if ($novo) {
        seed($pdo);
    }
    return $pdo;
}

function agora(): string
{
    return date('Y-m-d H:i:s');
}

function seed(PDO $pdo): void
{
    // Cursos de EXEMPLO — substitua pelos cursos reais no painel administrativo.
    $exemplos = [
        ['MBA em Gestão Escolar e Docência do Ensino Superior', 'humanas', 'Híbrido', '18 meses', 'PG-DOC-01'],
        ['Pós em Direito Civil e Processual Civil', 'humanas', 'Híbrido', '15 meses', 'PG-DIR-01'],
        ['Pós em Direito Penal e Processual Penal', 'humanas', 'Híbrido', '15 meses', 'PG-DIR-02'],
        ['Pós em Enfermagem em Urgência, Emergência e UTI', 'saude', 'Híbrido', '18 meses', 'PG-ENF-01'],
        ['Pós em Fisioterapia Traumato-Ortopédica', 'saude', 'Híbrido', '18 meses', 'PG-FIS-01'],
        ['Pós em Neuropsicologia', 'saude', 'Híbrido', '18 meses', 'PG-PSI-01'],
        ['Pós em Ortodontia e Estética em Odontologia', 'saude', 'Presencial', '24 meses', 'PG-ODO-01'],
        ['Pós em Clínica e Cirurgia de Pequenos Animais', 'saude', 'Híbrido', '18 meses', 'PG-VET-01'],
        ['Pós em Engenharia de Segurança do Trabalho', 'exatas', 'EAD', '18 meses', 'PG-ENG-01'],
        ['Pós em Ciência de Dados e Inteligência Artificial', 'exatas', 'EAD', '18 meses', 'PG-TEC-01'],
        ['Pós em Segurança da Informação', 'exatas', 'EAD', '18 meses', 'PG-TEC-02'],
        ['MBA em Gestão de Pessoas', 'gestao', 'Híbrido', '15 meses', 'PG-ADM-01'],
        ['MBA em Gestão Financeira e Controladoria', 'gestao', 'Híbrido', '15 meses', 'PG-ADM-02'],
        ['Pós em Perícia e Auditoria Contábil', 'gestao', 'EAD', '15 meses', 'PG-CONT-01'],
    ];
    $st = $pdo->prepare('INSERT INTO cursos (nome, slug, area, modalidade, duracao, descricao, cod_rm, ordem, criado_em, atualizado_em)
                         VALUES (?,?,?,?,?,?,?,?,?,?)');
    foreach ($exemplos as $i => [$nome, $area, $mod, $dur, $cod]) {
        $st->execute([$nome, slugify($nome), $area, $mod, $dur,
            'Especialização lato sensu com foco prático e orientação de TCC.', $cod, $i + 1, agora(), agora()]);
    }
    $cfg = $pdo->prepare('INSERT INTO configuracoes (chave, valor) VALUES (?,?)');
    foreach ([
        'whatsapp' => '5531900000000',
        'email'    => 'posgraduacao@univicosa.com.br',
        'titulo'   => 'Dê o próximo passo na sua carreira com a Pós-Graduação Univiçosa',
        'subtitulo' => 'Especializações e MBAs com corpo docente qualificado e certificado reconhecido pelo MEC.',
    ] as $k => $v) {
        $cfg->execute([$k, $v]);
    }
}

function config(): array
{
    return db()->query('SELECT chave, valor FROM configuracoes')->fetchAll(PDO::FETCH_KEY_PAIR);
}

function slugify(string $s): string
{
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'curso';
}

function slug_unico(string $base, int $ignorarId = 0): string
{
    $slug = $base;
    $n = 2;
    $st = db()->prepare('SELECT COUNT(*) FROM cursos WHERE slug = ? AND id <> ?');
    while (true) {
        $st->execute([$slug, $ignorarId]);
        if ((int) $st->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $n++;
    }
}

/* ---------------- Sessão / CSRF / Auth ---------------- */

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('pos_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function csrf_token(): string
{
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    iniciar_sessao();
    $enviado = $_POST['_csrf'] ?? '';
    $esperado = $_SESSION['csrf'] ?? '';
    if (!is_string($enviado) || $esperado === '' || !hash_equals($esperado, $enviado)) {
        http_response_code(400);
        exit('Requisição inválida (token CSRF). Volte e tente novamente.');
    }
}

function usuario_logado(): ?array
{
    iniciar_sessao();
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $st = db()->prepare('SELECT id, usuario, nome FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    return $st->fetch() ?: null;
}

function exigir_login(): array
{
    $u = usuario_logado();
    if (!$u) {
        if (db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() == 0) {
            redirecionar('setup.php');
        }
        redirecionar('login.php');
    }
    return $u;
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(?string $msg = null, string $tipo = 'ok'): ?array
{
    iniciar_sessao();
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'tipo' => $tipo];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function auditar(string $acao, string $detalhe = ''): void
{
    $u = usuario_logado();
    db()->prepare('INSERT INTO auditoria (usuario, acao, detalhe, quando) VALUES (?,?,?,?)')
        ->execute([$u['usuario'] ?? '-', $acao, $detalhe, agora()]);
}

function senha_valida(string $s): ?string
{
    if (strlen($s) < 10) {
        return 'A senha deve ter pelo menos 10 caracteres.';
    }
    if (!preg_match('/[A-Za-z]/', $s) || !preg_match('/\d/', $s)) {
        return 'A senha deve conter letras e números.';
    }
    return null;
}

function cabecalhos_seguranca(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
