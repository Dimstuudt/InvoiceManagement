<template>
  <AppLayout title="Artisan Panel">
    <div class="space-y-6 max-w-4xl mx-auto">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Artisan Panel</h2>
          <p class="text-sm text-gray-400 mt-0.5">Jalankan perintah server langsung dari browser</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
          </svg>
          Admin Only
        </span>
      </div>

      <!-- Secret unlock -->
      <div class="bg-white rounded-2xl border shadow-sm p-5"
        :class="unlocked ? 'border-emerald-200 bg-emerald-50/30' : 'border-amber-200 bg-amber-50/30'">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
            :class="unlocked ? 'bg-emerald-100' : 'bg-amber-100'">
            <svg v-if="unlocked" class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            </svg>
            <svg v-else class="w-4.5 h-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM16 7a4 4 0 10-8 0v4h8V7z"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold" :class="unlocked ? 'text-emerald-700' : 'text-amber-700'">
              {{ unlocked ? 'Panel terbuka — perintah siap dijalankan' : 'Masukkan kunci rahasia untuk membuka panel' }}
            </p>
            <p class="text-xs mt-0.5" :class="unlocked ? 'text-emerald-500' : 'text-amber-500'">
              {{ unlocked ? 'Kunci disimpan sementara di sesi ini' : 'Nilai ARTISAN_SECRET dari file .env' }}
            </p>
          </div>
          <div v-if="!unlocked" class="flex items-center gap-2 shrink-0">
            <input v-model="secretInput" type="password" placeholder="Masukkan secret..."
              @keyup.enter="unlock"
              class="w-48 text-sm border border-amber-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-amber-300"/>
            <button @click="unlock"
              class="px-4 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-colors">
              Buka
            </button>
          </div>
          <button v-else @click="lock"
            class="text-xs font-medium text-emerald-600 hover:text-emerald-800 border border-emerald-200 hover:border-emerald-400 px-3 py-1.5 rounded-lg transition-colors shrink-0">
            Kunci ulang
          </button>
        </div>
        <p v-if="unlockError" class="text-xs text-red-500 font-medium mt-2.5 ml-12">{{ unlockError }}</p>
      </div>

      <!-- Command grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div v-for="cmd in commands" :key="cmd.key"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col gap-3"
          :class="!unlocked ? 'opacity-50 pointer-events-none select-none' : ''">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="w-2 h-2 rounded-full shrink-0" :class="cmd.dot"/>
              <p class="text-sm font-semibold text-gray-800">{{ cmd.label }}</p>
            </div>
            <p class="text-xs text-gray-400 leading-relaxed">{{ cmd.desc }}</p>
          </div>
          <button @click="confirmOrRun(cmd)"
            :disabled="running === cmd.key"
            class="mt-auto w-full py-2 px-3 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1.5"
            :class="[cmd.btnCls, running === cmd.key ? 'opacity-60 cursor-not-allowed' : '']">
            <svg v-if="running === cmd.key" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ running === cmd.key ? 'Running...' : cmd.label }}
          </button>
        </div>
      </div>

      <!-- Output log -->
      <div class="bg-gray-950 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Output Log</p>
          <button v-if="logs.length" @click="logs = []"
            class="text-xs text-gray-600 hover:text-gray-400 transition-colors">
            Bersihkan
          </button>
        </div>
        <div class="p-4 font-mono text-xs min-h-[120px] max-h-[400px] overflow-y-auto space-y-4" ref="logEl">
          <p v-if="!logs.length" class="text-gray-700 italic">Output perintah akan tampil di sini...</p>
          <div v-for="(log, i) in logs" :key="i" class="border-b border-gray-900 pb-4 last:border-0 last:pb-0">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="text-indigo-400 font-bold">$ artisan {{ log.command }}</span>
              <span class="text-gray-600 text-[10px]">{{ log.time }} · {{ log.elapsed }}</span>
            </div>
            <pre class="whitespace-pre-wrap leading-relaxed"
              :class="log.status === 'ok' ? 'text-emerald-400' : 'text-red-400'">{{ log.output }}</pre>
          </div>
        </div>
      </div>

    </div>

    <!-- Confirm modal -->
    <Teleport to="body">
      <div v-if="confirmTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="confirmTarget = null"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">{{ confirmTarget?.label }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Perintah ini bisa mengubah data secara permanen</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="confirmTarget = null"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="runCmd(confirmTarget); confirmTarget = null"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
              Ya, Jalankan
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, nextTick } from 'vue'
import axios from 'axios'

defineProps({ hasSecret: Boolean })

const secretInput = ref('')
const secret      = ref('')
const unlocked    = ref(false)
const unlockError = ref('')
const running     = ref(null)
const logs        = ref([])
const logEl       = ref(null)
const confirmTarget = ref(null)

const commands = [
  { key: 'migrate',          label: 'Migrate',          desc: 'Jalankan migrasi yang belum dijalankan',          dot: 'bg-blue-400',    btnCls: 'bg-blue-600 hover:bg-blue-700 text-white',    confirm: true  },
  { key: 'migrate:status',   label: 'Migrate Status',   desc: 'Lihat status semua migrasi',                      dot: 'bg-indigo-400',  btnCls: 'bg-indigo-600 hover:bg-indigo-700 text-white', confirm: false },
  { key: 'migrate:rollback', label: 'Rollback',         desc: 'Rollback batch migrasi terakhir',                 dot: 'bg-red-400',     btnCls: 'bg-red-600 hover:bg-red-700 text-white',      confirm: true  },
  { key: 'storage:link',     label: 'Storage Link',     desc: 'Buat symlink public/storage → storage/app/public',dot: 'bg-teal-400',    btnCls: 'bg-teal-600 hover:bg-teal-700 text-white',    confirm: false },
  { key: 'optimize',         label: 'Optimize',         desc: 'Cache config + route + view sekaligus',           dot: 'bg-emerald-400', btnCls: 'bg-emerald-600 hover:bg-emerald-700 text-white',confirm: false },
  { key: 'optimize:clear',   label: 'Optimize Clear',   desc: 'Hapus semua cache sekaligus',                     dot: 'bg-orange-400',  btnCls: 'bg-orange-600 hover:bg-orange-700 text-white', confirm: false },
  { key: 'config:cache',     label: 'Config Cache',     desc: 'Cache konfigurasi aplikasi',                      dot: 'bg-violet-400',  btnCls: 'bg-violet-600 hover:bg-violet-700 text-white', confirm: false },
  { key: 'config:clear',     label: 'Config Clear',     desc: 'Hapus cache konfigurasi',                         dot: 'bg-amber-400',   btnCls: 'bg-amber-500 hover:bg-amber-600 text-white',   confirm: false },
  { key: 'route:cache',      label: 'Route Cache',      desc: 'Cache semua route',                               dot: 'bg-violet-400',  btnCls: 'bg-violet-600 hover:bg-violet-700 text-white', confirm: false },
  { key: 'route:clear',      label: 'Route Clear',      desc: 'Hapus cache route',                               dot: 'bg-amber-400',   btnCls: 'bg-amber-500 hover:bg-amber-600 text-white',   confirm: false },
  { key: 'view:cache',       label: 'View Cache',       desc: 'Compile semua Blade template',                    dot: 'bg-violet-400',  btnCls: 'bg-violet-600 hover:bg-violet-700 text-white', confirm: false },
  { key: 'view:clear',       label: 'View Clear',       desc: 'Hapus cache Blade template',                      dot: 'bg-amber-400',   btnCls: 'bg-amber-500 hover:bg-amber-600 text-white',   confirm: false },
  { key: 'cache:clear',      label: 'Cache Clear',      desc: 'Hapus application cache (Redis/file)',            dot: 'bg-amber-400',   btnCls: 'bg-amber-500 hover:bg-amber-600 text-white',   confirm: false },
  { key: 'queue:restart',    label: 'Queue Restart',    desc: 'Restart queue worker setelah deploy',             dot: 'bg-sky-400',     btnCls: 'bg-sky-600 hover:bg-sky-700 text-white',      confirm: false },
]

function unlock() {
  if (!secretInput.value.trim()) { unlockError.value = 'Masukkan kunci rahasia terlebih dahulu.'; return }
  secret.value      = secretInput.value.trim()
  unlocked.value    = true
  unlockError.value = ''
  secretInput.value = ''
}

function lock() {
  unlocked.value = false
  secret.value   = ''
}

function confirmOrRun(cmd) {
  if (cmd.confirm) { confirmTarget.value = cmd; return }
  runCmd(cmd)
}

async function runCmd(cmd) {
  running.value = cmd.key
  try {
    const res = await axios.post(route('admin.artisan.run'), {
      command: cmd.key,
      secret:  secret.value,
    })
    pushLog({ ...res.data })
    if (res.data.status === 'error') {
      // secret salah → kunci ulang
      if (res.status === 403) lock()
    }
  } catch (err) {
    const data = err.response?.data
    pushLog({
      command: cmd.key,
      status:  'error',
      output:  data?.output ?? err.message,
      elapsed: '-',
      time:    new Date().toLocaleTimeString('id-ID'),
    })
    if (err.response?.status === 403) lock()
  } finally {
    running.value = null
  }
}

async function pushLog(entry) {
  logs.value.push(entry)
  await nextTick()
  if (logEl.value) logEl.value.scrollTop = logEl.value.scrollHeight
}
</script>
