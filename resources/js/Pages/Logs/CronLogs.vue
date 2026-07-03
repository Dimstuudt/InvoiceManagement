<template>
  <AppLayout title="Log Cron Otomatis">
    <div class="max-w-4xl mx-auto space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link :href="route('admin.cron')"
            class="w-8 h-8 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center hover:bg-slate-50 transition-colors shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
          </Link>
          <div>
            <h2 class="text-base font-bold text-slate-800 leading-tight">Log Cron Otomatis</h2>
            <p class="text-xs text-slate-400 mt-0.5">Riwayat aktivitas yang dijalankan sistem secara otomatis</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button v-if="stats.total > 0" @click="deleteAll"
            class="text-xs text-red-500 hover:text-red-700 border border-red-200 rounded-xl px-3 py-1.5 bg-white hover:bg-red-50 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Hapus Semua
          </button>
          <Link :href="route('logs.index')"
            class="text-xs text-slate-500 hover:text-slate-700 border border-slate-200 rounded-xl px-3 py-1.5 bg-white hover:bg-slate-50 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Log Aktivitas
          </Link>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-slate-800">{{ stats.total.toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Total Aksi Cron</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-purple-700">{{ stats.auto_sent.toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Auto Terkirim</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-orange-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-orange-700">{{ stats.overdue.toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Auto Unpaid</p>
          </div>
        </div>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3">
        <div class="flex flex-wrap gap-2 items-center">
          <!-- Quick dates -->
          <button v-for="q in quickDates" :key="q.key"
            @click="setQuickDate(q)"
            :class="['px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all border',
              activeQuick === q.key
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100']">
            {{ q.label }}
          </button>

          <div class="h-5 w-px bg-slate-200 mx-1"/>

          <!-- Date range -->
          <div class="flex items-center gap-1.5">
            <input v-model="dateFrom" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
            <span class="text-slate-300 text-xs">–</span>
            <input v-model="dateTo" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
          </div>

          <div class="h-5 w-px bg-slate-200 mx-1"/>

          <select v-model="perPage" @change="applyFilters"
            class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400">
            <option value="25">25 / hal</option>
            <option value="50">50 / hal</option>
            <option value="100">100 / hal</option>
          </select>

          <button v-if="hasActiveFilters" @click="clearFilters"
            class="ml-auto flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reset
          </button>
        </div>
      </div>

      <!-- Feed -->
      <div v-if="logs.data.length > 0" class="space-y-7">
        <div v-for="[dateLabel, entries] in groupedLogs" :key="dateLabel">

          <div class="flex items-center gap-3">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ dateLabel }}</span>
            <div class="flex-1 h-px bg-slate-100"/>
            <span class="text-[11px] text-slate-300 tabular-nums">{{ entries.length }} aksi</span>
          </div>

          <div class="mt-3 space-y-2">
            <div v-for="log in entries" :key="log.id"
              class="bg-white rounded-xl border shadow-sm hover:shadow hover:border-slate-200 transition-all duration-150"
              :class="log.action === 'invoice.auto_sent' ? 'border-purple-100' : 'border-orange-100'">
              <div class="flex gap-3 px-4 py-3 items-start">

                <!-- Time -->
                <div class="w-11 shrink-0 pt-0.5 text-center">
                  <span class="text-[11px] font-semibold text-slate-400 tabular-nums">{{ formatTime(log.created_at) }}</span>
                </div>

                <!-- Icon -->
                <div class="pt-0.5 shrink-0">
                  <div :class="['w-7 h-7 rounded-lg flex items-center justify-center',
                    log.action === 'invoice.auto_sent' ? 'bg-purple-50' : 'bg-orange-50']">
                    <!-- email icon for auto_sent -->
                    <svg v-if="log.action === 'invoice.auto_sent'"
                      class="w-3.5 h-3.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                    <!-- clock icon for auto_overdue -->
                    <svg v-else
                      class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                  </div>
                </div>

                <!-- Body -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <span :class="['inline-flex items-center rounded-lg px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset shrink-0',
                      log.action === 'invoice.auto_sent'
                        ? 'bg-purple-50 text-purple-700 ring-purple-600/20'
                        : 'bg-orange-50 text-orange-700 ring-orange-600/20']">
                      {{ log.action === 'invoice.auto_sent' ? 'Auto Kirim' : 'Auto Unpaid' }}
                    </span>
                    <Link v-if="log.subject_id"
                      :href="route('invoices.show', log.subject_id)"
                      class="text-sm font-semibold text-slate-700 hover:text-indigo-600 transition-colors font-mono truncate">
                      {{ log.subject_label }}
                    </Link>
                    <span v-else class="text-sm font-semibold text-slate-700 font-mono truncate">{{ log.subject_label }}</span>
                  </div>

                  <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    <template v-if="log.action === 'invoice.auto_sent'">
                      Dikirim ke
                      <span class="font-medium text-slate-700">
                        {{ Array.isArray(log.detail?.to) ? log.detail.to.join(', ') : (log.detail?.to ?? '?') }}
                      </span>
                    </template>
                    <template v-else>
                      Status berubah ke <span class="font-mono font-semibold text-orange-700 text-[11px] bg-orange-50 px-1.5 py-0.5 rounded">unpaid</span>
                      karena due date
                      <span class="font-medium text-slate-700">{{ log.detail?.due_date ?? '?' }}</span>
                      sudah lewat
                    </template>
                  </p>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-sm py-20 text-center">
        <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center">
          <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="mt-4 text-sm font-semibold text-slate-700">Belum ada aktivitas cron</h3>
        <p class="mt-1 text-xs text-slate-400">Cron belum pernah mengirim atau mengubah status invoice.</p>
      </div>

      <!-- Pagination -->
      <div v-if="logs.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
        <p class="text-xs text-slate-400">
          Menampilkan
          <span class="font-semibold text-slate-600">{{ logs.from }}–{{ logs.to }}</span>
          dari
          <span class="font-semibold text-slate-600">{{ (logs.total ?? 0).toLocaleString('id-ID') }}</span>
          entri
        </p>
        <div class="flex items-center gap-1.5">
          <button @click="goToPage(logs.prev_page_url)" :disabled="!logs.prev_page_url"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition font-medium">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Sebelumnya
          </button>
          <span class="px-3 py-1.5 text-xs font-semibold text-slate-500 bg-slate-50 rounded-lg border border-slate-200 tabular-nums">
            {{ logs.current_page }} / {{ logs.last_page }}
          </span>
          <button @click="goToPage(logs.next_page_url)" :disabled="!logs.next_page_url"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition font-medium">
            Berikutnya
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
          </button>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  logs:    { type: Object, default: () => ({ data: [] }) },
  stats:   { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

const dateFrom    = ref(props.filters.date_from ?? '')
const dateTo      = ref(props.filters.date_to   ?? '')
const perPage     = ref(String(props.filters.per_page ?? '50'))
const activeQuick = ref('')

function buildParams() {
  return {
    ...(dateFrom.value ? { date_from: dateFrom.value } : {}),
    ...(dateTo.value   ? { date_to:   dateTo.value   } : {}),
    ...(perPage.value !== '50' ? { per_page: perPage.value } : {}),
  }
}

function applyFilters() {
  router.get(route('logs.cron'), buildParams(), { preserveState: true, replace: true })
}

const goToPage = (url) => { if (url) router.get(url, {}, { preserveState: false }) }

const hasActiveFilters = computed(() => dateFrom.value || dateTo.value || perPage.value !== '50')

function clearFilters() {
  dateFrom.value    = ''
  dateTo.value      = ''
  perPage.value     = '50'
  activeQuick.value = ''
  applyFilters()
}

const quickDates = [
  { key: 'today',     label: 'Hari Ini' },
  { key: 'yesterday', label: 'Kemarin' },
  { key: '7d',        label: '7 Hari' },
  { key: '30d',       label: '30 Hari' },
]

const toISO = (d) => d.toISOString().substring(0, 10)

function setQuickDate(q) {
  if (activeQuick.value === q.key) {
    activeQuick.value = ''
    dateFrom.value = ''
    dateTo.value   = ''
    applyFilters()
    return
  }
  activeQuick.value = q.key
  const now   = new Date()
  const today = toISO(now)
  if (q.key === 'today') {
    dateFrom.value = today; dateTo.value = today
  } else if (q.key === 'yesterday') {
    const y = new Date(now); y.setDate(y.getDate() - 1); const ys = toISO(y)
    dateFrom.value = ys; dateTo.value = ys
  } else if (q.key === '7d') {
    const d = new Date(now); d.setDate(d.getDate() - 6)
    dateFrom.value = toISO(d); dateTo.value = today
  } else if (q.key === '30d') {
    const d = new Date(now); d.setDate(d.getDate() - 29)
    dateFrom.value = toISO(d); dateTo.value = today
  }
  applyFilters()
}

const groupedLogs = computed(() => {
  const groups = {}
  ;(props.logs.data ?? []).forEach(log => {
    const key = new Date(log.created_at).toLocaleDateString('id-ID', {
      weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    })
    ;(groups[key] ??= []).push(log)
  })
  return Object.entries(groups)
})

const formatTime = (dt) =>
  new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })

function deleteAll() {
  Swal.fire({
    title: 'Hapus semua log cron?',
    text: `${props.stats.total} entri akan dihapus permanen.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Ya, Hapus Semua',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('logs.cron.delete'))
  })
}
</script>
