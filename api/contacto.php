<?php
// ============================================================
//  CasaBela — API de Contacto
//  Ficheiro: api/contacto.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');

define('EMAIL_ADMIN', 'info@casabela.es');   // ← altere aqui
define('NOME_SITE',   'CasaBela');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'erro'=>'Método não suportado.']);
    exit;
}

$nome     = htmlspecialchars(trim($_POST['nome']     ?? ''));
$email    = filter_var(trim($_POST['email']    ?? ''), FILTER_SANITIZE_EMAIL);
$telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''));
$mensagem = htmlspecialchars(trim($_POST['mensagem'] ?? ''));

if (!$nome || !$email || !$mensagem) {
    echo json_encode(['ok'=>false,'erro'=>'Preencha todos os campos obrigatórios.']);
    exit;
}

$subject = "[" . NOME_SITE . "] Mensagem de contacto — {$nome}";
$body    = "Nova mensagem de contacto:\n\n"
         . "Nome:     {$nome}\n"
         . "Email:    {$email}\n"
         . "Telefone: {$telefone}\n\n"
         . "Mensagem:\n{$mensagem}\n";

$ok = @mail(EMAIL_ADMIN, $subject, $body,
    "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'casabela.es') . "\r\n"
    . "Reply-To: {$email}\r\n");

echo json_encode(['ok' => true]);
