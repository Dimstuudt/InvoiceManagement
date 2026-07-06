<template>
  <AppLayout title="Cron Panel">
    <div class="space-y-6 max-w-4xl mx-auto">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Cron Panel</h2>
          <p class="text-sm text-gray-400 mt-0.5">Monitor & jalankan cron job invoice otomatis</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <Link :href="route('logs.cron')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-600 border border-purple-200 hover:bg-purple-100 transition-colors">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Log Cron
          </Link>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Admin Only
          </span>
        </div>
      </div>

      <!-- Status + URL card -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Status hidup/mati -->
        <div class="bg-white rounded-2xl border shadow-sm p-5"
          :class="statusBorder">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="statusIconBg">
              <div v-if="props.isAlive" class="relative">
                <span class="absolute inset-0 rounded-full animate-ping opacity-40" :class="statusIconBg"/>
                <svg class="w-5 h-5 relative" :class="statusIconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <svg v-else class="w-5 h-5" :class="statusIconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold" :class="statusTextColor">
                {{ props.isAlive ? 'Pengeksekusi Aktif' : (props.lastRun ? 'Pengeksekusi Mati' : 'Belum Pernah Jalan') }}
              </p>
              <p class="text-xs mt-0.5 text-gray-400">
                <template v-if="props.lastRun">
                  Terakhir eksekusi <strong class="text-gray-600">{{ minutesAgo }}</strong> yang lalu
                  <span class="mx-1">·</span>
                  <span class="font-mono text-gray-500">{{ formatDate(props.lastRun.created_at) }}</span>
                </template>
                <template v-else>Belum ada riwayat — setup cron di cPanel dulu</template>
              </p>
            </div>
            <div class="shrink-0 text-right">
              <p class="text-xs font-semibold" :class="statusTextColor">
                {{ props.isAlive ? '≤ 10 mnt lalu' : (props.lastRun ? '> 10 mnt lalu' : '—') }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5">interval 2 mnt</p>
            </div>
          </div>
        </div>

        <!-- URL untuk cPanel -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">URL untuk cPanel Cron</p>
          <div class="flex items-center gap-2">
            <code class="flex-1 text-xs bg-gray-100 rounded-lg px-3 py-2 text-gray-700 font-mono truncate">
              {{ props.cronUrl }}
            </code>
            <button @click="copyUrl" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors shrink-0">
              <svg v-if="!copiedUrl" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              <svg v-else class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-2">Set di cPanel → Cron Jobs → setiap hari jam 08:00</p>
          <p class="text-xs font-mono text-gray-500 mt-1 bg-gray-50 rounded px-2 py-1">
            curl -s "{{ props.cronUrl }}"
          </p>
        </div>
      </div>

      <!-- Manual trigger + secret -->
      <div class="bg-white rounded-2xl border shadow-sm p-5"
        :class="unlocked ? 'border-emerald-200 bg-emerald-50/30' : 'border-amber-200 bg-amber-50/30'">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
            :class="unlocked ? 'bg-emerald-100' : 'bg-amber-100'">
            <svg class="w-4.5 h-4.5" :class="unlocked ? 'text-emerald-600' : 'text-amber-600'"
              fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                :d="unlocked
                  ? 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'
                  : 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM16 7a4 4 0 10-8 0v4h8V7z'"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold" :class="unlocked ? 'text-emerald-700' : 'text-amber-700'">
              {{ unlocked ? 'Panel terbuka — siap jalankan manual' : 'Masukkan kunci rahasia untuk trigger manual' }}
            </p>
          </div>
          <div v-if="!unlocked" class="flex items-center gap-2 shrink-0">
            <input v-model="secretInput" type="password" placeholder="Masukkan secret..."
              @keyup.enter="unlock"
              class="w-44 text-sm border border-amber-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-300"/>
            <button @click="unlock"
              class="px-4 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-colors">
              Buka
            </button>
          </div>
          <div v-else class="flex items-center gap-2 shrink-0">
            <button @click="manualRun" :disabled="running"
              class="px-4 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl transition-colors flex items-center gap-1.5">
              <svg v-if="running" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ running ? 'Running...' : 'Jalankan Sekarang' }}
            </button>
            <button @click="lock" class="text-xs text-emerald-600 hover:text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg transition-colors">
              Kunci
            </button>
          </div>
        </div>
        <p v-if="unlockError" class="text-xs text-red-500 font-medium mt-2.5 ml-12">{{ unlockError }}</p>
      </div>

      <!-- Log output -->
      <div v-if="lastOutput" class="bg-gray-950 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Output Terakhir</p>
        </div>
        <div class="p-4 font-mono text-xs">
          <span class="text-indigo-400 font-bold">$ artisan invoice:auto-send</span>
          <span class="text-gray-600 ml-2 text-[10px]">{{ lastOutput.time }} · {{ lastOutput.elapsed }}</span>
          <pre class="mt-2 whitespace-pre-wrap leading-relaxed text-emerald-400">{{ lastOutput.output }}</pre>
        </div>
      </div>

      <!-- Aksi otomatis -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <p class="text-sm font-semibold text-gray-800">Yang Dilakukan Tiap 2 Menit</p>
          <p class="text-xs text-gray-400 mt-0.5">Semua aksi otomatis yang dijalankan cron ini</p>
        </div>
        <div class="divide-y divide-gray-50">

          <!-- Auto send -->
          <div class="px-5 py-4 flex items-start gap-4">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-800">Kirim Email Invoice Otomatis</p>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Invoice yang <span class="font-medium text-gray-700">dalam antrean kirim (is_marked ✓)</span> dan
                <span class="font-medium text-gray-700">issue_date sudah lewat atau hari ini</span> →
                generate PDF, kirim via Brevo, status jadi
                <span class="font-mono text-xs bg-green-50 text-green-700 px-1.5 py-0.5 rounded">sent</span>.
              </p>
              <div class="mt-2 flex flex-wrap gap-1.5">
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Skip: sent / paid / frozen</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Skip: client tanpa email</span>
              </div>
              <div v-if="demoResult['auto-send']" class="mt-2 text-xs text-indigo-600 bg-indigo-50 rounded-lg px-3 py-2 font-mono">
                ✓ {{ demoResult['auto-send'] }}
              </div>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0">
              <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">Aktif</span>
              <button @click="openDemoModal('auto-send')" :disabled="demoLoading['auto-send']"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-100 transition-colors disabled:opacity-50 whitespace-nowrap">
                {{ demoLoading['auto-send'] ? 'Membuat...' : 'Buat Demo' }}
              </button>
            </div>
          </div>

          <!-- Auto overdue -->
          <div class="px-5 py-4 flex items-start gap-4">
            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-800">Auto Tandai Jatuh Tempo</p>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Invoice status <span class="font-mono text-xs bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">sent</span> yang
                <span class="font-medium text-gray-700">due_date sudah lewat</span> →
                otomatis ubah ke <span class="font-mono text-xs bg-red-50 text-red-600 px-1.5 py-0.5 rounded">unpaid</span>.
                Berubah di hari setelah due_date.
              </p>
              <div class="mt-2 flex flex-wrap gap-1.5">
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Hanya status: sent</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Skip: draft / paid / frozen</span>
              </div>
              <div v-if="demoResult['auto-overdue']" class="mt-2 text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2 font-mono">
                ✓ {{ demoResult['auto-overdue'] }}
              </div>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0">
              <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">Aktif</span>
              <button @click="openDemoModal('auto-overdue')" :disabled="demoLoading['auto-overdue']"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 transition-colors disabled:opacity-50 whitespace-nowrap">
                {{ demoLoading['auto-overdue'] ? 'Membuat...' : 'Buat Demo' }}
              </button>
            </div>
          </div>

          <!-- Placeholder aksi berikutnya -->
          <div class="px-5 py-4 flex items-start gap-4 opacity-40">
            <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-gray-500">Otomatisasi berikutnya...</p>
              <p class="text-xs text-gray-400 mt-1">Bisa ditambah: reminder jatuh tempo, generate invoice recurring, notifikasi invoice overdue, dll.</p>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-400 border border-gray-200 shrink-0">Belum ada</span>
          </div>

        </div>
      </div>

      <!-- Riwayat runs -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <p class="text-sm font-semibold text-gray-800">Riwayat Eksekusi</p>
          <span class="text-xs text-gray-400">{{ runs.length }} terakhir</span>
        </div>

        <div v-if="!runs.length" class="px-5 py-10 text-center">
          <p class="text-sm text-gray-400">Belum ada riwayat eksekusi</p>
        </div>

        <div v-else class="divide-y divide-gray-50">
          <div v-for="run in runs" :key="run.id"
            class="px-5 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <!-- Status dot -->
                <span class="w-2 h-2 rounded-full mt-1 shrink-0"
                  :class="{
                    'bg-emerald-400': run.status === 'success',
                    'bg-amber-400':   run.status === 'partial',
                    'bg-red-400':     run.status === 'error',
                    'bg-gray-300':    run.status === 'empty',
                  }"/>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-medium text-gray-800">{{ formatDate(run.created_at) }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded-md font-semibold capitalize"
                      :class="{
                        'bg-emerald-50 text-emerald-700': run.status === 'success',
                        'bg-amber-50 text-amber-700':     run.status === 'partial',
                        'bg-red-50 text-red-700':         run.status === 'error',
                        'bg-gray-100 text-gray-500':      run.status === 'empty',
                      }">{{ statusLabel(run.status) }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-500 capitalize">
                      {{ triggerLabel(run.triggered_by) }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ run.invoices_found }} ditemukan ·
                    <span class="text-emerald-600 font-medium">{{ run.invoices_sent }} terkirim</span>
                    <span v-if="run.invoices_failed" class="text-red-500 font-medium"> · {{ run.invoices_failed }} gagal</span>
                    · {{ run.duration_ms }} ms
                  </p>
                </div>
              </div>

              <!-- Expand details toggle -->
              <button v-if="run.details?.length"
                @click="toggleExpand(run.id)"
                class="text-xs text-indigo-500 hover:text-indigo-700 shrink-0 mt-0.5">
                {{ expanded === run.id ? 'Tutup' : 'Detail' }}
              </button>
            </div>

            <!-- Detail rows -->
            <div v-if="expanded === run.id && run.details?.length"
              class="mt-3 ml-5 space-y-1.5">
              <div v-for="d in run.details" :key="d.invoice_id"
                class="flex items-center gap-3 text-xs">
                <span class="w-1.5 h-1.5 rounded-full shrink-0"
                  :class="d.status === 'sent' ? 'bg-emerald-400' : d.status === 'skipped' ? 'bg-gray-300' : 'bg-red-400'"/>
                <span class="font-mono text-gray-600">{{ d.invoice_number }}</span>
                <span class="text-gray-400">{{ d.client }}</span>
                <span v-if="d.status === 'sent'" class="text-emerald-600 font-medium">Terkirim</span>
                <span v-else-if="d.status === 'overdue'" class="text-red-500 font-medium">→ Unpaid (jatuh tempo)</span>
                <span v-else-if="d.status === 'skipped'" class="text-gray-400">Dilewati</span>
                <span v-else class="text-red-500">{{ d.error }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Guide setup -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <p class="text-sm font-semibold text-gray-800">Cara Setup Pengeksekusi</p>
          <button @click="guideOpen = !guideOpen" class="text-xs text-indigo-500 hover:text-indigo-700">
            {{ guideOpen ? 'Tutup' : 'Lihat panduan' }}
          </button>
        </div>

        <div v-if="guideOpen" class="divide-y divide-gray-50">

          <!-- Lokal -->
          <div class="px-5 py-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100">Lokal</span>
              <p class="text-sm font-semibold text-gray-800">Development di komputer sendiri</p>
            </div>
            <p class="text-xs text-gray-500 mb-2">Buka terminal baru (selain yang buat serve), jalankan:</p>
            <div class="bg-gray-950 rounded-xl overflow-hidden">
              <div class="flex items-center justify-between px-3 py-2 border-b border-gray-800">
                <span class="text-xs font-mono text-gray-600 uppercase tracking-wider">Terminal</span>
                <button @click="copy('php artisan schedule:work', 'local')" class="text-xs font-mono text-gray-600 hover:text-blue-400 transition-colors">
                  {{ copied === 'local' ? 'Tersalin ✓' : 'Salin' }}
                </button>
              </div>
              <pre class="px-4 py-3 font-mono text-xs text-blue-300">php artisan schedule:work</pre>
            </div>
            <p class="text-xs text-gray-400 mt-2">Biarkan terminal itu tetap terbuka. Cron akan jalan otomatis tiap 2 menit. Status di atas akan menjadi <span class="text-emerald-600 font-medium">Aktif</span>. Tutup terminal → status jadi <span class="text-red-500 font-medium">Mati</span>.</p>
          </div>

          <!-- Hosting -->
          <div class="px-5 py-4">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-violet-600 border border-violet-100">Hosting</span>
              <p class="text-sm font-semibold text-gray-800">cPanel Cron Jobs</p>
            </div>
            <p class="text-xs text-gray-500 mb-2">Login cPanel → <strong>Cron Jobs</strong> → tambah baris baru. Set jadwal <strong>Every 5 Minutes</strong>, command:</p>
            <div class="bg-gray-950 rounded-xl overflow-hidden">
              <div class="flex items-center justify-between px-3 py-2 border-b border-gray-800">
                <span class="text-xs font-mono text-gray-600 uppercase tracking-wider">cPanel Command</span>
                <button @click="copy(props.cronUrl, 'hosting')" class="text-xs font-mono text-gray-600 hover:text-blue-400 transition-colors">
                  {{ copied === 'hosting' ? 'Tersalin ✓' : 'Salin' }}
                </button>
              </div>
              <pre class="px-4 py-3 font-mono text-xs text-amber-300 whitespace-pre-wrap break-all">/usr/bin/curl -s "{{ props.cronUrl }}"</pre>
            </div>
            <p class="text-xs text-gray-400 mt-2">Tunggu 2 menit setelah disimpan, refresh halaman ini. Kalau status jadi <span class="text-emerald-600 font-medium">Aktif</span> berarti berhasil.</p>
          </div>

          <!-- Test cepat -->
          <div class="px-5 py-4 bg-gray-50">
            <p class="text-xs font-semibold text-gray-600 mb-1">Test cepat — buka di browser:</p>
            <div class="flex items-center gap-2">
              <code class="flex-1 text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 text-gray-600 font-mono truncate">{{ props.cronUrl }}</code>
              <button @click="copyUrl" class="p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-100 transition-colors shrink-0">
                <svg v-if="!copiedUrl" class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <svg v-else class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
              </button>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">Kalau muncul <code class="text-gray-500">{"status":"ok"}</code> berarti URL dan secret sudah benar.</p>
          </div>

        </div>
      </div>

    </div>

    <!-- Modal pilih client untuk demo -->
    <Teleport to="body">
      <div v-if="demoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="demoModal = null"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <p class="text-sm font-bold text-gray-900 mb-1">
            Buat Demo — {{ demoModal === 'auto-send' ? 'Auto-Send' : 'Auto-Overdue' }}
          </p>
          <p class="text-xs text-gray-400 mb-4">Pilih client yang akan dibuatkan invoice demo</p>

          <div class="space-y-1.5 max-h-52 overflow-y-auto mb-5">
            <label v-for="c in props.clients" :key="c.id"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-colors"
              :class="demoClientId === c.id
                ? 'border-indigo-300 bg-indigo-50'
                : 'border-gray-100 hover:bg-gray-50'">
              <input type="radio" :value="c.id" v-model="demoClientId" class="w-3.5 h-3.5 text-indigo-600"/>
              <span class="text-sm text-gray-700">{{ c.company_name }}</span>
            </label>
          </div>

          <div class="flex gap-2">
            <button @click="demoModal = null"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="submitDemo" :disabled="!demoClientId"
              class="flex-1 px-4 py-2 text-sm font-medium text-white rounded-xl transition-colors disabled:opacity-50"
              :class="demoModal === 'auto-send' ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-red-500 hover:bg-red-600'">
              Buat Invoice Demo
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  runs:    { type: Array,  default: () => [] },
  lastRun: { type: Object, default: null },
  isAlive: { type: Boolean, default: false },
  cronUrl: { type: String, default: '' },
  clients: { type: Array,  default: () => [] },
})

const secretInput = ref('')
const secret      = ref('')
const unlocked    = ref(false)
const unlockError = ref('')
const running     = ref(false)
const copied      = ref(null)
const copiedUrl   = ref(false)
const expanded    = ref(null)
const lastOutput  = ref(null)
const guideOpen   = ref(false)
const demoLoading   = ref({ 'auto-send': false, 'auto-overdue': false })
const demoResult    = ref({ 'auto-send': null,  'auto-overdue': null })
const demoModal     = ref(null)   // 'auto-send' | 'auto-overdue' | null
const demoClientId  = ref(null)

// reactive copy of runs so we can prepend new ones
const runs = ref([...props.runs])

const statusBorder    = computed(() => props.isAlive ? 'border-emerald-200 bg-emerald-50/30' : 'border-red-200 bg-red-50/30')
const statusIconBg    = computed(() => props.isAlive ? 'bg-emerald-100' : 'bg-red-100')
const statusIconColor = computed(() => props.isAlive ? 'text-emerald-600' : 'text-red-400')
const statusTextColor = computed(() => props.isAlive ? 'text-emerald-700' : 'text-red-600')

const minutesAgo = computed(() => {
  if (!props.lastRun) return '-'
  const diff = Math.floor((Date.now() - new Date(props.lastRun.created_at).getTime()) / 60000)
  if (diff < 1) return '< 1 menit'
  if (diff < 60) return `${diff} menit`
  const h = Math.floor(diff / 60)
  return `${h} jam ${diff % 60} menit`
})

function unlock() {
  if (!secretInput.value.trim()) { unlockError.value = 'Masukkan kunci rahasia.'; return }
  secret.value      = secretInput.value.trim()
  unlocked.value    = true
  unlockError.value = ''
  secretInput.value = ''
}

function lock() {
  unlocked.value = false
  secret.value   = ''
}

async function manualRun() {
  running.value = true
  try {
    const res = await axios.post(route('admin.cron.run'), { secret: secret.value })
    lastOutput.value = { output: res.data.output, elapsed: res.data.elapsed, time: res.data.time }
    if (res.data.run) runs.value.unshift(res.data.run)
    if (res.status === 403) lock()
  } catch (err) {
    const data = err.response?.data
    lastOutput.value = {
      output:  data?.output ?? err.message,
      elapsed: '-',
      time:    new Date().toLocaleTimeString('id-ID'),
    }
    if (err.response?.status === 403) lock()
  } finally {
    running.value = false
  }
}

function copyUrl() {
  navigator.clipboard.writeText(props.cronUrl).then(() => {
    copiedUrl.value = true
    setTimeout(() => { copiedUrl.value = false }, 2000)
  })
}

function openDemoModal(type) {
  demoModal.value    = type
  demoClientId.value = props.clients[0]?.id ?? null
}

async function submitDemo() {
  const type = demoModal.value
  demoModal.value     = null
  demoLoading.value[type] = true
  demoResult.value[type]  = null
  try {
    const res = await axios.post(route('admin.cron.demo'), { type, client_id: demoClientId.value })
    demoResult.value[type] = `${res.data.invoice_number} (${res.data.client}) — ${res.data.info}`
  } catch (err) {
    demoResult.value[type] = 'Gagal: ' + (err.response?.data?.error ?? err.message)
  } finally {
    demoLoading.value[type] = false
  }
}

function copy(text, key) {
  navigator.clipboard.writeText(text).then(() => {
    copied.value = key
    setTimeout(() => { copied.value = null }, 2000)
  })
}

function toggleExpand(id) {
  expanded.value = expanded.value === id ? null : id
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function statusLabel(s) {
  return { success: 'Berhasil', partial: 'Sebagian', error: 'Gagal', empty: 'Tidak Ada Invoice' }[s] ?? s
}

function triggerLabel(t) {
  return { schedule: 'Otomatis', http: 'HTTP', manual: 'Manual' }[t] ?? t
}
</script>
