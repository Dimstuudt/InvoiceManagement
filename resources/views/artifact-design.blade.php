<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Panduan Invoice Management</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f1f5f9; color: #1f2937; font-size: 13px; line-height: 1.5; }
code { font-family: ui-monospace, 'SF Mono', Monaco, Consolas, monospace; }

.page-wrap { max-width: 860px; margin: 0 auto; padding: 32px 24px 64px; }

.section { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 32px; margin-bottom: 24px; }

/* Cover */
.cover { background: #4f46e5; border: none; color: #fff; padding: 40px 40px 36px; }
.cover-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #a5b4fc; margin-bottom: 12px; }
.cover-title { font-size: 28px; font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 8px; }
.cover-sub { font-size: 14px; color: #c7d2fe; margin-bottom: 24px; }
.cover-pills { display: flex; flex-wrap: wrap; gap: 8px; }
.cover-pill { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); border-radius: 999px; padding: 4px 12px; font-size: 11px; color: #e0e7ff; font-weight: 500; }

/* Section header */
.sec-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px; }
.sec-title { font-size: 17px; font-weight: 800; color: #111827; margin-bottom: 4px; }
.sec-desc { font-size: 12px; color: #6b7280; margin-bottom: 20px; line-height: 1.6; }

/* Badges */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; line-height: 1.6; white-space: nowrap; }
.badge-draft   { background: #f3f4f6; color: #6b7280; }
.badge-verified { background: #fffbeb; color: #b45309; outline: 1px solid #fde68a; }
.badge-sent    { background: #eff6ff; color: #1d4ed8; outline: 1px solid #bfdbfe; }
.badge-paid    { background: #f0fdf4; color: #15803d; outline: 1px solid #bbf7d0; }
.badge-frozen  { background: #e0f2fe; color: #0369a1; outline: 1px solid #bae6fd; }
.badge-carried { background: #fff7ed; color: #c2410c; outline: 1px solid #fed7aa; }
.badge-indigo  { background: #eef2ff; color: #4338ca; outline: 1px solid #c7d2fe; }

.badge-chain-carry     { background: #fff7ed; color: #ea580c; font-size: 9px; padding: 1px 6px; border-radius: 4px; font-weight: 700; }
.badge-chain-reaktivasi { background: #f5f3ff; color: #7c3aed; font-size: 9px; padding: 1px 6px; border-radius: 4px; font-weight: 700; }
.badge-carry-head      { background: #d1fae5; color: #065f46; font-size: 9px; padding: 1px 6px; border-radius: 4px; font-weight: 700; border: 1px solid #6ee7b7; }

/* Status table */
.status-grid { display: grid; grid-template-columns: 110px 1fr; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
.status-row { display: contents; }
.status-row > div { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; font-size: 12px; color: #374151; }
.status-row:last-child > div { border-bottom: none; }
.status-row:nth-child(even) > div { background: #fafafa; }
.status-row > div:first-child { display: flex; align-items: center; border-right: 1px solid #f3f4f6; }

/* Mock UI */
.mock-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin: 12px 0; }
.mock-header { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; }
.mock-row { padding: 10px 16px; border-bottom: 1px solid #f9fafb; display: flex; align-items: center; gap: 12px; font-size: 12px; }
.mock-row:last-child { border-bottom: none; }
.mock-row.carried-row { background: #fff7ed; }

.col-name { flex: 1; min-width: 0; }
.inv-number { font-family: ui-monospace,'SF Mono',monospace; font-size: 11px; font-weight: 700; color: #4f46e5; }
.client-name { font-weight: 600; color: #111827; font-size: 12px; }

/* Dots */
.send-dots { display: inline-flex; gap: 3px; align-items: center; }
.dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.dot-indigo { background: #6366f1; }
.dot-red    { background: #f87171; }
.dot-gray   { background: #d1d5db; }

/* Checkbox */
.chk { width: 15px; height: 15px; border: 1.5px solid #d1d5db; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chk.checked { background: #f59e0b; border-color: #f59e0b; }
.chk.checked::after { content: ''; display: block; width: 8px; height: 5px; border-left: 1.5px solid #fff; border-bottom: 1.5px solid #fff; transform: rotate(-45deg) translateY(-1px); }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 9px; font-size: 11px; font-weight: 600; cursor: default; white-space: nowrap; border: none; }
.btn-primary { background: #4f46e5; color: #fff; }
.btn-emerald { background: #ecfdf5; color: #065f46; outline: 1px solid #6ee7b7; }
.btn-ghost   { background: #f9fafb; color: #374151; outline: 1px solid #e5e7eb; }
.btn-orange  { background: #fff7ed; color: #9a3412; outline: 1px solid #fed7aa; }
.btn-sky     { background: #f0f9ff; color: #075985; outline: 1px solid #bae6fd; }
.btn-violet  { background: #f5f3ff; color: #5b21b6; outline: 1px solid #ddd6fe; }
.btn-teal    { background: #f0fdfa; color: #134e4a; outline: 1px solid #99f6e4; }
.btn-sm { padding: 3px 8px; font-size: 10px; border-radius: 7px; }

/* Three-dot menu */
.menu-mock { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,.12); width: 200px; padding: 6px 0; overflow: hidden; }
.menu-label { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .1em; padding: 8px 12px 4px; }
.menu-divider { height: 1px; background: #f3f4f6; margin: 4px 0; }
.menu-item { padding: 8px 12px; font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 8px; cursor: default; }
.menu-item .m-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.menu-item.green  { color: #065f46; }
.menu-item.amber  { color: #92400e; }
.menu-item.gray   { color: #374151; }
.menu-item.orange { color: #9a3412; }
.menu-item.sky    { color: #075985; }
.menu-item.red    { color: #991b1b; }
.menu-item.dim    { color: #9ca3af; cursor: not-allowed; font-size: 11px; }
.menu-code { font-family: ui-monospace,'SF Mono',monospace; font-size: 10px; font-weight: 700; color: #6b7280; padding: 6px 12px 4px; border-bottom: 1px solid #f3f4f6; }

/* Timeline */
.timeline { position: relative; padding-left: 28px; }
.tl-item { position: relative; padding: 0 0 16px 0; }
.tl-item:last-child { padding-bottom: 0; }
.tl-dot { position: absolute; left: -28px; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 0 0 2px #e5e7eb; top: 1px; }
.tl-dot.paid   { background: #10b981; box-shadow: 0 0 0 2px #bbf7d0; }
.tl-dot.unpaid { background: #6366f1; box-shadow: 0 0 0 2px #c7d2fe; }
.tl-line { position: absolute; left: -20px; top: 20px; bottom: 0; width: 2px; background: #e5e7eb; }
.tl-line.green { background: #86efac; }
.tl-inv { font-family: ui-monospace,'SF Mono',monospace; font-size: 11px; font-weight: 700; color: #4f46e5; }
.tl-tags { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 3px; }

/* Sub-item */
.sub-item { margin-left: 0; padding: 8px 12px; background: #fafafa; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 8px; display: flex; align-items: flex-start; gap: 8px; }
.sub-prefix { font-size: 9px; padding: 1px 5px; border-radius: 4px; font-weight: 700; flex-shrink: 0; margin-top: 1px; }
.sub-prefix.carry   { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.sub-prefix.prepay  { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
.sub-prefix.reaktiv { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }

/* Email timeline */
.send-timeline { display: flex; align-items: flex-start; margin: 16px 0; position: relative; }
.send-stage { flex: 1; text-align: center; position: relative; }
.send-stage::before { content: ''; position: absolute; top: 11px; left: 50%; right: -50%; height: 2px; background: #e5e7eb; z-index: 0; }
.send-stage:last-child::before { display: none; }
.send-circle { width: 24px; height: 24px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800; position: relative; z-index: 1; }
.send-circle.s-indigo { background: #4f46e5; color: #fff; }
.send-circle.s-red    { background: #ef4444; color: #fff; }
.send-stage-label { font-size: 10px; font-weight: 700; color: #6b7280; }
.send-stage-day   { font-size: 9px; color: #9ca3af; margin-top: 2px; }
.send-stage-desc  { font-size: 10px; color: #6b7280; margin-top: 4px; line-height: 1.4; }
.send-stage:last-child .send-stage-label { color: #b91c1c; }
.send-stage:last-child .send-stage-desc  { color: #b91c1c; }

/* Layout */
.two-col   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

/* Info boxes */
.info-box { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; }
.info-box.warn { background: #fffbeb; border-color: #fde68a; }
.info-box.tip  { background: #f0fdf4; border-color: #bbf7d0; }
.info-box p { font-size: 12px; color: #374151; line-height: 1.6; }
.info-box strong { color: #1f2937; }

/* Dividers */
.divider { display: flex; align-items: center; gap: 12px; margin: 16px 0 12px; }
.divider-line { flex: 1; height: 1px; background: #f3f4f6; }
.divider-text { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .1em; }

/* Before/after */
.ba-wrap { display: grid; grid-template-columns: 1fr 36px 1fr; align-items: start; }
.ba-arrow { display: flex; align-items: center; justify-content: center; padding-top: 20px; color: #9ca3af; font-size: 18px; }
.ba-label { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 8px; }

/* Filter bar */
.filter-bar { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 16px; display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.filter-select { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px 10px; font-size: 11px; color: #374151; font-weight: 500; }
.filter-search { display: flex; align-items: center; gap: 6px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 4px 10px; font-size: 11px; color: #9ca3af; margin-left: auto; }

/* Client profile */
.profile-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
.avatar { width: 44px; height: 44px; border-radius: 12px; background: #4f46e5; color: #fff; font-size: 18px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
.stat-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
.stat-num { font-size: 22px; font-weight: 800; color: #111827; line-height: 1; }
.stat-label { font-size: 10px; color: #9ca3af; font-weight: 500; margin-top: 2px; }

/* Tabs */
.tab-bar { display: flex; border-bottom: 2px solid #f3f4f6; overflow: hidden; }
.tab { padding: 10px 16px; font-size: 12px; font-weight: 500; color: #9ca3af; border-bottom: 2px solid transparent; margin-bottom: -2px; display: flex; align-items: center; gap: 6px; white-space: nowrap; }
.tab.active { color: #4f46e5; border-bottom-color: #4f46e5; background: rgba(238,242,255,.5); font-weight: 700; }
.tab-dot { width: 7px; height: 7px; border-radius: 50%; }
.tab-count { font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 999px; }
.tab.active .tab-count { background: #e0e7ff; color: #4338ca; }
.tab:not(.active) .tab-count { background: #f3f4f6; color: #9ca3af; }

@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }
.pulse { animation: pulse 1.8s ease-in-out infinite; }

.note { font-size: 11px; color: #6b7280; margin-top: 6px; }
.note strong { color: #374151; }

.steps { display: flex; flex-direction: column; gap: 10px; }
.step { display: flex; gap: 12px; align-items: flex-start; }
.step-num { width: 22px; height: 22px; border-radius: 50%; background: #4f46e5; color: #fff; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.step-body { flex: 1; font-size: 12px; color: #374151; line-height: 1.6; }
.step-body strong { color: #111827; }

.recur-badge { display: inline-flex; align-items: center; padding: 2px 6px; background: #eef2ff; color: #4338ca; font-size: 10px; font-weight: 600; border-radius: 5px; }

.doc-footer { text-align: center; font-size: 11px; color: #9ca3af; padding-top: 8px; }

@media (prefers-color-scheme: dark) {
  body { background: #0f172a; color: #e2e8f0; }
  .section { background: #1e293b; border-color: #334155; }
  .sec-title { color: #f1f5f9; }
  .sec-desc { color: #94a3b8; }
  .mock-card { background: #1e293b; border-color: #334155; }
  .mock-header { background: #0f172a; border-color: #334155; }
  .mock-row { border-color: #1e293b; }
  .client-name { color: #f1f5f9; }
  .status-grid { border-color: #334155; }
  .status-row > div { border-bottom-color: #334155; color: #cbd5e1; }
  .status-row > div:first-child { border-right-color: #334155; }
  .status-row:nth-child(even) > div { background: #0f172a; }
  .info-box { background: #1e1b4b; border-color: #4338ca; }
  .info-box p { color: #c7d2fe; }
  .info-box.warn { background: #1c1708; border-color: #92400e; }
  .info-box.warn p { color: #fde68a; }
  .info-box.tip { background: #052e16; border-color: #166534; }
  .info-box.tip p { color: #86efac; }
  .filter-bar, .profile-card, .stat-box { background: #1e293b; border-color: #334155; }
  .tab-bar { border-color: #334155; }
  .tab { color: #64748b; }
  .tab.active { background: rgba(79,70,229,.15); }
  .sub-item { background: #0f172a; border-color: #334155; }
  .menu-mock { background: #1e293b; border-color: #334155; }
  .menu-divider { background: #334155; }
  .tl-line { background: #334155; }
  .send-stage::before { background: #334155; }
  .divider-line { background: #334155; }
  .note { color: #94a3b8; }
  .step-body { color: #cbd5e1; }
  .step-body strong { color: #f1f5f9; }
  .btn-ghost { background: #0f172a; color: #94a3b8; outline-color: #334155; }
  .toc-sidebar { background: #1e293b; border-color: #334155; }
  .toc-header { color: #94a3b8; border-color: #334155; }
  .toc-item { color: #64748b; }
  .toc-item:hover { background: #0f172a; color: #c7d2fe; }
  .toc-item.active { background: rgba(99,102,241,.18); color: #a5b4fc; }
  .toc-item.active .toc-num { background: #4f46e5; color: #fff; }
  .toc-progress { background: #334155; }
}

/* TOC Sidebar */
html { scroll-behavior: smooth; }

.toc-sidebar {
  position: fixed;
  top: 50%;
  left: max(12px, calc(50% - 430px - 212px));
  transform: translateY(-50%);
  width: 192px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 14px 0 10px;
  box-shadow: 0 4px 24px rgba(0,0,0,.09);
  z-index: 100;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  scrollbar-width: none;
}
.toc-sidebar::-webkit-scrollbar { display: none; }

.toc-header {
  font-size: 9px;
  font-weight: 800;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #9ca3af;
  padding: 0 14px 10px;
  border-bottom: 1px solid #f3f4f6;
  margin-bottom: 6px;
}

.toc-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 6px 14px;
  text-decoration: none;
  color: #6b7280;
  font-size: 11px;
  font-weight: 500;
  line-height: 1.4;
  border-radius: 0;
  transition: background .15s, color .15s;
  cursor: pointer;
}
.toc-item:hover { background: #f9fafb; color: #4f46e5; }
.toc-item.active { background: #eef2ff; color: #4338ca; font-weight: 700; }
.toc-item.active .toc-num { background: #4f46e5; color: #fff; }

.toc-num {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  border-radius: 5px;
  background: #f3f4f6;
  color: #9ca3af;
  font-size: 8px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1px;
  transition: background .15s, color .15s;
}

.toc-progress {
  height: 2px;
  background: #f3f4f6;
  border-radius: 999px;
  margin: 8px 14px 0;
  overflow: hidden;
}
.toc-progress-bar {
  height: 100%;
  background: #4f46e5;
  border-radius: 999px;
  width: 0%;
  transition: width .2s ease;
}

@media (max-width: 1240px) {
  .toc-sidebar { display: none; }
}
</style>
</head>
<body>

<nav class="toc-sidebar" aria-label="Daftar Isi">
  <div class="toc-header">Daftar Isi</div>
  <a class="toc-item active" data-target="sec-cover">
    <span class="toc-num">&#9679;</span>
    <span>Pendahuluan</span>
  </a>
  <a class="toc-item" data-target="sec-1">
    <span class="toc-num">1</span>
    <span>Semua Invoice</span>
  </a>
  <a class="toc-item" data-target="sec-2">
    <span class="toc-num">2</span>
    <span>Invoice per Client</span>
  </a>
  <a class="toc-item" data-target="sec-3">
    <span class="toc-num">3</span>
    <span>Status Invoice</span>
  </a>
  <a class="toc-item" data-target="sec-4">
    <span class="toc-num">4</span>
    <span>Kirim Email Otomatis</span>
  </a>
  <a class="toc-item" data-target="sec-5">
    <span class="toc-num">5</span>
    <span>Membuat Invoice</span>
  </a>
  <a class="toc-item" data-target="sec-6">
    <span class="toc-num">6</span>
    <span>Menu Aksi &#8942;</span>
  </a>
  <a class="toc-item" data-target="sec-7">
    <span class="toc-num">7</span>
    <span>Carry</span>
  </a>
  <a class="toc-item" data-target="sec-8">
    <span class="toc-num">8</span>
    <span>Freeze &amp; Resume</span>
  </a>
  <a class="toc-item" data-target="sec-9">
    <span class="toc-num">9</span>
    <span>Reaktivasi</span>
  </a>
  <a class="toc-item" data-target="sec-10">
    <span class="toc-num">10</span>
    <span>Prepay</span>
  </a>
  <a class="toc-item" data-target="sec-11">
    <span class="toc-num">11</span>
    <span>Master Data</span>
  </a>
  <div class="toc-progress"><div class="toc-progress-bar" id="toc-bar"></div></div>
</nav>

<div class="page-wrap">

  <!-- COVER -->
  <div class="section cover" id="sec-cover">
    <div class="cover-eyebrow">Invoice Management &middot; Dokumentasi Internal</div>
    <div class="cover-title">Panduan Lengkap<br>Pengelolaan Invoice</div>
    <div class="cover-sub">Semua fitur berdasarkan tampilan aktual aplikasi.</div>
    <div class="cover-pills">
      <span class="cover-pill">Buat Invoice</span>
      <span class="cover-pill">Antrean Kirim Otomatis</span>
      <span class="cover-pill">Carry &amp; Tunggakan</span>
      <span class="cover-pill">Freeze &amp; Resume</span>
      <span class="cover-pill">Reaktivasi</span>
      <span class="cover-pill">Prepay</span>
      <span class="cover-pill">Master Data</span>
    </div>
  </div>


  <!-- 1. HALAMAN SEMUA INVOICE -->
  <div class="section" id="sec-1">
    <div class="sec-eyebrow">Bagian 1</div>
    <div class="sec-title">Halaman Semua Invoice</div>
    <div class="sec-desc">Pintu masuk utama. Invoice yang jatuh tempo <strong>bulan depan</strong> tampil di bagian atas (prioritas). Gunakan filter bulan/tahun untuk berpindah tampilan.</div>

    <div class="filter-bar">
      <span style="font-size:9px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.1em;">Tampilkan bulan</span>
      <span class="filter-select">Agustus</span>
      <span class="filter-select">2026</span>
      <div class="filter-search">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Cari nomor invoice...
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
      <span class="dot dot-indigo pulse" style="width:8px;height:8px;"></span>
      <span style="font-size:12px;color:#374151;">Invoice bulan <strong style="color:#4338ca;">Agustus 2026</strong></span>
      <span style="font-size:11px;color:#9ca3af;">3 invoice</span>
    </div>

    <div class="mock-card">
      <div class="mock-header">
        <span style="font-size:9px;font-weight:700;color:#a5b4fc;text-transform:uppercase;letter-spacing:.1em;">Client / Invoice</span>
        <span style="font-size:9px;font-weight:700;color:#a5b4fc;text-transform:uppercase;letter-spacing:.1em;">Status Kirim</span>
      </div>

      <div class="mock-row" style="background:#fffbeb;">
        <div class="chk checked"></div>
        <div class="col-name">
          <div class="client-name">PT Maju Bersama</div>
          <div class="inv-number">INV/2026/08/001</div>
          <div style="margin-top:3px;display:flex;gap:4px;align-items:center;">
            <span style="font-size:10px;color:#9ca3af;">Hosting &amp; Domain</span>
            <span class="recur-badge">&#8635; 1 bln</span>
          </div>
        </div>
        <div style="width:120px;"><div style="font-size:11px;color:#374151;">1 Agu 2026</div><div style="font-size:11px;color:#9ca3af;">&rarr; 31 Agu 2026</div></div>
        <div style="flex:1;">
          <div style="display:flex;align-items:center;gap:6px;">
            <span class="badge badge-verified">Terverifikasi</span>
            <span class="send-dots"><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span></span>
          </div>
          <div style="font-size:10px;color:#f59e0b;margin-top:3px;font-weight:600;">send1: secepatnya</div>
        </div>
        <div style="width:80px;text-align:right;font-weight:700;font-size:12px;">Rp&nbsp;500.000</div>
        <div style="display:flex;gap:4px;">
          <span class="btn btn-ghost btn-sm">Lihat</span>
          <span class="btn btn-emerald btn-sm">&#10003; Paid</span>
        </div>
      </div>

      <div class="mock-row">
        <div class="chk checked"></div>
        <div class="col-name">
          <div class="client-name">CV Sinar Abadi</div>
          <div class="inv-number">INV/2026/08/002</div>
          <div style="margin-top:3px;font-size:10px;color:#9ca3af;">Maintenance Website</div>
        </div>
        <div style="width:120px;"><div style="font-size:11px;color:#374151;">5 Agu 2026</div><div style="font-size:11px;color:#9ca3af;">&rarr; 4 Sep 2026</div></div>
        <div style="flex:1;">
          <div style="display:flex;align-items:center;gap:6px;">
            <span class="badge badge-sent">Sent</span>
            <span class="send-dots"><span class="dot dot-indigo"></span><span class="dot dot-indigo"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span></span>
          </div>
          <div style="font-size:10px;color:#9ca3af;margin-top:3px;">send3: 26 Agu 2026</div>
        </div>
        <div style="width:80px;text-align:right;font-weight:700;font-size:12px;">Rp&nbsp;250.000</div>
        <div style="display:flex;gap:4px;">
          <span class="btn btn-ghost btn-sm">Lihat</span>
          <span class="btn btn-emerald btn-sm">&#10003; Paid</span>
        </div>
      </div>

      <div class="mock-row carried-row">
        <div class="chk"></div>
        <div class="col-name">
          <div class="client-name">PT Karya Digital</div>
          <div class="inv-number">INV/2026/08/003</div>
          <div style="margin-top:3px;display:flex;gap:4px;"><span style="font-size:10px;color:#9ca3af;">SEO Services</span><span class="badge-chain-carry">carry</span></div>
        </div>
        <div style="width:120px;"><div style="font-size:11px;color:#374151;">1 Agu 2026</div><div style="font-size:11px;color:#9ca3af;">&rarr; 31 Agu 2026</div></div>
        <div style="flex:1;"><span class="badge badge-carried">Carried</span></div>
        <div style="width:80px;text-align:right;font-weight:700;font-size:12px;color:#9ca3af;">Rp&nbsp;750.000</div>
        <div><span class="btn btn-ghost btn-sm">Lihat</span></div>
      </div>
    </div>

    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:10px;">
      <div style="display:flex;align-items:center;gap:5px;"><div class="chk checked"></div><span style="font-size:11px;color:#6b7280;"><strong>Centang terisi</strong> = Terverifikasi (masuk antrean kirim otomatis)</span></div>
      <div style="display:flex;align-items:center;gap:5px;"><div class="chk"></div><span style="font-size:11px;color:#6b7280;"><strong>Centang kosong</strong> = Draft (belum antrean)</span></div>
    </div>
    <p class="note" style="margin-top:8px;"><strong>Tombol aksi</strong> (Lihat / Paid / Hapus) muncul saat kursor diarahkan ke baris invoice. Klik <strong>nomor invoice</strong> untuk langsung ke halaman client dengan invoice di-highlight.</p>
  </div>


  <!-- 2. HALAMAN CLIENT -->
  <div class="section" id="sec-2">
    <div class="sec-eyebrow">Bagian 2</div>
    <div class="sec-title">Halaman Invoice per Client</div>
    <div class="sec-desc">Tampilan detail semua invoice milik satu client. Setiap layanan recurring punya tab tersendiri. Diakses dengan mengklik nama client atau nomor invoice dari halaman utama.</div>

    <div class="profile-card">
      <div style="display:flex;gap:14px;align-items:flex-start;">
        <div class="avatar">P</div>
        <div style="flex:1;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
            <div>
              <div style="font-size:16px;font-weight:800;color:#111827;">PT Maju Bersama</div>
              <div style="display:flex;gap:6px;margin-top:5px;align-items:center;">
                <span class="badge badge-indigo" style="font-size:9px;">Teknologi</span>
                <span style="font-size:11px;color:#9ca3af;">Jakarta &middot; 4 invoice</span>
              </div>
            </div>
            <span class="btn btn-primary btn-sm">+ Buat Invoice</span>
          </div>
          <div style="display:flex;gap:24px;margin-top:12px;padding-top:10px;border-top:1px solid #f3f4f6;flex-wrap:wrap;">
            <div><div style="font-size:9px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">PIC</div><div style="font-size:12px;color:#374151;margin-top:2px;">Budi Santoso</div></div>
            <div><div style="font-size:9px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Email</div><div style="font-size:12px;color:#374151;margin-top:2px;">budi@majubersama.co.id</div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="three-col" style="margin-bottom:12px;">
      <div class="stat-box">
        <div class="stat-icon" style="background:#f3f4f6;"><svg width="16" height="16" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <div class="stat-num">4</div><div class="stat-label">Total Invoice</div>
      </div>
      <div class="stat-box">
        <div class="stat-icon" style="background:#f0fdf4;"><svg width="16" height="16" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="stat-num" style="font-size:14px;">Rp&nbsp;2,5jt</div><div class="stat-label">Sudah Dibayar</div>
        <div style="font-size:9px;color:#86efac;margin-top:2px;">3 invoice</div>
      </div>
      <div class="stat-box">
        <div class="stat-icon" style="background:#eef2ff;"><svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
        <div class="stat-num">2</div><div class="stat-label">Layanan Berulang</div>
      </div>
    </div>

    <div class="mock-card">
      <div class="tab-bar">
        <div class="tab active"><span class="tab-dot" style="background:#6366f1;"></span>Hosting &amp; Domain<span class="tab-count">3</span></div>
        <div class="tab"><span class="tab-dot" style="background:#10b981;"></span>Maintenance<span class="tab-count">1</span></div>
        <div class="tab"><span class="tab-dot" style="background:#9ca3af;"></span>Invoice Mandiri<span class="tab-count">1</span></div>
      </div>
      <div style="padding:10px 16px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;background:#fafafa;">
        <div style="display:flex;align-items:center;gap:8px;"><span class="recur-badge">&#8635; tiap 1 bulan</span><span style="font-size:11px;color:#9ca3af;">3 periode &middot; mulai 1 Jun 2026</span></div>
        <div style="text-align:right;"><div style="font-size:13px;font-weight:800;color:#111827;">Rp 1.500.000</div><div style="font-size:10px;color:#9ca3af;"><span style="color:#10b981;font-weight:600;">Rp 1.000.000</span> terbayar</div></div>
      </div>
      <div style="padding:16px 16px 12px;">
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line green"></div>
            <div class="tl-content">
              <span class="tl-inv">INV/2026/06/001</span>
              <div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;padding:1px 5px;">Paid</span><span style="font-size:10px;color:#9ca3af;">Jun 2026</span></div>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line green"></div>
            <div class="tl-content">
              <div style="display:flex;align-items:center;gap:5px;margin-bottom:2px;"><span class="badge-carry-head">Carry Head</span><span class="tl-inv">INV/2026/07/001</span></div>
              <div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;padding:1px 5px;">Paid</span><span style="font-size:10px;color:#f97316;">+ Rp 500.000 carry</span></div>
              <div class="sub-item" style="margin-top:6px;">
                <span class="sub-prefix carry">Carry</span>
                <div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">C-INV/2026/06/001</span><div style="font-size:10px;color:#9ca3af;margin-top:1px;">Tunggakan Jun 2026</div></div>
                <span style="font-size:10px;font-weight:700;color:#9ca3af;">Rp 500.000</span>
              </div>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot unpaid"><div style="width:6px;height:6px;border-radius:50%;background:#fff;"></div></div>
            <div class="tl-content">
              <span class="tl-inv">INV/2026/08/001</span>
              <div class="tl-tags">
                <span class="badge badge-verified" style="font-size:9px;padding:1px 5px;">Terverifikasi</span>
                <span class="badge" style="font-size:9px;padding:1px 5px;background:#f3f4f6;color:#6b7280;">Unpaid</span>
                <span class="send-dots"><span class="dot dot-gray" style="width:5px;height:5px;"></span><span class="dot dot-gray" style="width:5px;height:5px;"></span><span class="dot dot-gray" style="width:5px;height:5px;"></span><span class="dot dot-gray" style="width:5px;height:5px;"></span><span class="dot dot-gray" style="width:5px;height:5px;"></span></span>
                <span style="font-size:9px;color:#f59e0b;font-weight:600;">&rarr; send1: secepatnya</span>
              </div>
              <p style="font-size:10px;color:#9ca3af;margin-top:3px;">1 Agu &ndash; 31 Agu 2026</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="note">Dot tab: <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#ef4444;vertical-align:middle;"></span> merah = jatuh tempo &nbsp;|&nbsp; <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#6366f1;vertical-align:middle;"></span> biru = terverifikasi belum bayar &nbsp;|&nbsp; <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#10b981;vertical-align:middle;"></span> hijau = semua lunas.</p>
  </div>


  <!-- 3. STATUS INVOICE -->
  <div class="section" id="sec-3">
    <div class="sec-eyebrow">Bagian 3</div>
    <div class="sec-title">Status Invoice</div>
    <div class="sec-desc">Setiap invoice memiliki dua status: <strong>status dokumen</strong> (alur kerja) dan <strong>status pembayaran</strong>.</div>

    <div class="status-grid">
      <div class="status-row"><div><span class="badge badge-draft">Draft</span></div><div><strong>Belum diproses</strong> — Invoice baru dibuat. Masih bisa diedit. Belum masuk antrean kirim. Cron tidak menyentuhnya.</div></div>
      <div class="status-row"><div><span class="badge badge-verified">Terverifikasi</span></div><div><strong>Siap kirim otomatis</strong> — Masuk antrean. Cron berjalan tiap 2 menit: saat issue_date tiba, invoice otomatis dikirim via email. Tidak bisa diedit.</div></div>
      <div class="status-row"><div><span class="badge badge-sent">Sent</span></div><div><strong>Sudah dikirim</strong> — Sudah terkirim minimal satu kali (send1+). Akan terus dikirim terjadwal sampai lunas atau mencapai send5.</div></div>
      <div class="status-row"><div><span class="badge badge-paid">Paid</span></div><div><strong>Lunas</strong> — Email dihentikan. Untuk invoice recurring, sistem otomatis membuat draft invoice periode berikutnya.</div></div>
      <div class="status-row"><div><span class="badge badge-frozen">Frozen</span></div><div><strong>Dibekukan</strong> — Tidak dihitung jatuh tempo, cron tidak mengirim. Lanjutkan dengan <strong>Perbarui</strong> dari menu &#8942; saat client siap.</div></div>
      <div class="status-row"><div><span class="badge badge-carried">Carried</span></div><div><strong>Terbawa tunggakan</strong> — Invoice lama yang tunggakannya dipindah ke invoice baru. Tidak bisa diubah; hanya sebagai referensi tunggakan.</div></div>
    </div>

    <div class="divider"><div class="divider-line"></div><div class="divider-text">Status Pengiriman (send_status)</div><div class="divider-line"></div></div>

    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:3px;"><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span style="font-size:11px;color:#6b7280;margin-left:4px;font-family:monospace;">unsent</span></div>
      <span style="color:#d1d5db;">&rarr;</span>
      <div style="display:flex;align-items:center;gap:3px;"><span class="dot dot-indigo"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span style="font-size:11px;color:#6b7280;margin-left:4px;font-family:monospace;">send1</span></div>
      <span style="color:#d1d5db;">&rarr;</span>
      <div style="display:flex;align-items:center;gap:3px;"><span class="dot dot-indigo"></span><span class="dot dot-indigo"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span class="dot dot-gray"></span><span style="font-size:11px;color:#6b7280;margin-left:4px;font-family:monospace;">send2</span></div>
      <span style="color:#d1d5db;">&rarr; &hellip; &rarr;</span>
      <div style="display:flex;align-items:center;gap:3px;"><span class="dot dot-indigo"></span><span class="dot dot-indigo"></span><span class="dot dot-indigo"></span><span class="dot dot-indigo"></span><span class="dot dot-red"></span><span style="font-size:11px;color:#b91c1c;font-weight:700;margin-left:4px;font-family:monospace;">send5 &#9888;</span></div>
    </div>
    <p class="note" style="margin-top:6px;">Dot merah di send5 = peringatan nonaktif layanan. Jika sudah Paid, dot berubah hijau dan email berhenti.</p>
  </div>


  <!-- 4. KIRIM EMAIL OTOMATIS -->
  <div class="section" id="sec-4">
    <div class="sec-eyebrow">Bagian 4</div>
    <div class="sec-title">Pengiriman Email Otomatis (Cron Job)</div>
    <div class="sec-desc">Cron job berjalan <strong>setiap 2 menit</strong>. Invoice berstatus <span class="badge badge-verified" style="font-size:9px;">Terverifikasi</span> + belum bayar + issue_date sudah tiba &rarr; email dikirim otomatis via Brevo dengan PDF terlampir.</div>

    <div class="send-timeline">
      <div class="send-stage"><div class="send-circle s-indigo">1</div><div class="send-stage-label">send1</div><div class="send-stage-day">Hari H</div><div class="send-stage-desc">Invoice pertama kali dikirim pada issue_date</div></div>
      <div class="send-stage"><div class="send-circle s-indigo">2</div><div class="send-stage-label">send2</div><div class="send-stage-day">+14 hari</div><div class="send-stage-desc">Pengingat pertama jika belum lunas</div></div>
      <div class="send-stage"><div class="send-circle s-indigo">3</div><div class="send-stage-label">send3</div><div class="send-stage-day">+21 hari</div><div class="send-stage-desc">Pengingat kedua</div></div>
      <div class="send-stage"><div class="send-circle s-indigo">4</div><div class="send-stage-label">send4</div><div class="send-stage-day">+28 hari</div><div class="send-stage-desc">Pengingat ketiga</div></div>
      <div class="send-stage"><div class="send-circle s-red">!</div><div class="send-stage-label">send5</div><div class="send-stage-day">+35 hari</div><div class="send-stage-desc">Peringatan: layanan akan dinonaktifkan</div></div>
    </div>

    <div class="info-box warn" style="margin-top:16px;">
      <p><strong>Syarat email terkirim otomatis:</strong> (1) Status dokumen = Terverifikasi, (2) payment_status = Unpaid, (3) issue_date sudah lewat atau sama dengan hari ini. Invoice Draft, Frozen, Carried, atau sudah Paid tidak akan disentuh cron.</p>
    </div>
    <div class="info-box">
      <p><strong>Cara tandai masuk antrean:</strong> Klik checkbox di kolom kiri, atau klik badge status dokumen di timeline client &rarr; pilih <em>Antrean Kirim</em>. Untuk keluarkan dari antrean: klik badge Terverifikasi &rarr; <em>Draft &mdash; keluarkan antrean</em>.</p>
    </div>

    <div style="margin-top:12px;">
      <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:8px;">Contoh: issue_date 1 Agustus 2026</div>
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:4px;font-size:10px;text-align:center;">
        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:8px 4px;"><div style="font-weight:700;color:#4338ca;">1 Agu</div><div style="color:#6b7280;margin-top:2px;">send1 dikirim</div></div>
        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:8px 4px;"><div style="font-weight:700;color:#4338ca;">15 Agu</div><div style="color:#6b7280;margin-top:2px;">send2 dikirim</div></div>
        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:8px 4px;"><div style="font-weight:700;color:#4338ca;">22 Agu</div><div style="color:#6b7280;margin-top:2px;">send3 dikirim</div></div>
        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:8px 4px;"><div style="font-weight:700;color:#4338ca;">29 Agu</div><div style="color:#6b7280;margin-top:2px;">send4 dikirim</div></div>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 4px;"><div style="font-weight:700;color:#b91c1c;">5 Sep</div><div style="color:#b91c1c;margin-top:2px;font-weight:600;">send5 &#9888; nonaktif</div></div>
      </div>
    </div>
  </div>


  <!-- 5. MEMBUAT INVOICE -->
  <div class="section" id="sec-5">
    <div class="sec-eyebrow">Bagian 5</div>
    <div class="sec-title">Membuat Invoice Baru</div>
    <div class="sec-desc">Bisa dari tombol <strong>+ Buat Invoice</strong> di halaman Semua Invoice, atau dari halaman Client (client terisi otomatis).</div>

    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Pilih Client</strong> — Jika dari halaman client, sudah terisi otomatis.</div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Isi informasi dasar</strong> — Nomor invoice (auto-generate atau manual), kategori proyek, tanggal terbit (issue_date), dan jatuh tempo (due_date).</div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Tambah item</strong> — Nama layanan, jumlah, dan harga. Bisa tambah banyak item. Diskon per item juga bisa diisi.</div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Atur opsi tambahan</strong> — Diskon total (nominal/persen), PPN, interval recurring (berapa bulan sekali), template email, rekening bank, penandatangan.</div></div>
      <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Simpan</strong> — Invoice tersimpan sebagai <span class="badge badge-draft" style="font-size:9px;">Draft</span>. Tandai Terverifikasi jika sudah siap masuk antrean kirim.</div></div>
    </div>

    <div class="info-box tip" style="margin-top:14px;">
      <p><strong>Invoice Recurring:</strong> Isi field interval bulan. Setelah invoice di-Paid, sistem otomatis membuat draft invoice periode berikutnya.</p>
    </div>
  </div>


  <!-- 6. MENU AKSI -->
  <div class="section" id="sec-6">
    <div class="sec-eyebrow">Bagian 6</div>
    <div class="sec-title">Menu Aksi (&#8942; Tiga Titik)</div>
    <div class="sec-desc">Klik ikon &#8942; di pojok kanan setiap baris invoice. Menu yang tampil berbeda tergantung status invoice.</div>

    <div class="two-col">
      <div>
        <div style="font-size:11px;font-weight:600;color:#374151;margin-bottom:6px;">Contoh: invoice recurring, Terverifikasi, Unpaid</div>
        <div class="menu-mock">
          <div class="menu-code">INV/2026/08/001</div>
          <div class="menu-label">Ubah Status</div>
          <div class="menu-item green"><span class="m-dot" style="background:#10b981;"></span>Tandai Lunas</div>
          <div class="menu-item amber"><span class="m-dot" style="background:#f59e0b;"></span>Keluarkan dari Antrean</div>
          <div class="menu-divider"></div>
          <div class="menu-label">Tindakan</div>
          <div class="menu-item orange"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 5l7 7-7 7M5 12h15"/></svg>Carry — bawa tunggakan</div>
          <div class="menu-item" style="color:#0f766e;"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 3"/></svg>Prepay</div>
          <div class="menu-item sky"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/></svg>Freeze — tunda pembayaran</div>
          <div class="menu-divider"></div>
          <div class="menu-label">Dokumen</div>
          <div class="menu-item dim"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>Edit — keluarkan antrean dulu</div>
          <div class="menu-item gray"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download PDF</div>
          <div class="menu-item gray"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l5.414 5.414V19a2 2 0 01-2 2z"/></svg>Receipt</div>
          <div class="menu-item gray"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>Print Invoice</div>
          <div class="menu-divider"></div>
          <div class="menu-item red"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Hapus Invoice</div>
        </div>
      </div>

      <div>
        <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:8px;">Keterangan tiap aksi</div>
        <div style="display:flex;flex-direction:column;gap:7px;">
          <div style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#065f46;margin-bottom:2px;">Tandai Lunas</div><div style="font-size:11px;color:#6b7280;">Set paid. Email berhenti. Invoice recurring: draft periode berikutnya dibuat otomatis.</div></div>
          <div style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#92400e;margin-bottom:2px;">Masuk / Keluarkan Antrean</div><div style="font-size:11px;color:#6b7280;">Toggle Draft &harr; Terverifikasi. Jika Terverifikasi, cron akan mengirim email.</div></div>
          <div style="padding:8px 10px;border:1px solid #ffedd5;background:#fff7ed;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#9a3412;margin-bottom:2px;">Carry — bawa tunggakan</div><div style="font-size:11px;color:#6b7280;">Invoice lama &rarr; Carried. Invoice baru dibuat dengan tunggakan lama di PDF. Hanya untuk recurring, unpaid, belum punya anak.</div></div>
          <div style="padding:8px 10px;border:1px solid #ccfbf1;background:#f0fdfa;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#0f766e;margin-bottom:2px;">Prepay — bayar di muka</div><div style="font-size:11px;color:#6b7280;">Client membayar beberapa periode sekaligus. Sistem membuat sub-invoice P- untuk tiap periode dalam satu chain. Tidak bisa dikombinasi dengan Carry atau Reaktivasi.</div></div>
          <div style="padding:8px 10px;border:1px solid #e0f2fe;background:#f0f9ff;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#075985;margin-bottom:2px;">Freeze — tunda pembayaran</div><div style="font-size:11px;color:#6b7280;">Invoice jadi Frozen. Tidak ada email, tidak ada jatuh tempo. Hanya untuk invoice yang punya parent (dalam chain recurring).</div></div>
          <div style="padding:8px 10px;border:1px solid #ddd6fe;background:#f5f3ff;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#5b21b6;margin-bottom:2px;">Reaktivasi</div><div style="font-size:11px;color:#6b7280;">Tersedia di invoice Paid terakhir yang overdue + belum punya anak. Generate ulang semua periode yang terlewat.</div></div>
          <div style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:2px;">Hapus Invoice</div><div style="font-size:11px;color:#6b7280;">Hanya bisa jika tidak punya child. Jika ada chain, hapus dari yang paling akhir dulu.</div></div>
          <div style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;"><div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:2px;">Edit Invoice</div><div style="font-size:11px;color:#6b7280;">Hanya saat status Draft + belum Paid. Jika Terverifikasi, keluarkan dari antrean dulu.</div></div>
        </div>
      </div>
    </div>
  </div>


  <!-- 7. CARRY -->
  <div class="section" id="sec-7">
    <div class="sec-eyebrow">Bagian 7</div>
    <div class="sec-title">Carry — Membawa Tunggakan ke Periode Baru</div>
    <div class="sec-desc">Digunakan saat client belum membayar tapi layanan tetap berjalan. Invoice baru dibuat dengan tunggakan lama sudah tercantum otomatis di PDF.</div>

    <div class="info-box"><p><strong>Kapan bisa Carry:</strong> Status Draft atau Terverifikasi (bukan Frozen/Carried/Paid), harus recurring, belum ada child invoice, dan bukan dalam chain prepay/reaktivasi.</p></div>

    <div class="ba-wrap" style="margin-top:16px;">
      <div>
        <div class="ba-label">Sebelum Carry</div>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line green"></div>
            <div class="tl-content"><div class="tl-inv">INV/2026/06/001</div><div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;padding:1px 5px;">Paid</span><span style="font-size:10px;color:#9ca3af;">Jun 2026</span></div></div>
          </div>
          <div class="tl-item">
            <div class="tl-dot unpaid"><div style="width:6px;height:6px;border-radius:50%;background:#fff;"></div></div>
            <div class="tl-content">
              <div class="tl-inv">INV/2026/07/001</div>
              <div class="tl-tags"><span class="badge badge-verified" style="font-size:9px;padding:1px 5px;">Terverifikasi</span><span class="badge" style="font-size:9px;padding:1px 5px;background:#f3f4f6;color:#6b7280;">Unpaid</span></div>
              <div style="margin-top:5px;display:flex;gap:4px;align-items:center;font-size:10px;color:#9ca3af;">klik &#8942; &rarr; <span class="btn btn-orange btn-sm" style="padding:2px 7px;font-size:10px;">Carry &rarr;</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="ba-arrow">&rarr;</div>
      <div>
        <div class="ba-label">Sesudah Carry</div>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line"></div>
            <div class="tl-content"><div class="tl-inv" style="font-size:10px;color:#9ca3af;">INV/2026/06/001</div><div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;">Paid</span></div></div>
          </div>
          <div style="margin-bottom:6px;">
            <div class="sub-item">
              <span class="sub-prefix carry">Carry</span>
              <div style="flex:1;"><div style="display:flex;align-items:center;gap:4px;"><span class="tl-inv" style="font-size:10px;color:#9ca3af;">C-INV/2026/07/001</span><span class="badge badge-carried" style="font-size:9px;padding:1px 4px;">Carried</span></div><div style="font-size:10px;color:#9ca3af;margin-top:1px;">Tunggakan Jul 2026</div></div>
              <span style="font-size:10px;font-weight:700;color:#9ca3af;">Rp 500rb</span>
            </div>
          </div>
          <div class="tl-item">
            <div class="tl-dot unpaid"><div style="width:6px;height:6px;border-radius:50%;background:#fff;"></div></div>
            <div class="tl-content">
              <div style="display:flex;align-items:center;gap:4px;margin-bottom:2px;"><span class="badge-carry-head">Carry Head</span><span class="tl-inv">INV/2026/08/001</span></div>
              <div class="tl-tags"><span class="badge badge-draft" style="font-size:9px;padding:1px 5px;">Draft</span></div>
              <div style="font-size:10px;color:#f97316;margin-top:3px;font-weight:600;">+ Rp 500rb tunggakan Jul tercantum di PDF</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="info-box warn" style="margin-top:14px;">
      <p><strong>Cara membatalkan carry:</strong> Buka invoice HEAD hasil carry &rarr; klik &#8942; &rarr; Hapus Invoice (hanya bisa jika belum punya child lagi) &rarr; Hapus juga C- sub-invoice agar chain kembali bersih. Jika carry sudah berlapis, semua tunggakan diakumulasi ke invoice baru berikutnya secara otomatis.</p>
    </div>
  </div>


  <!-- 8. FREEZE & RESUME -->
  <div class="section" id="sec-8">
    <div class="sec-eyebrow">Bagian 8</div>
    <div class="sec-title">Freeze &amp; Resume — Tunda Lalu Lanjut</div>
    <div class="sec-desc">Freeze membekukan invoice sementara. Saat client siap lanjut, gunakan <strong>Perbarui</strong> dari menu &#8942; dengan tanggal dan durasi baru.</div>

    <div class="two-col">
      <div>
        <div class="ba-label" style="margin-bottom:8px;">Cara Freeze</div>
        <div class="info-box" style="margin-bottom:10px;"><p><strong>Syarat Freeze:</strong> Invoice harus punya parent_invoice_id (dalam chain recurring), belum Paid, dan bukan Frozen/Carried.</p></div>
        <div class="steps">
          <div class="step"><div class="step-num">1</div><div class="step-body">Di invoice aktif dalam chain, klik &#8942; &rarr; <span class="btn btn-sky btn-sm" style="display:inline-flex;">Freeze</span></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body">Invoice berubah jadi <span class="badge badge-frozen" style="font-size:9px;">Frozen</span>. Tidak ada email, tidak ada jatuh tempo.</div></div>
        </div>
        <div style="margin-top:12px;display:flex;align-items:center;gap:8px;">
          <span class="badge badge-verified" style="font-size:9px;">Terverifikasi</span>
          <span style="font-size:12px;color:#9ca3af;">&rarr; setelah Freeze &rarr;</span>
          <span class="badge badge-frozen" style="font-size:9px;">Frozen</span>
        </div>
      </div>
      <div>
        <div class="ba-label" style="margin-bottom:8px;">Cara Resume (Perbarui)</div>
        <div class="info-box tip" style="margin-bottom:10px;"><p>Saat client mau lanjut: klik &#8942; di invoice <span class="badge badge-frozen" style="font-size:9px;">Frozen</span> &rarr; <strong>Perbarui &mdash; lanjutkan</strong>.</p></div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 4px 12px rgba(0,0,0,.08);">
          <div style="font-size:12px;font-weight:800;color:#111827;margin-bottom:2px;">Lanjutkan Invoice</div>
          <div style="font-size:11px;color:#9ca3af;margin-bottom:12px;">Tentukan tanggal mulai dan durasi perpanjangan berikutnya.</div>
          <div style="margin-bottom:10px;"><div style="font-size:11px;font-weight:500;color:#374151;margin-bottom:4px;">Tanggal Mulai</div><div style="border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:11px;color:#374151;">2026-09-01</div></div>
          <div style="margin-bottom:14px;"><div style="font-size:11px;font-weight:500;color:#374151;margin-bottom:4px;">Perpanjangan (bulan)</div><div style="border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:11px;color:#374151;">1 bulan &#9662;</div></div>
          <div style="display:flex;gap:8px;"><span class="btn btn-ghost" style="flex:1;justify-content:center;">Batal</span><span class="btn btn-primary" style="flex:1;justify-content:center;">Lanjutkan</span></div>
        </div>
        <p class="note" style="margin-top:8px;">Setelah Lanjutkan: invoice baru (F- prefix) dibuat sebagai child dari yang Frozen.</p>
      </div>
    </div>
  </div>


  <!-- 9. REAKTIVASI -->
  <div class="section" id="sec-9">
    <div class="sec-eyebrow">Bagian 9</div>
    <div class="sec-title">Reaktivasi — Hidupkan Kembali Layanan</div>
    <div class="sec-desc">Digunakan saat client pernah berhenti berlangganan dan mau aktif lagi. Sistem generate ulang invoice dari tanggal mulai baru, termasuk semua periode yang terlewat selama jeda.</div>

    <div class="info-box warn"><p><strong>Syarat Reaktivasi:</strong> Invoice terakhir sudah <span class="badge badge-paid" style="font-size:9px;">Paid</span>, tidak punya child invoice, belum dalam chain carry/prepay/reaktivasi lain, dan harus <em>overdue</em> (lewat due_date tanpa periode baru).</p></div>

    <div class="ba-wrap" style="margin-top:16px;">
      <div>
        <div class="ba-label">Layanan berhenti lama</div>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line green"></div>
            <div class="tl-content"><div class="tl-inv">INV/2026/01/001</div><div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;">Paid</span><span style="font-size:10px;color:#9ca3af;">Jan 2026</span></div></div>
          </div>
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-content">
              <div class="tl-inv">INV/2026/02/001</div>
              <div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;">Paid</span><span style="font-size:10px;color:#9ca3af;">Feb 2026</span></div>
              <div style="margin-top:5px;font-size:10px;color:#ef4444;font-weight:600;">&#9888; overdue 150+ hari, tidak ada kelanjutan</div>
              <div style="margin-top:4px;display:flex;gap:4px;align-items:center;font-size:10px;color:#9ca3af;">klik &#8942; &rarr; <span class="btn btn-violet btn-sm" style="padding:2px 7px;font-size:10px;display:inline-flex;">&#8635; Reaktivasi</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="ba-arrow">&rarr;</div>
      <div>
        <div class="ba-label">Setelah Reaktivasi (mulai Jul 2026)</div>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot paid"><svg width="8" height="8" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
            <div class="tl-line"></div>
            <div class="tl-content"><div class="tl-inv" style="font-size:10px;color:#9ca3af;">INV/2026/02/001</div><div class="tl-tags"><span class="badge badge-paid" style="font-size:9px;">Paid</span></div></div>
          </div>
          <div style="margin-bottom:6px;">
            <div class="sub-item" style="margin-bottom:4px;"><span class="sub-prefix reaktiv">Reaktivasi</span><div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">R-INV/2026/07/001</span><div style="font-size:10px;color:#9ca3af;">Mar 2026 (terlewat)</div></div></div>
            <div class="sub-item" style="margin-bottom:4px;"><span class="sub-prefix reaktiv">Reaktivasi</span><div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">R-INV/2026/07/002</span><div style="font-size:10px;color:#9ca3af;">Apr 2026 (terlewat)</div></div></div>
            <div style="font-size:9px;color:#9ca3af;padding-left:6px;">&hellip; dst s/d bulan sebelum tanggal mulai baru &hellip;</div>
          </div>
          <div class="tl-item">
            <div class="tl-dot unpaid" style="background:#7c3aed;"><div style="width:6px;height:6px;border-radius:50%;background:#fff;"></div></div>
            <div class="tl-content">
              <div style="display:flex;align-items:center;gap:4px;margin-bottom:2px;"><span class="badge-chain-reaktivasi">reaktivasi</span><span class="tl-inv">INV/2026/07/003</span></div>
              <div class="tl-tags"><span class="badge badge-draft" style="font-size:9px;">Draft</span><span style="font-size:10px;color:#9ca3af;">Jul 2026 — invoice aktif baru</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <p class="note" style="margin-top:12px;">Untuk membatalkan: klik &#8942; di invoice HEAD reaktivasi (bukan R- sub-item) &rarr; <strong>Rollback reaktivasi</strong>.</p>
  </div>


  <!-- 10. PREPAY -->
  <div class="section" id="sec-10">
    <div class="sec-eyebrow">Bagian 10</div>
    <div class="sec-title">Prepay — Bayar di Muka</div>
    <div class="sec-desc">Client membayar beberapa periode sekaligus. Sistem membuat sub-invoice P- untuk setiap periode dalam satu chain. Tidak bisa dikombinasi dengan Carry atau Reaktivasi.</div>

    <div class="two-col">
      <div>
        <div class="info-box"><p><strong>Syarat Prepay:</strong> Invoice recurring, belum Paid/Frozen/Carried, dan bukan dalam chain carry/reaktivasi.</p></div>
        <div class="steps" style="margin-top:10px;">
          <div class="step"><div class="step-num">1</div><div class="step-body">Di invoice aktif, klik &#8942; &rarr; <span class="btn btn-teal btn-sm" style="display:inline-flex;">Prepay</span></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body">Pilih berapa periode yang ingin dibayar di muka.</div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body">Sistem generate sub-invoice P- untuk setiap periode dalam satu chain.</div></div>
        </div>
        <p class="note" style="margin-top:10px;">P- sub-invoice tidak punya send_status — email hanya dikirim untuk HEAD invoice.</p>
      </div>
      <div>
        <div class="ba-label">Contoh struktur Prepay (3 bulan)</div>
        <div class="timeline">
          <div class="tl-item">
            <div class="tl-dot unpaid" style="background:#0d9488;"><div style="width:6px;height:6px;border-radius:50%;background:#fff;"></div></div>
            <div class="tl-line"></div>
            <div class="tl-content"><div class="tl-inv">INV/2026/08/001</div><div class="tl-tags"><span class="badge badge-draft" style="font-size:9px;">Draft</span><span style="font-size:10px;color:#9ca3af;">Agu 2026 (HEAD)</span></div></div>
          </div>
          <div>
            <div class="sub-item" style="margin-bottom:4px;"><span class="sub-prefix prepay">Prepay</span><div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">P-INV/2026/08/001</span><div style="font-size:10px;color:#9ca3af;">Sep 2026</div></div></div>
            <div class="sub-item" style="margin-bottom:4px;"><span class="sub-prefix prepay">Prepay</span><div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">P-INV/2026/08/002</span><div style="font-size:10px;color:#9ca3af;">Okt 2026</div></div></div>
            <div class="sub-item"><span class="sub-prefix prepay">Prepay</span><div style="flex:1;"><span class="tl-inv" style="font-size:10px;color:#6b7280;">P-INV/2026/08/003</span><div style="font-size:10px;color:#9ca3af;">Nov 2026</div></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- 11. MASTER DATA -->
  <div class="section" id="sec-11">
    <div class="sec-eyebrow">Bagian 11</div>
    <div class="sec-title">Master Data</div>
    <div class="sec-desc">Data referensi yang dipakai saat membuat dan mengirim invoice. Diisi sekali, digunakan berulang kali.</div>

    <div class="two-col">
      <div>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:10px;"><div style="font-size:12px;font-weight:700;color:#4f46e5;margin-bottom:4px;">Client</div><div style="font-size:11px;color:#6b7280;line-height:1.7;">Nama perusahaan, kategori, kota, PIC, direktur, alamat, dan <strong>daftar email tujuan</strong>. Semua email yang terdaftar akan menerima invoice otomatis. Satu client bisa punya lebih dari satu alamat email.</div></div>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:10px;"><div style="font-size:12px;font-weight:700;color:#4f46e5;margin-bottom:4px;">Kategori Proyek</div><div style="font-size:11px;color:#6b7280;line-height:1.7;">Label yang muncul sebagai nama tab di halaman client dan di kolom daftar invoice.</div></div>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:10px;"><div style="font-size:12px;font-weight:700;color:#4f46e5;margin-bottom:4px;">Rekening Bank</div><div style="font-size:11px;color:#6b7280;line-height:1.7;">Nama bank, nomor rekening, dan nama pemilik. Muncul di PDF invoice sebagai informasi pembayaran.</div></div>
      </div>
      <div>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:10px;"><div style="font-size:12px;font-weight:700;color:#4f46e5;margin-bottom:4px;">Template Email</div><div style="font-size:11px;color:#6b7280;line-height:1.7;">Template subject dan body email. Mendukung variabel: <code style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:3px;">&#123;&#123;invoice_number&#125;&#125;</code>, <code style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:3px;">&#123;&#123;client_name&#125;&#125;</code>, <code style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:3px;">&#123;&#123;due_date&#125;&#125;</code>, <code style="font-size:10px;background:#f3f4f6;padding:1px 4px;border-radius:3px;">&#123;&#123;amount&#125;&#125;</code>, dll. Template default dipakai jika invoice tidak punya template khusus.</div></div>
        <div style="padding:12px;border:1px solid #e5e7eb;border-radius:10px;"><div style="font-size:12px;font-weight:700;color:#4f46e5;margin-bottom:4px;">Tanda Tangan &amp; Penerbit Dokumen</div><div style="font-size:11px;color:#6b7280;line-height:1.7;">Nama penandatangan dan informasi penerbit yang muncul di bagian bawah PDF invoice.</div></div>
      </div>
    </div>
  </div>


  <div class="doc-footer">
    Panduan ini dibuat berdasarkan kode aktual aplikasi Invoice Management &middot; Juli 2026
  </div>

</div>

<script>
(function () {
  const tocItems = document.querySelectorAll('.toc-item[data-target]');
  const sections = Array.from(document.querySelectorAll('[id^="sec-"]'));
  const bar = document.getElementById('toc-bar');

  // Click → smooth scroll
  tocItems.forEach(function (item) {
    item.addEventListener('click', function () {
      const target = document.getElementById(item.dataset.target);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
    item.style.cursor = 'pointer';
  });

  function setActive(id) {
    tocItems.forEach(function (item) {
      item.classList.toggle('active', item.dataset.target === id);
    });
  }

  function updateProgress() {
    const scrolled = window.scrollY;
    const total = document.body.scrollHeight - window.innerHeight;
    const pct = total > 0 ? Math.round((scrolled / total) * 100) : 0;
    if (bar) bar.style.width = pct + '%';
  }

  // IntersectionObserver: track which section is in view
  var current = 'sec-cover';

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        current = entry.target.id;
        setActive(current);
      }
    });
  }, { rootMargin: '-15% 0px -70% 0px', threshold: 0 });

  sections.forEach(function (s) { observer.observe(s); });

  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();
})();
</script>
</body>
</html>
