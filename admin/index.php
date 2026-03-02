<?php
// ============================================================
//  CasaBela — Painel de Administração
//  Ficheiro: admin/index.php
//  URL: https://casabela.es/admin/
//  Senha: definida em api/reservas.php → SENHA_ADMIN
// ============================================================

session_start();

define('FICHEIRO_RESERVAS', __DIR__ . '/../dados/reservas.json');
define('SENHA_ADMIN', 'casabela2025');  // ← altere aqui (igual ao api/reservas.php)
define('EMAIL_ADMIN', 'info@casabela.es');

function lerReservas() {
    if (!file_exists(FICHEIRO_RESERVAS)) return [];
    return json_decode(file_get_contents(FICHEIRO_RESERVAS), true) ?: [];
}
function gravarReservas($r) {
    file_put_contents(FICHEIRO_RESERVAS, json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ── Login ──────────────────────────────────────────────────────
if (isset($_POST['senha'])) {
    if ($_POST['senha'] === SENHA_ADMIN) {
        $_SESSION['admin_ok'] = true;
    } else {
        $erro_login = 'Senha incorreta.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy(); header('Location: index.php'); exit;
}

// ── Ações sobre reservas ───────────────────────────────────────
if (!empty($_SESSION['admin_ok']) && isset($_POST['action'])) {
    $reservas = lerReservas();
    foreach ($reservas as &$r) {
        if ($r['id'] === $_POST['reserva_id']) {
            if ($_POST['action'] === 'confirmar') $r['estado'] = 'confirmado';
            if ($_POST['action'] === 'cancelar')  $r['estado'] = 'cancelado';
            if ($_POST['action'] === 'pendente')  $r['estado'] = 'pendente';
            break;
        }
    }
    gravarReservas($reservas);
    header('Location: index.php'); exit;
}

// Filtro
$filtro = $_GET['filtro'] ?? 'todos';
$reservas = lerReservas();
usort($reservas, fn($a,$b) => strcmp($b['criado_em'] ?? '', $a['criado_em'] ?? ''));
if ($filtro !== 'todos') {
    $reservas = array_filter($reservas, fn($r) => $r['estado'] === $filtro);
}

$contagem = [
    'todos'     => count(lerReservas()),
    'pendente'  => count(array_filter(lerReservas(), fn($r)=>$r['estado']==='pendente')),
    'confirmado'=> count(array_filter(lerReservas(), fn($r)=>$r['estado']==='confirmado')),
    'cancelado' => count(array_filter(lerReservas(), fn($r)=>$r['estado']==='cancelado')),
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin — CasaBela</title>
  <link rel="stylesheet" href="../style.css"/>
</head>
<body class="admin-body">

<?php if (empty($_SESSION['admin_ok'])): ?>
<!-- ── LOGIN ───────────────────────────────────────────────── -->
<div class="login-wrap">
  <div class="login-box">
    <h2>CasaBela Admin</h2>
    <?php if (!empty($erro_login)): ?>
      <p style="color:red;margin-bottom:1rem"><?= htmlspecialchars($erro_login) ?></p>
    <?php endif ?>
    <form method="post">
      <input type="password" name="senha" placeholder="Senha de acesso" required autofocus/>
      <button type="submit" class="btn">Entrar</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── PAINEL ──────────────────────────────────────────────── -->
<header class="admin-header">
  <h1>⚙️ CasaBela — Administração</h1>
  <a href="../index.html" style="margin-left:auto">← Ver site</a>
  <a href="?logout=1">Sair</a>
</header>

<div class="admin-main">

  <!-- Estatísticas -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem">
    <?php
    $stats = [
      ['Todas', $contagem['todos'],     '#2c1f0e', '#fff'],
      ['Pendentes', $contagem['pendente'],  '#b5773c', '#fff'],
      ['Confirmadas',$contagem['confirmado'],'#155724', '#d4edda'],
      ['Canceladas', $contagem['cancelado'], '#721c24', '#f8d7da'],
    ];
    foreach ($stats as [$label,$n,$bg,$col]):
    ?>
    <div style="background:<?=$bg?>;color:<?=$col?>;padding:1.2rem;border-radius:10px;text-align:center">
      <div style="font-size:2rem;font-weight:700"><?=$n?></div>
      <div style="font-size:.85rem;opacity:.8"><?=$label?></div>
    </div>
    <?php endforeach ?>
  </div>

  <!-- Filtros -->
  <div style="margin-bottom:1rem;display:flex;gap:.5rem;flex-wrap:wrap">
    <?php foreach(['todos'=>'Todas','pendente'=>'Pendentes','confirmado'=>'Confirmadas','cancelado'=>'Canceladas'] as $k=>$v): ?>
    <a href="?filtro=<?=$k?>" class="btn btn-sm" style="<?=$filtro===$k?'':'background:#ccc;color:#333'?>"><?=$v?></a>
    <?php endforeach ?>
  </div>

  <!-- Tabela de reservas -->
  <div class="admin-section">
    <h2>Reservas</h2>
    <?php if (empty($reservas)): ?>
      <p style="color:#999;text-align:center;padding:2rem">Nenhuma reserva encontrada.</p>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table class="reservas-table">
      <thead>
        <tr>
          <th>Data Pedido</th>
          <th>Imóvel</th>
          <th>Nome</th>
          <th>Email / Tel</th>
          <th>Entrada</th>
          <th>Saída</th>
          <th>Noites</th>
          <th>Estado</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reservas as $r): ?>
        <tr>
          <td><?= htmlspecialchars(substr($r['criado_em']??'',0,10)) ?></td>
          <td><?= (int)$r['imovel_id'] ?></td>
          <td><strong><?= htmlspecialchars($r['nome']) ?></strong><br/><small><?= htmlspecialchars($r['mensagem']??'') ?></small></td>
          <td><?= htmlspecialchars($r['email']) ?><br/><?= htmlspecialchars($r['telefone']??'') ?></td>
          <td><?= htmlspecialchars($r['entrada_raw']) ?></td>
          <td><?= htmlspecialchars($r['saida_raw']) ?></td>
          <td><?= (int)$r['noites'] ?></td>
          <td>
            <span class="badge-<?= $r['estado'] ?>">
              <?= ['pendente'=>'Pendente','confirmado'=>'Confirmado','cancelado'=>'Cancelado'][$r['estado']] ?? $r['estado'] ?>
            </span>
          </td>
          <td style="white-space:nowrap">
            <?php if ($r['estado'] !== 'confirmado'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>"/>
              <input type="hidden" name="action" value="confirmar"/>
              <button class="btn-confirmar" onclick="return confirm('Confirmar esta reserva?')">✅ Confirmar</button>
            </form>
            <?php endif ?>
            <?php if ($r['estado'] !== 'cancelado'): ?>
            <form method="post" style="display:inline">
              <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>"/>
              <input type="hidden" name="action" value="cancelar"/>
              <button class="btn-cancelar" onclick="return confirm('Cancelar esta reserva?')">✕ Cancelar</button>
            </form>
            <?php endif ?>
            <a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="font-size:.8rem;margin-left:.3rem">✉️ Email</a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    </div>
    <?php endif ?>
  </div>

  <!-- Exportar CSV -->
  <div class="admin-section">
    <h2>Exportar</h2>
    <a href="exportar.php" class="btn btn-sm">📥 Exportar CSV</a>
    <p style="font-size:.85rem;color:#999;margin-top:.5rem">Descarrega todas as reservas em formato CSV (abre no Excel).</p>
  </div>

</div>
<?php endif ?>
</body>
</html>
