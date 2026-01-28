<?php
// analyze.php
require_once __DIR__ . '/includes/naive.php';
require_once __DIR__ . '/includes/kmp.php';

function split_patterns(string $raw): array {
  $lines = preg_split("/\r\n|\n|\r/", $raw);
  $out = [];
  foreach ($lines as $l) {
    $l = trim($l);
    if ($l !== '') $out[] = $l;
  }
  return $out;
}

function highlight_line(string $line, array $patterns): string {
  $safe = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
  foreach ($patterns as $p) {
    if ($p === '') continue;
    $safe = preg_replace(
      "/(" . preg_quote($p, "/") . ")/i",
      "<mark>$1</mark>",
      $safe
    );
  }
  return $safe;
}

function read_input_lines(?array $file, string $paste): array {
  if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name']) && ($file['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_OK) {
    $content = file_get_contents($file['tmp_name']);
    if ($content === false) return [];
    $lines = preg_split("/\r\n|\n|\r/", $content);
    return array_values(array_filter($lines, fn($x) => trim((string)$x) !== ''));
  }
  $paste = trim($paste);
  if ($paste !== '') {
    $lines = preg_split("/\r\n|\n|\r/", $paste);
    return array_values(array_filter($lines, fn($x) => trim((string)$x) !== ''));
  }
  return [];
}

function contains_pattern(string $text, string $pattern, string $algo): bool {
  if ($pattern === '') return false;
  if ($algo === 'naive') return naive_contains($text, $pattern);
  return kmp_contains($text, $pattern);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: index.php");
  exit;
}

$algo = ($_POST['algo'] ?? 'kmp') === 'naive' ? 'naive' : 'kmp';
$mode = ($_POST['mode'] ?? 'single') === 'all' ? 'all' : 'single';
$patterns = split_patterns($_POST['patterns'] ?? '');
$lines = read_input_lines($_FILES['logfile'] ?? null, $_POST['paste_log'] ?? '');

if (count($lines) === 0 || count($patterns) === 0) {
  header("Location: index.php?error=empty");
  exit;
}

$fileLabel = 'paste-input.log';
if (isset($_FILES['logfile']) && is_uploaded_file($_FILES['logfile']['tmp_name'] ?? '')) {
  $fileLabel = basename($_FILES['logfile']['name'] ?? 'uploaded.log');
}

$start = microtime(true);

$counts = [];
foreach ($patterns as $p) $counts[$p] = 0;

$details = [];
$limitDetail = 200;

$totalLines = count($lines);
$totalDetections = 0;

for ($i = 0; $i < $totalLines; $i++) {
  $line = $lines[$i];
  $matchedPatterns = [];

  foreach ($patterns as $p) {
    if (contains_pattern($line, $p, $algo)) {
      $matchedPatterns[] = $p;
      $counts[$p]++;

      if ($mode === 'single') break;
    }
  }

  if (!empty($matchedPatterns)) {
    $totalDetections += 1;

    if (count($details) < $limitDetail) {
      $showPatterns = ($mode === 'single') ? [$matchedPatterns[0]] : $matchedPatterns;
      $details[] = [
        'line_no' => $i + 1,
        'pattern' => implode(", ", $showPatterns),
        'html'    => highlight_line($line, $showPatterns),
      ];
    }
  }
}

$elapsedMs = (microtime(true) - $start) * 1000.0;

$values = array_values($counts);
$maxVal = max($values ?: [1]);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Hasil Analisis</title>
  <style>
    :root{
      --bgA:#eef3ff; --bgB:#f7fbff;
      --card: rgba(255,255,255,.78);
      --card2: rgba(255,255,255,.62);
      --text:#0b1223;
      --muted:#64748b;
      --line: rgba(229,231,235,.85);
      --p1:#4f46e5; --p2:#6366f1;
      --shadow: 0 22px 70px rgba(15,23,42,.12);
      --shadow2: 0 14px 28px rgba(15,23,42,.10);
      --shadow3: 0 10px 18px rgba(15,23,42,.08);
      --r2:26px;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      color:var(--text);
      background:
        radial-gradient(1200px 650px at 10% 10%, rgba(99,102,241,.24), transparent 60%),
        radial-gradient(900px 520px at 90% 20%, rgba(79,70,229,.18), transparent 56%),
        linear-gradient(180deg, var(--bgA), var(--bgB));
      min-height:100vh;
    }
    .wrap{width:100%; max-width:none; margin:0; padding:18px 22px 22px;}

    .head{
      background: linear-gradient(180deg, var(--card2), rgba(255,255,255,.35));
      border:1px solid rgba(255,255,255,.40);
      border-radius: var(--r2);
      box-shadow: var(--shadow2);
      padding:16px 18px;
      display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
      backdrop-filter: blur(12px);
    }
    .head h1{margin:0; font-size:26px; letter-spacing:-.45px}
    .meta{margin-top:8px; display:flex; gap:10px; flex-wrap:wrap; color:var(--muted); font-size:14px;}
    .pill{
      display:inline-flex; align-items:center; gap:6px;
      padding:8px 10px;
      background: rgba(255,255,255,.72);
      border:1px solid var(--line);
      border-radius:999px;
      color:var(--text);
      font-weight:900;
      font-size:13px;
      backdrop-filter: blur(10px);
    }
    a.back{
      text-decoration:none;
      font-weight:900;
      color:var(--p1);
      font-size:16px;
      padding:10px 12px;
      border-radius:14px;
      background: rgba(255,255,255,.72);
      border:1px solid rgba(255,255,255,.55);
      box-shadow: var(--shadow3);
      height:fit-content;
    }

    .stats{
      margin-top:14px;
      display:grid;
      grid-template-columns: repeat(4, minmax(220px, 1fr));
      gap:12px;
    }
    .stat{
      background: var(--card);
      border:1px solid rgba(255,255,255,.40);
      border-radius:20px;
      box-shadow: var(--shadow3);
      backdrop-filter: blur(12px);
      padding:14px 14px;
      position:relative;
      overflow:hidden;
    }
    .stat:before{
      content:"";
      position:absolute;
      top:-50px; right:-50px;
      width:140px; height:140px;
      background: radial-gradient(circle, rgba(99,102,241,.20), transparent 60%);
      filter: blur(6px);
    }
    .stat small{color:var(--muted); font-size:12px; position:relative}
    .stat b{display:block; font-size:26px; margin-top:6px; letter-spacing:-.45px; position:relative}

    .grid{
      margin-top:14px;
      display:grid;
      grid-template-columns: 1.15fr 1fr;
      gap:14px;
      align-items:start;
    }
    .card{
      background: var(--card);
      border:1px solid rgba(255,255,255,.40);
      border-radius: var(--r2);
      box-shadow: var(--shadow);
      overflow:hidden;
      backdrop-filter: blur(12px);
    }
    .cardHeader{
      padding:16px 18px;
      border-bottom:1px solid rgba(229,231,235,.55);
      display:flex; align-items:center; justify-content:space-between;
    }
    .cardHeader h2{margin:0; font-size:20px; letter-spacing:-.3px;}
    .cardBody{padding:18px;}

    table{
      width:100%;
      border-collapse:collapse;
      background: rgba(255,255,255,.78);
      border:1px solid var(--line);
      border-radius:18px;
      overflow:hidden;
      backdrop-filter: blur(10px);
    }
    th,td{padding:12px 12px; border-bottom:1px solid rgba(229,231,235,.72); font-size:14px}
    th{color:var(--muted); text-align:left; font-size:13px; letter-spacing:.3px}
    tr:last-child td{border-bottom:none}
    td b{font-size:14px}

    .bars{
      background: rgba(255,255,255,.78);
      border:1px solid var(--line);
      border-radius:18px;
      padding:14px;
      backdrop-filter: blur(10px);
    }
    .barRow{
      display:grid;
      grid-template-columns: 200px 1fr 60px;
      gap:12px;
      align-items:center;
      margin:10px 0;
    }
    .barLabel{
      font-size:13px;
      font-weight:900;
      overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .barTrack{
      height:12px;
      border-radius:999px;
      background: rgba(99,102,241,.12);
      overflow:hidden;
      border:1px solid rgba(99,102,241,.18);
    }
    .barFill{
      height:100%;
      border-radius:999px;
      background: linear-gradient(90deg, rgba(79,70,229,.88), rgba(99,102,241,.88));
    }
    .barVal{font-weight:900; text-align:right;}

    .details{
      margin-top:14px;
      background: var(--card);
      border:1px solid rgba(255,255,255,.40);
      border-radius: var(--r2);
      box-shadow: var(--shadow);
      overflow:hidden;
      backdrop-filter: blur(12px);
    }
    .detailsHeader{
      padding:16px 18px;
      border-bottom:1px solid rgba(229,231,235,.55);
      display:flex; align-items:center; justify-content:space-between;
      gap:10px;
    }
    .detailsHeader h2{margin:0; font-size:20px; letter-spacing:-.3px;}
    .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;}
    mark{
      background: rgba(245,158,11,.28);
      padding:1px 4px;
      border-radius:7px;
    }

    @media (max-width:1100px){
      .stats{grid-template-columns:1fr 1fr}
      .grid{grid-template-columns:1fr}
    }
    @media (max-width:640px){
      .stats{grid-template-columns:1fr}
      .barRow{grid-template-columns: 140px 1fr 48px}
    }
  </style>
</head>
<body>
<div class="wrap">

  <div class="head">
    <div>
      <h1>Hasil Analisis</h1>
      <div class="meta">
        <span>File: <span class="pill"><?= htmlspecialchars($fileLabel) ?></span></span>
        <span>Algoritma: <span class="pill"><?= strtoupper(htmlspecialchars($algo)) ?></span></span>
        <span>Mode: <span class="pill"><?= $mode === 'single' ? 'Satu/baris' : 'Semua/baris' ?></span></span>
      </div>
    </div>
    <a class="back" href="index.php">← Kembali</a>
  </div>

  <div class="stats">
    <div class="stat"><small>Total Baris</small><b><?= (int)$totalLines ?></b></div>
    <div class="stat"><small>Total Deteksi</small><b><?= (int)$totalDetections ?></b></div>
    <div class="stat"><small>Execution Time</small><b><?= number_format($elapsedMs, 3) ?> ms</b></div>
    <div class="stat"><small>Jumlah Pattern</small><b><?= (int)count($patterns) ?></b></div>
  </div>

  <div class="grid">
    <div class="card">
      <div class="cardHeader">
        <h2>Ringkasan</h2>
        <span class="pill">per pattern</span>
      </div>
      <div class="cardBody">
        <table>
          <thead>
            <tr><th>PATTERN</th><th style="text-align:right">JUMLAH</th></tr>
          </thead>
          <tbody>
          <?php foreach ($counts as $p => $c): ?>
            <tr>
              <td><b><?= htmlspecialchars($p) ?></b></td>
              <td style="text-align:right; font-weight:900;"><?= (int)$c ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="cardHeader">
        <h2>Grafik</h2>
        <span class="pill">batang</span>
      </div>
      <div class="cardBody">
        <div class="bars">
          <?php foreach ($counts as $p => $c):
            $pct = ($maxVal > 0) ? (($c / $maxVal) * 100.0) : 0;
          ?>
            <div class="barRow">
              <div class="barLabel" title="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></div>
              <div class="barTrack"><div class="barFill" style="width: <?= number_format($pct, 2) ?>%;"></div></div>
              <div class="barVal"><?= (int)$c ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="details">
    <div class="detailsHeader">
      <h2>Detail Temuan</h2>
      <span class="pill">Top <?= (int)count($details) ?> / max <?= (int)$limitDetail ?></span>
    </div>
    <div class="cardBody">
      <table>
        <thead>
          <tr>
            <th style="width:90px">BARIS</th>
            <th style="width:240px">PATTERN</th>
            <th>ISI LOG</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($details) === 0): ?>
          <tr>
            <td colspan="3" style="color:#64748b; font-weight:900;">Tidak ada temuan.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($details as $d): ?>
            <tr>
              <td><b><?= (int)$d['line_no'] ?></b></td>
              <td><b><?= htmlspecialchars($d['pattern']) ?></b></td>
              <td class="mono"><?= $d['html'] ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
