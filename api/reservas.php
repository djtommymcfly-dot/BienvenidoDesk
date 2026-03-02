<?php
// ============================================================
//  CasaBela — API de Reservas
//  Ficheiro: api/reservas.php
//  Servidor: IONOS (PHP 7.4+)
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Configuração ──────────────────────────────────────────────
define('FICHEIRO_RESERVAS', __DIR__ . '/../dados/reservas.json');
define('EMAIL_ADMIN',        'info@bienvenidodesk.es');          // ← altere aqui
define('NOME_SITE',          'Bienvenido Desk');
define('SENHA_ADMIN',        'Bienvenido2025');               // ← altere aqui (usada no admin)

// ── Cria pasta/ficheiro se não existe ─────────────────────────
if (!is_dir(__DIR__ . '/../dados')) {
    mkdir(__DIR__ . '/../dados', 0755, true);
}
if (!file_exists(FICHEIRO_RESERVAS)) {
    file_put_contents(FICHEIRO_RESERVAS, json_encode([]));
}

function lerReservas() {
    $conteudo = file_get_contents(FICHEIRO_RESERVAS);
    return json_decode($conteudo, true) ?: [];
}

function gravarReservas($reservas) {
    file_put_contents(FICHEIRO_RESERVAS, json_encode($reservas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ══ GET — retorna datas ocupadas para o calendário ════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $imovel_id = intval($_GET['imovel_id'] ?? 1);
    $action    = $_GET['action'] ?? 'datas';

    if ($action === 'datas') {
        $reservas = lerReservas();
        $datas = [];
        foreach ($reservas as $r) {
            if ((int)$r['imovel_id'] === $imovel_id && $r['estado'] !== 'cancelado') {
                // Gera todas as datas entre entrada e saída
                $cur = new DateTime($r['entrada_raw']);
                $fim = new DateTime($r['saida_raw']);
                while ($cur <= $fim) {
                    $datas[] = $cur->format('Y-m-d');
                    $cur->modify('+1 day');
                }
            }
        }
        echo json_encode(['ok' => true, 'datas' => array_unique(array_values($datas))]);
    }

    elseif ($action === 'lista') {
        // Para o admin (requer sessão)
        session_start();
        if (empty($_SESSION['admin_ok'])) {
            http_response_code(401);
            echo json_encode(['ok'=>false,'erro'=>'Não autorizado']);
            exit;
        }
        $reservas = lerReservas();
        // Ordena por data de criação DESC
        usort($reservas, fn($a,$b) => strcmp($b['criado_em'] ?? '', $a['criado_em'] ?? ''));
        echo json_encode(['ok'=>true,'reservas'=>$reservas]);
    }
    exit;
}

// ══ POST — nova reserva ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Acção do admin (confirmar/cancelar)
    if (isset($_POST['admin_action'])) {
        session_start();
        if (empty($_SESSION['admin_ok'])) {
            echo json_encode(['ok'=>false,'erro'=>'Não autorizado']); exit;
        }
        $reservas = lerReservas();
        $id_reserva = $_POST['reserva_id'] ?? '';
        foreach ($reservas as &$r) {
            if ($r['id'] === $id_reserva) {
                if ($_POST['admin_action'] === 'confirmar')  $r['estado'] = 'confirmado';
                if ($_POST['admin_action'] === 'cancelar')   $r['estado'] = 'cancelado';
                break;
            }
        }
        gravarReservas($reservas);
        echo json_encode(['ok'=>true]);
        exit;
    }

    // Nova reserva do cliente
    $campos = ['nome','email','telefone','entrada_raw','saida_raw','imovel_id'];
    foreach ($campos as $c) {
        if (empty($_POST[$c])) {
            echo json_encode(['ok'=>false,'erro'=>"Campo '{$c}' obrigatório."]); exit;
        }
    }

    $entrada = $_POST['entrada_raw'];
    $saida   = $_POST['saida_raw'];

    // Valida formato de data
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entrada) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $saida)) {
        echo json_encode(['ok'=>false,'erro'=>'Datas inválidas.']); exit;
    }
    if ($saida <= $entrada) {
        echo json_encode(['ok'=>false,'erro'=>'A data de saída deve ser posterior à entrada.']); exit;
    }

    // Verifica conflito com reservas existentes
    $reservas   = lerReservas();
    $imovel_id  = intval($_POST['imovel_id']);
    $dtEntrada  = new DateTime($entrada);
    $dtSaida    = new DateTime($saida);

    foreach ($reservas as $r) {
        if ((int)$r['imovel_id'] !== $imovel_id) continue;
        if ($r['estado'] === 'cancelado') continue;
        $rE = new DateTime($r['entrada_raw']);
        $rS = new DateTime($r['saida_raw']);
        if ($dtEntrada < $rS && $dtSaida > $rE) {
            echo json_encode(['ok'=>false,'erro'=>'Essas datas já estão reservadas. Por favor escolha outras datas.']);
            exit;
        }
    }

    // Cria reserva
    $noites  = (int)$dtEntrada->diff($dtSaida)->days;
    $nova = [
        'id'          => uniqid('res_'),
        'imovel_id'   => $imovel_id,
        'nome'        => htmlspecialchars(trim($_POST['nome'])),
        'email'       => filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
        'telefone'    => htmlspecialchars(trim($_POST['telefone'])),
        'pessoas'     => intval($_POST['pessoas'] ?? 2),
        'mensagem'    => htmlspecialchars(trim($_POST['mensagem'] ?? '')),
        'entrada_raw' => $entrada,
        'saida_raw'   => $saida,
        'noites'      => $noites,
        'estado'      => 'pendente',
        'criado_em'   => date('Y-m-d H:i:s'),
    ];
    $reservas[] = $nova;
    gravarReservas($reservas);

    // Email para o admin
    $subject_admin = "[" . NOME_SITE . "] Nova reserva — {$nova['nome']}";
    $body_admin = "Nova reserva recebida!\n\n"
        . "Imóvel ID: {$imovel_id}\n"
        . "Nome:      {$nova['nome']}\n"
        . "Email:     {$nova['email']}\n"
        . "Telefone:  {$nova['telefone']}\n"
        . "Entrada:   {$entrada}\n"
        . "Saída:     {$saida}\n"
        . "Noites:    {$noites}\n"
        . "Pessoas:   {$nova['pessoas']}\n"
        . "Mensagem:  {$nova['mensagem']}\n\n"
        . "Gerir em: https://" . ($_SERVER['HTTP_HOST'] ?? 'casabela.es') . "/admin/\n";
    @mail(EMAIL_ADMIN, $subject_admin, $body_admin,
        "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'casabela.es') . "\r\n"
        . "Reply-To: {$nova['email']}\r\n");

    // Email de confirmação para o cliente
    $subject_cliente = "[" . NOME_SITE . "] Pedido de reserva recebido";
    $body_cliente = "Olá {$nova['nome']},\n\n"
        . "Recebemos o seu pedido de reserva!\n\n"
        . "Detalhes:\n"
        . "Entrada: {$entrada}\n"
        . "Saída:   {$saida}\n"
        . "Noites:  {$noites}\n\n"
        . "Entraremos em contacto em breve para confirmar a reserva e enviar os detalhes de pagamento.\n\n"
        . "Obrigado pela sua escolha!\n\n"
        . NOME_SITE . "\n";
    @mail($nova['email'], $subject_cliente, $body_cliente,
        "From: " . EMAIL_ADMIN . "\r\n");

    echo json_encode(['ok'=>true,'id'=>$nova['id']]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'erro'=>'Método não suportado.']);
