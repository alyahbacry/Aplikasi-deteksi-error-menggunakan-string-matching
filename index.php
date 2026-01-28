<?php
// index.php
$error = $_GET['error'] ?? '';
$errMsg = '';
if ($error === 'empty') {
  $errMsg = 'Belum ada input log. Upload file atau tempel log dulu.';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Aplikasi Analisis Log</title>
  <style>
    :root{
      --bgA:#eef3ff;
      --bgB:#f7fbff;

      --card: rgba(255,255,255,.78);
      --card2: rgba(255,255,255,.62);
      --cardSolid:#ffffff;

      --text:#0b1223;
      --muted:#64748b;
      --line: rgba(229,231,235,.85);

      --p1:#4f46e5;
      --p2:#6366f1;
      --p3:#22c55e;
      --warn:#f59e0b;

      --shadow: 0 22px 70px rgba(15,23,42,.12);
      --shadow2: 0 14px 28px rgba(15,23,42,.10);
      --shadow3: 0 10px 18px rgba(15,23,42,.08);

      --r:18px;
      --r2:26px;

      --focus: 0 0 0 4px rgba(79,70,229,.16);
    }

    [data-theme="dark"]{
      --bgA:#070a14;
      --bgB:#0b1023;

      --card: rgba(17,24,39,.72);
      --card2: rgba(17,24,39,.58);
      --cardSolid:#0f172a;

      --text:#e5e7eb;
      --muted:#94a3b8;
      --line: rgba(148,163,184,.18);

      --shadow: 0 22px 70px rgba(0,0,0,.45);
      --shadow2: 0 14px 28px rgba(0,0,0,.35);
      --shadow3: 0 10px 18px rgba(0,0,0,.28);

      --focus: 0 0 0 4px rgba(99,102,241,.22);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      color:var(--text);
      background:
        radial-gradient(1200px 650px at 10% 10%, rgba(99,102,241,.24), transparent 60%),
        radial-gradient(900px 520px at 90% 20%, rgba(79,70,229,.18), transparent 56%),
        radial-gradient(900px 520px at 70% 95%, rgba(34,197,94,.14), transparent 58%),
        linear-gradient(180deg, var(--bgA), var(--bgB));
      min-height:100vh;
      overflow-x:hidden;
    }

    /* subtle animated glow */
    .bg-anim{
      position:fixed; inset:0;
      pointer-events:none;
      background: radial-gradient(700px 260px at 20% 0%, rgba(99,102,241,.18), transparent 60%);
      filter: blur(18px);
      opacity:.7;
      animation: floatGlow 8s ease-in-out infinite;
      z-index:0;
    }
    @keyframes floatGlow{
      0%,100%{transform: translateY(0px)}
      50%{transform: translateY(14px)}
    }

    .wrap{
      position:relative;
      z-index:1;
      width:100%;
      max-width:none;
      margin:0;
      padding:18px 22px 22px;
    }

    /* header */
    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      padding:14px 16px;
      border-radius: var(--r2);
      background: linear-gradient(180deg, var(--card2), rgba(255,255,255,.35));
      border:1px solid rgba(255,255,255,.35);
      box-shadow: var(--shadow2);
      backdrop-filter: blur(12px);
    }
    [data-theme="dark"] .topbar{
      border:1px solid rgba(148,163,184,.14);
      background: linear-gradient(180deg, rgba(17,24,39,.66), rgba(17,24,39,.42));
    }

    .brand{display:flex; align-items:center; gap:12px; min-width:280px;}
    .logo{
      width:46px; height:46px;
      border-radius:18px;
      background: linear-gradient(135deg, var(--p1), var(--p2));
      box-shadow: 0 18px 34px rgba(79,70,229,.28);
      display:grid; place-items:center;
      position:relative; overflow:hidden;
    }
    .logo:before{
      content:"";
      position:absolute;
      inset:-55%;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.52), transparent 60%);
      transform: rotate(18deg);
      opacity:.9;
    }
    .logo svg{position:relative}

    .title{display:flex; flex-direction:column; gap:2px}
    .title h1{margin:0; font-size:26px; letter-spacing:-.45px; line-height:1.12}
    .title p{margin:0; font-size:14px; color:var(--muted)}

    .right{display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end}
    .pill{
      display:flex; align-items:center; gap:8px;
      padding:10px 12px;
      border-radius:999px;
      background: rgba(255,255,255,.72);
      border:1px solid rgba(255,255,255,.50);
      box-shadow: var(--shadow3);
      font-size:14px;
      white-space:nowrap;
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .pill{
      background: rgba(15,23,42,.70);
      border:1px solid rgba(148,163,184,.16);
    }
    .dot{
      width:8px; height:8px; border-radius:999px;
      background:var(--p3);
      box-shadow:0 0 0 4px rgba(34,197,94,.18);
    }

    .iconBtn{
      width:40px; height:40px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,.55);
      background: rgba(255,255,255,.62);
      cursor:pointer;
      display:grid; place-items:center;
      box-shadow: var(--shadow3);
      transition:.14s ease;
    }
    [data-theme="dark"] .iconBtn{
      background: rgba(15,23,42,.70);
      border:1px solid rgba(148,163,184,.18);
    }
    .iconBtn:hover{transform: translateY(-1px)}
    .iconBtn:active{transform: translateY(0px)}

    /* chips */
    .chips{
      margin-top:14px;
      display:grid;
      grid-template-columns: repeat(4, minmax(220px, 1fr));
      gap:12px;
    }
    .chip{
      background: var(--card);
      border:1px solid rgba(255,255,255,.40);
      border-radius: 18px;
      padding:12px 14px;
      box-shadow: var(--shadow3);
      backdrop-filter: blur(12px);
      display:flex; justify-content:space-between; align-items:center; gap:10px;
      transition:.16s ease;
    }
    [data-theme="dark"] .chip{border:1px solid rgba(148,163,184,.14)}
    .chip:hover{transform: translateY(-1px)}
    .chip small{color:var(--muted); font-size:12px}
    .chip strong{font-size:14px}
    .tag{
      font-size:12px;
      padding:6px 10px;
      border-radius:999px;
      border:1px solid var(--line);
      background: rgba(255,255,255,.70);
      color:var(--text);
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .tag{
      background: rgba(15,23,42,.75);
      border:1px solid rgba(148,163,184,.18);
    }

    /* main grid */
    .grid{
      margin-top:14px;
      display:grid;
      grid-template-columns: 1.55fr 1fr;
      gap:14px;
      align-items:start;
    }

    .card{
      background: var(--card);
      border:1px solid rgba(255,255,255,.42);
      border-radius: var(--r2);
      box-shadow: var(--shadow);
      overflow:hidden;
      backdrop-filter: blur(12px);
      transition:.16s ease;
    }
    [data-theme="dark"] .card{border:1px solid rgba(148,163,184,.14)}
    .card:hover{transform: translateY(-1px)}

    .cardHeader{
      padding:16px 18px;
      border-bottom:1px solid rgba(229,231,235,.55);
      display:flex; align-items:flex-end; justify-content:space-between;
      gap:12px;
    }
    [data-theme="dark"] .cardHeader{border-bottom:1px solid rgba(148,163,184,.14)}
    .cardHeader h2{margin:0; font-size:20px; letter-spacing:-.3px}
    .cardHeader p{margin:2px 0 0; font-size:13px; color:var(--muted)}
    .cardBody{padding:18px}

    .sectionTitle{margin:0 0 8px; font-size:14px; font-weight:800}
    .hint{margin:0 0 10px; font-size:13px; color:var(--muted)}

    /* dropzone */
    .drop{
      border:2px dashed rgba(99,102,241,.35);
      background:
        radial-gradient(900px 200px at 20% 0%, rgba(99,102,241,.12), transparent 65%),
        linear-gradient(180deg, rgba(255,255,255,.42), rgba(255,255,255,.22));
      border-radius:20px;
      padding:14px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      transition: .16s ease;
      position:relative;
      overflow:hidden;
    }
    [data-theme="dark"] .drop{
      background:
        radial-gradient(900px 200px at 20% 0%, rgba(99,102,241,.14), transparent 65%),
        linear-gradient(180deg, rgba(15,23,42,.60), rgba(15,23,42,.40));
      border-color: rgba(99,102,241,.26);
    }
    .drop:after{
      content:"";
      position:absolute;
      top:-60px; left:-60px;
      width:140px; height:140px;
      background: radial-gradient(circle, rgba(99,102,241,.22), transparent 60%);
      filter: blur(6px);
      opacity:.7;
      transform: rotate(18deg);
    }
    .drop.dragover{
      transform: translateY(-1px);
      box-shadow: 0 18px 36px rgba(79,70,229,.14);
      border-color: rgba(79,70,229,.55);
    }

    .dropLeft{display:flex; align-items:center; gap:12px; min-width:220px}
    .ico{
      width:46px; height:46px;
      border-radius:18px;
      background: rgba(79,70,229,.14);
      border:1px solid rgba(79,70,229,.22);
      display:grid; place-items:center;
      box-shadow: 0 14px 22px rgba(79,70,229,.10);
    }
    .fileMeta{display:flex; flex-direction:column; gap:2px}
    .fileMeta b{font-size:14px}
    .fileMeta span{font-size:12px; color:var(--muted)}
    .dropActions{display:flex; gap:8px; align-items:center; z-index:1; position:relative}

    /* buttons */
    .btn{
      border:none;
      cursor:pointer;
      border-radius:14px;
      padding:10px 12px;
      font-weight:900;
      font-size:13px;
      background: rgba(255,255,255,.72);
      border:1px solid var(--line);
      color:var(--text);
      transition:.14s ease;
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .btn{
      background: rgba(15,23,42,.72);
      border:1px solid rgba(148,163,184,.18);
    }
    .btn:hover{transform: translateY(-1px)}
    .btn:active{transform: translateY(0px)}
    .btnPrimary{
      background: linear-gradient(135deg, var(--p1), var(--p2));
      border:none;
      color:#fff;
      box-shadow: 0 18px 34px rgba(79,70,229,.26);
    }
    .btnPrimary[disabled]{
      opacity:.55;
      cursor:not-allowed;
      transform:none;
      box-shadow:none;
    }
    .btnGhost{
      background: rgba(255,255,255,.50);
    }
    [data-theme="dark"] .btnGhost{
      background: rgba(15,23,42,.55);
    }

    input[type="file"]{display:none}

    textarea{
      width:100%;
      min-height: 230px;
      resize: vertical;
      border-radius:18px;
      border:1px solid rgba(229,231,235,.75);
      padding:12px 12px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
      font-size:14px;
      line-height:1.5;
      outline:none;
      background: rgba(255,255,255,.78);
      color:var(--text);
      backdrop-filter: blur(10px);
      transition:.14s ease;
    }
    [data-theme="dark"] textarea{
      background: rgba(15,23,42,.70);
      border:1px solid rgba(148,163,184,.18);
    }
    textarea:focus{box-shadow: var(--focus); border-color: rgba(79,70,229,.45)}

    /* option cards */
    .algoCards{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:10px;
      margin-top:8px;
    }
    .opt{
      border:1px solid rgba(229,231,235,.80);
      background: rgba(255,255,255,.70);
      border-radius:18px;
      padding:12px;
      display:flex; gap:10px; align-items:flex-start;
      cursor:pointer;
      transition:.15s ease;
      position:relative;
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .opt{
      background: rgba(15,23,42,.70);
      border:1px solid rgba(148,163,184,.18);
    }
    .opt:hover{transform: translateY(-1px); box-shadow: var(--shadow3)}
    .opt input{margin-top:2px}
    .opt .meta{display:flex; flex-direction:column; gap:2px}
    .opt .meta b{font-size:14px}
    .opt .meta span{font-size:12px; color:var(--muted)}
    .opt .badge{
      position:absolute;
      top:10px; right:10px;
      font-size:11px;
      padding:6px 8px;
      border-radius:999px;
      border:1px solid var(--line);
      background: rgba(255,255,255,.70);
      color:var(--text);
    }
    [data-theme="dark"] .opt .badge{
      background: rgba(15,23,42,.75);
      border:1px solid rgba(148,163,184,.18);
    }
    .opt.active{
      border-color: rgba(79,70,229,.45);
      box-shadow: var(--focus);
    }

    /* segment */
    .seg{
      margin-top:10px;
      background: rgba(255,255,255,.70);
      border:1px solid rgba(229,231,235,.75);
      border-radius:18px;
      padding:6px;
      display:flex;
      gap:6px;
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .seg{
      background: rgba(15,23,42,.70);
      border:1px solid rgba(148,163,184,.18);
    }
    .seg button{
      flex:1;
      border:none;
      background:transparent;
      padding:10px 10px;
      border-radius:14px;
      cursor:pointer;
      font-weight:900;
      font-size:13px;
      color:var(--muted);
      transition:.12s ease;
    }
    .seg button.active{
      background: rgba(79,70,229,.16);
      color:var(--text);
      box-shadow: inset 0 0 0 1px rgba(79,70,229,.22);
    }

    .miniRow{
      margin-top:10px;
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      align-items:center;
      justify-content:flex-start;
    }
    .note{font-size:12px; color:var(--muted)}

    /* footer bar sticky */
    .footerBar{
      margin-top:14px;
      padding:12px 14px;
      border-radius:20px;
      background: var(--card);
      border:1px solid rgba(255,255,255,.40);
      box-shadow: var(--shadow2);
      backdrop-filter: blur(14px);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      position:sticky;
      bottom:14px;
      z-index:5;
    }
    [data-theme="dark"] .footerBar{border:1px solid rgba(148,163,184,.14)}
    .status{display:flex; align-items:center; gap:10px; font-size:13px}
    .badgePill{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 10px;
      border-radius:999px;
      background: rgba(255,255,255,.72);
      border:1px solid var(--line);
      font-weight:900;
      backdrop-filter: blur(10px);
    }
    [data-theme="dark"] .badgePill{
      background: rgba(15,23,42,.72);
      border:1px solid rgba(148,163,184,.18);
    }
    .warnDot{
      width:8px; height:8px; border-radius:999px;
      background:var(--warn);
      box-shadow:0 0 0 4px rgba(245,158,11,.18);
    }
    .okDot{
      width:8px; height:8px; border-radius:999px;
      background:var(--p3);
      box-shadow:0 0 0 4px rgba(34,197,94,.18);
    }
    .actions{display:flex; gap:10px; align-items:center}

    .alert{
      margin-top:12px;
      padding:12px 14px;
      border-radius:18px;
      background: rgba(245,158,11,.12);
      border:1px solid rgba(245,158,11,.35);
      color: #7c2d12;
      font-weight:900;
      font-size:13px;
      box-shadow: var(--shadow3);
    }
    [data-theme="dark"] .alert{
      color:#ffd9a3;
      background: rgba(245,158,11,.14);
    }

    /* loading overlay */
    .overlay{
      position:fixed; inset:0;
      background: rgba(2,6,23,.45);
      display:none;
      align-items:center;
      justify-content:center;
      z-index:99;
      backdrop-filter: blur(6px);
    }
    .modal{
      width:min(520px, calc(100% - 32px));
      border-radius:22px;
      background: rgba(255,255,255,.88);
      border:1px solid rgba(255,255,255,.55);
      box-shadow: var(--shadow);
      padding:18px;
      display:flex; align-items:center; gap:14px;
    }
    [data-theme="dark"] .modal{
      background: rgba(15,23,42,.86);
      border:1px solid rgba(148,163,184,.18);
    }
    .spinner{
      width:34px; height:34px;
      border-radius:999px;
      border:3px solid rgba(99,102,241,.25);
      border-top-color: rgba(79,70,229,.95);
      animation: spin .9s linear infinite;
    }
    @keyframes spin{to{transform: rotate(360deg)}}
    .modal b{font-size:15px}
    .modal span{display:block; color:var(--muted); font-size:13px; margin-top:2px}

    @media (max-width: 1100px){
      .chips{grid-template-columns: 1fr 1fr}
      .grid{grid-template-columns:1fr}
      .brand{min-width:unset}
    }
    @media (max-width: 640px){
      .chips{grid-template-columns: 1fr}
      .title h1{font-size:22px}
      textarea{min-height:200px}
    }
  </style>
</head>
<body>
  <div class="bg-anim"></div>

  <div class="wrap">
    <div class="topbar">
      <div class="brand">
        <div class="logo" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M10.5 3.5h3A6.5 6.5 0 0 1 20 10v4a6.5 6.5 0 0 1-6.5 6.5h-3A6.5 6.5 0 0 1 4 14v-4A6.5 6.5 0 0 1 10.5 3.5Z" stroke="white" stroke-width="1.8"/>
            <path d="M9 12h6" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M12 9v6" stroke="white" stroke-width="1.8" stroke-linecap="round" opacity=".9"/>
          </svg>
        </div>
        <div class="title">
          <h1>Aplikasi Analisis Log</h1>
          <p>Upload atau paste log, lalu deteksi pattern.</p>
        </div>
      </div>

      <div class="right">
        <div class="pill"><span class="dot"></span><span>Server: <b>localhost</b></span></div>
        <button class="iconBtn" type="button" id="btnTheme" title="Tema">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M21 12.8A8.4 8.4 0 0 1 11.2 3a6.9 6.9 0 1 0 9.8 9.8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          </svg>
        </button>
        <button class="iconBtn" type="button" title="Refresh" onclick="location.reload()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M20 12a8 8 0 1 1-2.34-5.66" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M20 4v6h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>

    <?php if ($errMsg): ?>
      <div class="alert">⚠️ <?= htmlspecialchars($errMsg) ?></div>
    <?php endif; ?>

    <div class="chips">
      <div class="chip"><div><small>Status</small><br><strong id="chipInput">Kosong</strong></div><span class="tag" id="tagInput">—</span></div>
      <div class="chip"><div><small>Pattern</small><br><strong id="chipPattern">4</strong></div><span class="tag">baris</span></div>
      <div class="chip"><div><small>Algoritma</small><br><strong id="chipAlgo">KMP</strong></div><span class="tag">opsi</span></div>
      <div class="chip"><div><small>Mode</small><br><strong id="chipMode">Satu/baris</strong></div><span class="tag">match</span></div>
    </div>

    <form class="grid" action="analyze.php" method="post" enctype="multipart/form-data" id="formAnalyze">
      <!-- LEFT -->
      <div class="card">
        <div class="cardHeader">
          <div>
            <h2>Input</h2>
            <p>File atau teks log.</p>
          </div>
          <div class="tag" id="lineInfo">0 baris</div>
        </div>

        <div class="cardBody">
          <div class="sectionTitle">Unggah File</div>
          <div class="hint">.log / .txt / .csv</div>

          <label class="drop" id="dropZone">
            <div class="dropLeft">
              <div class="ico" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M12 16V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <path d="M7 9l5-5 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="fileMeta">
                <b id="fileName">Belum ada file</b>
                <span>Drag & drop / pilih file</span>
              </div>
            </div>

            <div class="dropActions">
              <span class="tag" id="fileSize">Kosong</span>
              <button class="btn" type="button" id="btnPick">Pilih</button>
              <button class="btn btnGhost" type="button" id="btnClearFile" style="display:none">Hapus</button>
            </div>

            <input id="fileInput" name="logfile" type="file" accept=".log,.txt,.csv">
          </label>

          <div style="height:12px"></div>

          <div class="sectionTitle">Tempel Log <span class="note">(opsional)</span></div>
          <textarea id="pasteLog" name="paste_log" placeholder="Tempel log di sini..."></textarea>

          <div class="miniRow">
            <button class="btn" type="button" id="btnExample">Contoh</button>
            <button class="btn" type="button" id="btnClearPaste">Bersihkan</button>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="card">
        <div class="cardHeader">
          <div>
            <h2>Konfigurasi</h2>
            <p>Algoritma & pattern.</p>
          </div>
          <div class="tag" id="tagAlgo">KMP</div>
        </div>

        <div class="cardBody">
          <div class="sectionTitle">Algoritma</div>
          <div class="algoCards">
            <label class="opt active" id="optKMP">
              <input type="radio" name="algo" value="kmp" checked>
              <div class="meta">
                <b>KMP</b><span>Stabil</span>
              </div>
              <span class="badge">Recommended</span>
            </label>
            <label class="opt" id="optNaive">
              <input type="radio" name="algo" value="naive">
              <div class="meta">
                <b>Naive</b><span>Baseline</span>
              </div>
              <span class="badge">Simple</span>
            </label>
          </div>

          <div style="height:12px"></div>

          <div class="sectionTitle">Mode</div>
          <input type="hidden" name="mode" id="modeField" value="single">
          <div class="seg">
            <button type="button" class="active" id="modeSingle">Satu/baris</button>
            <button type="button" id="modeAll">Semua/baris</button>
          </div>

          <div style="height:12px"></div>

          <div class="sectionTitle">Pattern <span class="note">(1 baris = 1)</span></div>
          <textarea id="patterns" name="patterns">ERROR
Exception
Timeout
Connection failed</textarea>

          <div class="miniRow" style="justify-content:space-between">
            <span class="note" id="patternCount">4 pattern</span>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <button class="btn" type="button" id="btnDefault">Default</button>
              <button class="btn" type="button" id="btnTidy">Rapikan</button>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="footerBar" style="grid-column: 1 / -1;">
        <div class="status">
          <span class="badgePill"><span id="statusDot" class="warnDot"></span><span id="statusText">Belum ada input</span></span>
          <span class="note">Klik Analisis saat input siap.</span>
        </div>
        <div class="actions">
          <button class="btn" type="reset" id="btnReset">Reset</button>
          <button class="btn btnPrimary" type="submit" id="btnSubmit" disabled>Analisis</button>
        </div>
      </div>
    </form>
  </div>

  <!-- loading -->
  <div class="overlay" id="overlay">
    <div class="modal">
      <div class="spinner"></div>
      <div>
        <b>Menganalisis log…</b>
        <span>Mohon tunggu sebentar.</span>
      </div>
    </div>
  </div>

<script>
  const root = document.documentElement;
  const btnTheme = document.getElementById('btnTheme');

  // theme init
  const savedTheme = localStorage.getItem('theme');
  if(savedTheme === 'dark') root.setAttribute('data-theme','dark');

  btnTheme.addEventListener('click', () => {
    const isDark = root.getAttribute('data-theme') === 'dark';
    if(isDark){
      root.removeAttribute('data-theme');
      localStorage.setItem('theme','light');
    }else{
      root.setAttribute('data-theme','dark');
      localStorage.setItem('theme','dark');
    }
  });

  const fileInput = document.getElementById('fileInput');
  const btnPick = document.getElementById('btnPick');
  const btnClearFile = document.getElementById('btnClearFile');
  const fileName = document.getElementById('fileName');
  const fileSize = document.getElementById('fileSize');
  const dropZone = document.getElementById('dropZone');

  const pasteLog = document.getElementById('pasteLog');
  const patterns = document.getElementById('patterns');
  const patternCount = document.getElementById('patternCount');

  const chipInput = document.getElementById('chipInput');
  const tagInput  = document.getElementById('tagInput');
  const chipPattern = document.getElementById('chipPattern');
  const chipAlgo = document.getElementById('chipAlgo');
  const chipMode = document.getElementById('chipMode');
  const tagAlgo = document.getElementById('tagAlgo');

  const statusText = document.getElementById('statusText');
  const statusDot = document.getElementById('statusDot');
  const lineInfo = document.getElementById('lineInfo');

  const optKMP = document.getElementById('optKMP');
  const optNaive = document.getElementById('optNaive');

  const modeField = document.getElementById('modeField');
  const modeSingle = document.getElementById('modeSingle');
  const modeAll = document.getElementById('modeAll');

  const btnSubmit = document.getElementById('btnSubmit');
  const overlay = document.getElementById('overlay');
  const formAnalyze = document.getElementById('formAnalyze');

  function bytesToSize(bytes){
    if(!bytes) return 'Kosong';
    const sizes = ['B','KB','MB','GB'];
    const i = Math.floor(Math.log(bytes)/Math.log(1024));
    return (bytes/Math.pow(1024,i)).toFixed(i?1:0)+' '+sizes[i];
  }

  function countPatterns(){
    const lines = patterns.value.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
    patternCount.textContent = `${lines.length} pattern`;
    chipPattern.textContent = `${lines.length}`;
  }

  function hasInput(){
    const hasFile = fileInput.files && fileInput.files.length > 0;
    const hasPaste = (pasteLog.value || '').trim().length > 0;
    return hasFile || hasPaste;
  }

  function updateInputStatus(){
    const hasFile = fileInput.files && fileInput.files.length > 0;
    const hasPaste = (pasteLog.value || '').trim().length > 0;

    if(hasFile){
      chipInput.textContent = 'Upload';
      tagInput.textContent = 'File';
      statusText.textContent = 'Siap (file)';
      statusDot.className = 'okDot';
    } else if(hasPaste){
      chipInput.textContent = 'Paste';
      tagInput.textContent = 'Teks';
      statusText.textContent = 'Siap (teks)';
      statusDot.className = 'okDot';
    } else {
      chipInput.textContent = 'Kosong';
      tagInput.textContent = '—';
      statusText.textContent = 'Belum ada input';
      statusDot.className = 'warnDot';
    }

    const lines = (pasteLog.value || '').split(/\r?\n/).filter(l=>l.trim().length>0).length;
    lineInfo.textContent = hasFile ? 'File dipilih' : `${lines} baris`;

    btnSubmit.disabled = !hasInput();
  }

  function setAlgoUI(){
    const algo = document.querySelector('input[name="algo"]:checked')?.value || 'kmp';
    if(algo === 'kmp'){
      optKMP.classList.add('active');
      optNaive.classList.remove('active');
      chipAlgo.textContent = 'KMP';
      tagAlgo.textContent = 'KMP';
    } else {
      optNaive.classList.add('active');
      optKMP.classList.remove('active');
      chipAlgo.textContent = 'Naive';
      tagAlgo.textContent = 'Naive';
    }
  }

  function setModeUI(mode){
    modeField.value = mode;
    if(mode === 'single'){
      modeSingle.classList.add('active');
      modeAll.classList.remove('active');
      chipMode.textContent = 'Satu/baris';
    } else {
      modeAll.classList.add('active');
      modeSingle.classList.remove('active');
      chipMode.textContent = 'Semua/baris';
    }
  }

  btnPick.addEventListener('click', () => fileInput.click());

  btnClearFile.addEventListener('click', () => {
    fileInput.value = '';
    fileName.textContent = 'Belum ada file';
    fileSize.textContent = 'Kosong';
    btnClearFile.style.display = 'none';
    updateInputStatus();
  });

  fileInput.addEventListener('change', () => {
    const f = fileInput.files[0];
    if(!f){
      fileName.textContent = 'Belum ada file';
      fileSize.textContent = 'Kosong';
      btnClearFile.style.display = 'none';
      updateInputStatus();
      return;
    }
    fileName.textContent = f.name;
    fileSize.textContent = bytesToSize(f.size);
    btnClearFile.style.display = 'inline-block';
    updateInputStatus();
  });

  // drag & drop
  ['dragenter','dragover'].forEach(evt => {
    dropZone.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      dropZone.classList.add('dragover');
    });
  });
  ['dragleave','drop'].forEach(evt => {
    dropZone.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      dropZone.classList.remove('dragover');
    });
  });
  dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if(files && files.length){
      fileInput.files = files;
      fileInput.dispatchEvent(new Event('change'));
    }
  });

  pasteLog.addEventListener('input', updateInputStatus);
  patterns.addEventListener('input', countPatterns);

  document.querySelectorAll('input[name="algo"]').forEach(r => r.addEventListener('change', setAlgoUI));

  modeSingle.addEventListener('click', () => setModeUI('single'));
  modeAll.addEventListener('click', () => setModeUI('all'));

  document.getElementById('btnExample').addEventListener('click', () => {
    pasteLog.value =
`2026-01-23 10:10:10 INFO Service started
2026-01-23 10:10:11 ERROR Connection failed
2026-01-23 10:10:12 Exception NullPointerException
2026-01-23 10:10:13 Timeout waiting response
2026-01-23 10:10:14 WARN Retry attempt #1`;
    updateInputStatus();
  });

  document.getElementById('btnClearPaste').addEventListener('click', () => {
    pasteLog.value = '';
    updateInputStatus();
  });

  document.getElementById('btnDefault').addEventListener('click', () => {
    patterns.value = `ERROR
Exception
Timeout
Connection failed`;
    countPatterns();
  });

  document.getElementById('btnTidy').addEventListener('click', () => {
    const lines = patterns.value.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
    patterns.value = lines.join("\n");
    countPatterns();
  });

  document.getElementById('btnReset').addEventListener('click', () => {
    setTimeout(() => {
      fileName.textContent = 'Belum ada file';
      fileSize.textContent = 'Kosong';
      btnClearFile.style.display = 'none';
      setAlgoUI();
      setModeUI('single');
      countPatterns();
      updateInputStatus();
    }, 0);
  });

  // loading overlay
  formAnalyze.addEventListener('submit', (e) => {
    if(!hasInput()){
      e.preventDefault();
      return;
    }
    overlay.style.display = 'flex';
  });

  // init
  countPatterns();
  setAlgoUI();
  setModeUI('single');
  updateInputStatus();
</script>
</body>
</html>
