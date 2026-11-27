<?php
declare(strict_types=1);
require __DIR__ . '/_auth.php';
require_login();
$pdo = db();

/* ---------- Acciones (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' && $id > 0) {
        $estatus = in_array($_POST['estatus'] ?? '', ESTATUS, true) ? $_POST['estatus'] : 'nuevo';
        $monto = trim((string)($_POST['monto'] ?? ''));
        $monto = $monto === '' ? null : (float)str_replace([',', '$'], '', $monto);
        $notas = mb_substr(trim((string)($_POST['notas'] ?? '')), 0, 5000);
        $pdo->prepare('UPDATE prospectos SET estatus = ?, monto = ?, notas = ? WHERE id = ?')
            ->execute([$estatus, $monto, $notas ?: null, $id]);
        $_SESSION['flash'] = 'Prospecto actualizado.';
    } elseif ($accion === 'eliminar' && $id > 0) {
        $pdo->prepare('DELETE FROM prospectos WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = 'Prospecto eliminado.';
    }
    header('Location: index.php?' . http_build_query(array_intersect_key($_GET, array_flip(['estatus', 'q', 'p']))));
    exit;
}

/* ---------- Filtros ---------- */
$fEstatus = in_array($_GET['estatus'] ?? '', ESTATUS, true) ? $_GET['estatus'] : '';
$fQ = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100);
$pagina = max(1, (int)($_GET['p'] ?? 1));
$porPagina = 30;

$where = [];
$params = [];
if ($fEstatus !== '') { $where[] = 'estatus = ?'; $params[] = $fEstatus; }
if ($fQ !== '') {
    $where[] = '(nombre LIKE ? OR telefono LIKE ? OR email LIKE ? OR servicio LIKE ? OR mensaje LIKE ?)';
    array_push($params, ...array_fill(0, 5, '%' . $fQ . '%'));
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM prospectos $sqlWhere");
$st->execute($params);
$total = (int)$st->fetchColumn();
$paginas = max(1, (int)ceil($total / $porPagina));
$offset = ($pagina - 1) * $porPagina;

$st = $pdo->prepare("SELECT * FROM prospectos $sqlWhere ORDER BY creado_en DESC LIMIT $porPagina OFFSET $offset");
$st->execute($params);
$filas = $st->fetchAll();

/* ---------- Métricas ---------- */
$conteo = array_fill_keys(ESTATUS, 0);
foreach ($pdo->query('SELECT estatus, COUNT(*) n FROM prospectos GROUP BY estatus') as $r) {
    $conteo[$r['estatus']] = (int)$r['n'];
}
$mes = (int)$pdo->query("SELECT COUNT(*) FROM prospectos WHERE creado_en >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
$vendido = (float)$pdo->query("SELECT COALESCE(SUM(monto),0) FROM prospectos WHERE estatus = 'ganado'")->fetchColumn();
$totalTodos = array_sum($conteo);
$conversion = $totalTodos ? round($conteo['ganado'] / $totalTodos * 100) : 0;

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function waLink(string $tel): string {
    $d = preg_replace('/\D/', '', $tel);
    if (strlen($d) === 10) $d = '52' . $d;
    return 'https://wa.me/' . $d;
}
function qs(array $extra): string {
    global $fEstatus, $fQ;
    return '?' . http_build_query(array_filter(['estatus' => $fEstatus, 'q' => $fQ] + $extra, fn($v) => $v !== ''));
}
?>
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Prospectos · Panel Safe Pixel</title>
  <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <header class="top">
    <div class="brand"><span class="brand-mark"><i></i><i></i><i></i><i></i></span>Safe<b>Pixel</b> <small>Panel</small></div>
    <div class="top-right">
      <span class="muted">Hola, <?= e($_SESSION['usuario']) ?></span>
      <a class="btn btn-ghost" href="../" target="_blank">Ver sitio</a>
      <form method="post" action="logout.php"><?= csrf_field() ?><button class="btn btn-ghost">Salir</button></form>
    </div>
  </header>

  <main class="wrap">
    <?php if ($flash): ?><p class="ok"><?= e($flash) ?></p><?php endif; ?>

    <section class="stats">
      <div class="stat"><b><?= $mes ?></b><span>Prospectos este mes</span></div>
      <div class="stat"><b><?= $conteo['nuevo'] ?></b><span>Sin contactar</span></div>
      <div class="stat"><b><?= $conteo['cotizado'] ?></b><span>Cotizados</span></div>
      <div class="stat"><b><?= $conteo['ganado'] ?></b><span>Ganados (<?= $conversion ?>%)</span></div>
      <div class="stat"><b>$<?= number_format($vendido, 0) ?></b><span>Vendido (MXN)</span></div>
    </section>

    <section class="toolbar">
      <nav class="chips">
        <a href="<?= e(qs(['estatus' => '', 'p' => ''])) ?>" class="<?= $fEstatus === '' ? 'on' : '' ?>">Todos (<?= $totalTodos ?>)</a>
        <?php foreach (ESTATUS as $s): ?>
          <a href="<?= e(qs(['estatus' => $s, 'p' => ''])) ?>" class="<?= $fEstatus === $s ? 'on' : '' ?>"><?= ucfirst($s) ?> (<?= $conteo[$s] ?>)</a>
        <?php endforeach; ?>
      </nav>
      <form class="search" method="get">
        <?php if ($fEstatus): ?><input type="hidden" name="estatus" value="<?= e($fEstatus) ?>"><?php endif; ?>
        <input name="q" value="<?= e($fQ) ?>" placeholder="Buscar nombre, teléfono, servicio…">
        <button class="btn">Buscar</button>
        <a class="btn btn-ghost" href="exportar.php<?= e(qs([])) ?>">Exportar a Excel</a>
      </form>
    </section>

    <?php if (!$filas): ?>
      <p class="empty">No hay prospectos con este filtro todavía.</p>
    <?php endif; ?>

    <div class="list">
      <?php foreach ($filas as $f): ?>
        <article class="lead est-<?= e($f['estatus']) ?>">
          <div class="lead-main">
            <div class="lead-head">
              <h2><?= e($f['nombre']) ?></h2>
              <span class="tag"><?= e($f['estatus']) ?></span>
            </div>
            <p class="meta">
              <?= e($f['servicio']) ?> · <?= e(date('d/m/Y H:i', strtotime($f['creado_en']))) ?>
              <?php if ($f['origen']): ?> · <span class="muted"><?= e($f['origen']) ?></span><?php endif; ?>
            </p>
            <p class="contact">
              <a href="<?= e(waLink($f['telefono'])) ?>" target="_blank" rel="noopener">WhatsApp <?= e($f['telefono']) ?></a>
              <?php if ($f['email']): ?> · <a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a><?php endif; ?>
            </p>
            <?php if ($f['mensaje']): ?><p class="msg"><?= nl2br(e($f['mensaje'])) ?></p><?php endif; ?>
          </div>
          <form method="post" class="lead-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
            <label>Estatus
              <select name="estatus">
                <?php foreach (ESTATUS as $s): ?>
                  <option value="<?= $s ?>" <?= $s === $f['estatus'] ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Monto (MXN)<input name="monto" inputmode="decimal" value="<?= $f['monto'] !== null ? e((string)$f['monto']) : '' ?>" placeholder="0.00"></label>
            <label class="full">Notas internas<textarea name="notas" rows="2" placeholder="Llamar el lunes, envió logo…"><?= e($f['notas']) ?></textarea></label>
            <div class="actions">
              <button class="btn" name="accion" value="guardar">Guardar</button>
              <button class="btn btn-danger" name="accion" value="eliminar" onclick="return confirm('¿Eliminar este prospecto?')">Eliminar</button>
            </div>
          </form>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if ($paginas > 1): ?>
      <nav class="pager">
        <?php for ($i = 1; $i <= $paginas; $i++): ?>
          <a href="<?= e(qs(['p' => $i])) ?>" class="<?= $i === $pagina ? 'on' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </main>
</body>
</html>
