<?php
/** Descarga los prospectos (con el filtro actual) en CSV que abre Excel. */
declare(strict_types=1);
require __DIR__ . '/_auth.php';
require_login();

$where = [];
$params = [];
if (in_array($_GET['estatus'] ?? '', ESTATUS, true)) { $where[] = 'estatus = ?'; $params[] = $_GET['estatus']; }
$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100);
if ($q !== '') {
    $where[] = '(nombre LIKE ? OR telefono LIKE ? OR email LIKE ? OR servicio LIKE ? OR mensaje LIKE ?)';
    array_push($params, ...array_fill(0, 5, "%$q%"));
}
$st = db()->prepare('SELECT id, creado_en, nombre, telefono, email, servicio, mensaje, estatus, monto, notas, origen FROM prospectos '
    . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY creado_en DESC');
$st->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="prospectos-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete acentos
fputcsv($out, ['ID', 'Fecha', 'Nombre', 'Teléfono', 'Correo', 'Servicio', 'Mensaje', 'Estatus', 'Monto', 'Notas', 'Origen']);
while ($r = $st->fetch()) {
    // Evita fórmulas maliciosas al abrir en Excel
    $r = array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v, $r);
    fputcsv($out, array_values($r));
}
fclose($out);
