<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Artisan Dashboard</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:2rem}
  h1{font-size:1.4rem;font-weight:800;color:#f8fafc;letter-spacing:.03em}
  .sub{font-size:.8rem;color:#64748b;margin-top:.25rem}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-top:1.75rem}
  .card{background:#1e293b;border:1px solid #334155;border-radius:.875rem;padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;transition:.15s}
  .card:hover{border-color:#475569;background:#273449}
  .card-label{font-size:.875rem;font-weight:700;color:#f1f5f9}
  .card-desc{font-size:.75rem;color:#94a3b8;line-height:1.5;flex:1}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.5rem 1rem;border-radius:.5rem;font-size:.8rem;font-weight:600;border:none;cursor:pointer;transition:.15s;width:100%}
  .btn:disabled{opacity:.5;cursor:not-allowed}
  .btn-blue   {background:#1d4ed8;color:#fff} .btn-blue:hover:not(:disabled)   {background:#1e40af}
  .btn-indigo {background:#4338ca;color:#fff} .btn-indigo:hover:not(:disabled) {background:#3730a3}
  .btn-red    {background:#dc2626;color:#fff} .btn-red:hover:not(:disabled)    {background:#b91c1c}
  .btn-teal   {background:#0f766e;color:#fff} .btn-teal:hover:not(:disabled)   {background:#0d5c56}
  .btn-emerald{background:#059669;color:#fff} .btn-emerald:hover:not(:disabled){background:#047857}
  .btn-orange {background:#ea580c;color:#fff} .btn-orange:hover:not(:disabled) {background:#c2410c}
  .btn-violet {background:#7c3aed;color:#fff} .btn-violet:hover:not(:disabled) {background:#6d28d9}
  .btn-amber  {background:#d97706;color:#fff} .btn-amber:hover:not(:disabled)  {background:#b45309}
  .btn-sky    {background:#0284c7;color:#fff} .btn-sky:hover:not(:disabled)    {background:#0369a1}
  .log{margin-top:1.75rem;background:#020617;border:1px solid #1e293b;border-radius:.875rem;overflow:hidden}
  .log-header{padding:.75rem 1.25rem;background:#0f172a;border-bottom:1px solid #1e293b;display:flex;align-items:center;justify-content:space-between}
  .log-title{font-size:.8rem;font-weight:700;color:#94a3b8;letter-spacing:.05em;text-transform:uppercase}
  .log-clear{font-size:.75rem;color:#475569;cursor:pointer;background:none;border:none;color:#64748b}
  .log-clear:hover{color:#94a3b8}
  .log-body{padding:1rem 1.25rem;font-family:'Courier New',monospace;font-size:.8rem;min-height:120px;max-height:420px;overflow-y:auto;line-height:1.7}
  .log-empty{color:#334155;font-style:italic}
  .entry{margin-bottom:1rem;border-bottom:1px solid #0f172a;padding-bottom:1rem}
  .entry:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
  .entry-cmd{color:#818cf8;font-weight:700}
  .entry-ok  {color:#4ade80}
  .entry-err {color:#f87171}
  .entry-meta{color:#475569;font-size:.7rem;margin-top:.25rem}
  .entry-out {color:#cbd5e1;white-space:pre-wrap;margin-top:.5rem}
  .spinner{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
  .confirm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:50;align-items:center;justify-content:center}
  .confirm-overlay.show{display:flex}
  .confirm-box{background:#1e293b;border:1px solid #334155;border-radius:1rem;padding:2rem;max-width:360px;width:90%}
  .confirm-title{font-size:1rem;font-weight:700;color:#f8fafc;margin-bottom:.5rem}
  .confirm-text{font-size:.875rem;color:#94a3b8;margin-bottom:1.5rem;line-height:1.6}
  .confirm-btns{display:flex;gap:.75rem}
  .confirm-btns button{flex:1;padding:.6rem;border-radius:.5rem;font-size:.875rem;font-weight:600;border:none;cursor:pointer}
  .confirm-cancel{background:#334155;color:#cbd5e1}
  .confirm-cancel:hover{background:#475569}
  .confirm-ok{background:#dc2626;color:#fff}
  .confirm-ok:hover{background:#b91c1c}
</style>
</head>
<body>

<div style="max-width:960px;margin:0 auto">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem">
    <div>
      <h1>⚙️ Artisan Dashboard</h1>
      <p class="sub">Jalankan perintah artisan dari browser · hanya untuk deployment/maintenance</p>
    </div>
    <div style="text-align:right">
      <span style="font-size:.7rem;color:#334155;font-family:'Courier New',monospace">
        PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }} · Laravel {{ app()->version() }}
      </span>
    </div>
  </div>

  <div class="grid">
    @foreach($commands as $cmd)
    <div class="card">
      <div>
        <p class="card-label">{{ $cmd['label'] }}</p>
        <p class="card-desc">{{ $cmd['desc'] }}</p>
      </div>
      <button
        class="btn btn-{{ $cmd['color'] }}"
        onclick="{{ $cmd['confirm'] ? "confirmRun('".addslashes($cmd['key'])."','".addslashes($cmd['label'])."', this)" : "runCmd('".addslashes($cmd['key'])."', this)" }}"
        data-cmd="{{ $cmd['key'] }}">
        {{ $cmd['label'] }}
      </button>
    </div>
    @endforeach
  </div>

  <div class="log">
    <div class="log-header">
      <span class="log-title">Output Log</span>
      <button class="log-clear" onclick="clearLog()">Bersihkan</button>
    </div>
    <div class="log-body" id="logBody">
      <span class="log-empty">Output perintah akan tampil di sini...</span>
    </div>
  </div>
</div>

<!-- Confirm Dialog -->
<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <p class="confirm-title" id="confirmTitle">Konfirmasi</p>
    <p class="confirm-text" id="confirmText">Yakin ingin menjalankan perintah ini?</p>
    <div class="confirm-btns">
      <button class="confirm-cancel" onclick="closeConfirm()">Batal</button>
      <button class="confirm-ok" id="confirmOkBtn">Jalankan</button>
    </div>
  </div>
</div>

<script>
const SECRET  = '{{ $secret }}';
const BASE    = '{{ url("/artisan-run") }}';

function runCmd(cmd, btn) {
  const allBtns = document.querySelectorAll('.btn');
  allBtns.forEach(b => b.disabled = true);
  const orig = btn.innerHTML;
  btn.innerHTML = '<span class="spinner"></span> Running...';

  const key = cmd.replace(':', '--');

  fetch(`${BASE}/${key}?secret=${encodeURIComponent(SECRET)}`)
    .then(r => r.json())
    .then(data => {
      appendLog(data);
    })
    .catch(err => {
      appendLog({ command: cmd, status: 'error', output: err.toString(), elapsed: '-', time: new Date().toLocaleTimeString('id-ID') });
    })
    .finally(() => {
      allBtns.forEach(b => b.disabled = false);
      btn.innerHTML = orig;
    });
}

let pendingCmd = null, pendingBtn = null;

function confirmRun(cmd, label, btn) {
  pendingCmd = cmd;
  pendingBtn = btn;
  document.getElementById('confirmTitle').textContent = label;
  document.getElementById('confirmText').textContent = `Perintah "${cmd}" bisa mengubah data secara permanen. Yakin ingin melanjutkan?`;
  document.getElementById('confirmOkBtn').onclick = () => {
    closeConfirm();
    runCmd(pendingCmd, pendingBtn);
  };
  document.getElementById('confirmOverlay').classList.add('show');
}

function closeConfirm() {
  document.getElementById('confirmOverlay').classList.remove('show');
}

function appendLog(data) {
  const body = document.getElementById('logBody');
  const empty = body.querySelector('.log-empty');
  if (empty) empty.remove();

  const entry = document.createElement('div');
  entry.className = 'entry';
  entry.innerHTML = `
    <p><span class="entry-cmd">$ artisan ${data.command}</span></p>
    <p class="entry-meta">${data.time} · ${data.elapsed}</p>
    <pre class="entry-out ${data.status === 'ok' ? 'entry-ok' : 'entry-err'}">${escHtml(data.output)}</pre>
  `;
  body.appendChild(entry);
  body.scrollTop = body.scrollHeight;
}

function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function clearLog() {
  const body = document.getElementById('logBody');
  body.innerHTML = '<span class="log-empty">Output perintah akan tampil di sini...</span>';
}
</script>
</body>
</html>
