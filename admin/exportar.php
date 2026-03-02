<?php
// ============================================================
//  CasaBela — Exportar Reservas CSV
//  Ficheiro: admin/exportar.php
// ============================================================

session_start();
if (empty($_SESSION['admin_ok'])) {
    header('Location: index.php'); exit;
}

define('FICHEIRO_RESERVAS', __DIR__ . '/../dados/reservas.json');

$reservas = [];
if (file_exists(FICHEIRO_RESERVAS)) {
    $reservas = json_decode(file_get_contents(FICHEIRO_RESERVAS), true) ?: [];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reservas_' . date('Y-m-d') . '.csv"');

$fp = fopen('php://output', 'w');
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel

fputcsv($fp, ['ID','Imóvel','Nome','Email','Telefone','Entrada','Saída','Noites','Pessoas','Estado','Criado Em','Mensagem'], ';');

foreach ($reservas as $r) {
    fputcsv($fp, [
        $r['id']          ?? '',
        $r['imovel_id']   ?? '',
        $r['nome']        ?? '',
        $r['email']       ?? '',
        $r['telefone']    ?? '',
        $r['entrada_raw'] ?? '',
        $r['saida_raw']   ?? '',
        $r['noites']      ?? '',
        $r['pessoas']     ?? '',
        $r['estado']      ?? '',
        $r['criado_em']   ?? '',
        $r['mensagem']    ?? '',
    ], ';');
}
fclose($fp);
