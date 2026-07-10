<template>
  <AppLayout title="Log Cron Otomatis">
    <div class="max-w-4xl mx-auto space-y-5">

      <!-- ── Header ─────────────────────────────────────────────── -->
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

      <!-- ── Stats ──────────────────────────────────────────────── -->
      <div class="grid grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
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
            <svg class="w-5 h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-purple-700">{{ stats.auto_sent.toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Invoice Terkirim</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-emerald-700">{{ (stats.receipt_sent ?? 0).toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Receipt Terkirim</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-orange-100 shadow-sm p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-extrabold leading-none tabular-nums text-orange-700">{{ stats.overdue.toLocaleString('id-ID') }}</p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Auto Unpaid</p>
          </div>
        </div>
      </div>

      <!-- ── Filter bar ─────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <div class="flex flex-wrap gap-2 items-center">
          <button v-for="q in quickDates" :key="q.key"
            @click="setQuickDate(q)"
            :class="['px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all border',
              activeQuick === q.key
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-slate-50 text-slate-500 border-slate-100 hover:bg-slate-100']">
            {{ q.label }}
          </button>

          <div class="h-4 w-px bg-slate-100 mx-0.5"/>

          <div class="flex items-center gap-1.5">
            <input v-model="dateFrom" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
            <span class="text-slate-300 text-xs">–</span>
            <input v-model="dateTo" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
          </div>

          <div class="h-4 w-px bg-slate-100 mx-0.5"/>

          <select v-model="perPage" @change="applyFilters"
            class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400">
            <option value="25">25 / hal</option>
            <option value="50">50 / hal</option>
            <option value="100">100 / hal</option>
          </select>

          <button v-if="hasActiveFilters" @click="clearFilters"
            class="ml-auto flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reset
          </button>
        </div>
      </div>

      <!-- ── Session feed ───────────────────────────────────────── -->
      <div v-if="groupedSessions.length > 0" class="space-y-7">
        <div v-for="[dateLabel, sessions] in groupedSessions" :key="dateLabel" class="space-y-2">

          <!-- Date divider -->
          <div class="flex items-center gap-3">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ dateLabel }}</span>
            <div class="flex-1 h-px bg-slate-100"/>
            <span class="text-[11px] text-slate-300 tabular-nums">
              {{ sessions.length }} sesi · {{ sessions.reduce((s, x) => s + x.entries.length, 0) }} aksi
            </span>
          </div>

          <!-- Session cards -->
          <div class="space-y-2">
            <div v-for="(session, si) in sessions" :key="si"
              class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">

              <!-- Session header (click to collapse/expand) -->
              <button
                type="button"
                @click="toggleSession(dateLabel + si)"
                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50/70 transition-colors text-left">

                <!-- Time badge -->
                <span class="text-[11px] font-mono font-bold tabular-nums text-slate-400 shrink-0 w-10">
                  {{ formatTime(session.entries[0]?.created_at) }}
                </span>

                <!-- Divider -->
                <div class="w-px h-4 bg-slate-100 shrink-0"/>

                <!-- Action summary chips -->
                <div class="flex-1 flex items-center gap-1.5 flex-wrap">
                  <span v-if="session.sentCount > 0"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full ring-1 ring-purple-200/50">
                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                    {{ session.sentCount }} invoice terkirim
                  </span>
                  <!-- Stage breakdown pills -->
                  <template v-if="session.sentCount > 0">
                    <template v-for="stage in ['send1','send2','send3','send4','send5']" :key="stage">
                      <span v-if="session.stageCounts[stage]"
                        :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-md tabular-nums', stageColor(stage)]">
                        {{ session.stageCounts[stage] }}× {{ stageLabel(stage) }}
                      </span>
                    </template>
                  </template>
                  <span v-if="session.receiptCount > 0"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full ring-1 ring-emerald-200/50">
                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session.receiptCount }} receipt terkirim
                  </span>
                  <span v-if="session.overdueCount > 0"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold bg-orange-50 text-orange-700 px-2 py-0.5 rounded-full ring-1 ring-orange-200/50">
                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    {{ session.overdueCount }} jadi unpaid
                  </span>
                  <span v-if="session.sentCount === 0 && session.receiptCount === 0 && session.overdueCount === 0"
                    class="text-[11px] text-slate-300">Tidak ada aksi</span>
                </div>

                <!-- Expand chevron -->
                <svg :class="['w-3.5 h-3.5 text-slate-300 shrink-0 transition-transform duration-200',
                  isExpanded(dateLabel + si) ? '' : '-rotate-90']"
                  fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
              </button>

              <!-- Entry list (expandable) -->
              <div v-show="isExpanded(dateLabel + si)">
                <div class="border-t border-slate-100">
                  <div v-for="log in session.entries" :key="log.id"
                    :class="[
                      'flex items-center gap-3 px-4 py-2.5 border-b border-slate-50 last:border-b-0',
                      'hover:bg-slate-50/40 transition-colors group/row'
                    ]">

                    <!-- Action dot -->
                    <div :class="['w-1.5 h-1.5 rounded-full shrink-0',
                      log.action === 'invoice.auto_sent' ? 'bg-purple-300' :
                      log.action === 'invoice.receipt_sent' ? 'bg-emerald-400' : 'bg-orange-300']"/>

                    <!-- Invoice number -->
                    <Link v-if="log.subject_id"
                      :href="route('invoices.show', log.subject_id)"
                      class="text-[11px] font-mono font-bold text-indigo-600 hover:text-indigo-800 hover:underline shrink-0 tabular-nums"
                      @click.stop>
                      {{ log.subject_label }}
                    </Link>
                    <span v-else class="text-[11px] font-mono font-semibold text-slate-500 shrink-0">
                      {{ log.subject_label }}
                    </span>

                    <!-- Stage badge -->
                    <span v-if="log.detail?.stage"
                      :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-md shrink-0 tabular-nums', stageColor(log.detail.stage)]">
                      {{ stageLabel(log.detail.stage) }}
                    </span>

                    <!-- Detail -->
                    <span class="flex-1 text-[11px] text-slate-400 truncate min-w-0">
                      <template v-if="log.action === 'invoice.auto_sent' || log.action === 'invoice.receipt_sent'">
                        <span class="text-slate-300 mr-1">→</span>
                        {{ Array.isArray(log.detail?.to) ? log.detail.to.map(maskEmail).join(', ') : maskEmail(log.detail?.to ?? '?') }}
                      </template>
                      <template v-else>
                        <span class="text-slate-300 mr-1">→</span>
                        status jadi
                        <span class="font-semibold text-orange-600 bg-orange-50 px-1 py-0.5 rounded text-[10px]">unpaid</span>
                        <span class="ml-1 text-slate-300">·</span>
                        <span class="ml-1">due {{ log.detail?.due_date ?? '?' }}</span>
                      </template>
                    </span>

                    <!-- Timestamp -->
                    <span class="text-[10px] tabular-nums text-slate-300 shrink-0">{{ formatTime(log.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Empty state ────────────────────────────────────────── -->
      <div v-else class="bg-white rounded-2xl border border-slate-100 shadow-sm py-20 text-center">
        <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center">
          <svg class="w-6 h-6 text-slate-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="mt-4 text-sm font-semibold text-slate-600">Belum ada aktivitas cron</h3>
        <p class="mt-1 text-xs text-slate-400">
          {{ hasActiveFilters ? 'Tidak ada log yang cocok dengan filter aktif.' : 'Cron belum pernah mengirim atau mengubah status invoice.' }}
        </p>
        <button v-if="hasActiveFilters" @click="clearFilters" class="mt-3 text-xs text-indigo-600 hover:underline">
          Hapus filter
        </button>
      </div>

      <!-- ── Pagination ─────────────────────────────────────────── -->
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

// ── Filter state ─────────────────────────────────────────────
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

const hasActiveFilters = computed(() => !!(dateFrom.value || dateTo.value || perPage.value !== '50'))

function clearFilters() {
  dateFrom.value    = ''
  dateTo.value      = ''
  perPage.value     = '50'
  activeQuick.value = ''
  applyFilters()
}

const quickDates = [
  { key: 'today',     label: 'Hari Ini' },
  { key: 'yesterday', label: 'Kemarin'  },
  { key: '7d',        label: '7 Hari'   },
  { key: '30d',       label: '30 Hari'  },
]

const toISO = (d) => d.toISOString().substring(0, 10)

function maskEmail(email) {
  if (!email || typeof email !== 'string') return email
  const [local, domain] = email.split('@')
  if (!domain) return email
  const maskedLocal = local.slice(0, 2) + '***'
  const dot = domain.lastIndexOf('.')
  const domainName = dot > 0 ? domain.slice(0, dot) : domain
  const tld        = dot > 0 ? domain.slice(dot)    : ''
  return `${maskedLocal}@${domainName.slice(0, 2)}***${tld}`
}

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

// ── Session grouping ──────────────────────────────────────────
// Entries within 5 minutes of each other = same cron session
const SESSION_GAP_MS = 5 * 60 * 1000

const groupedSessions = computed(() => {
  const entries = [...(props.logs.data ?? [])].reverse() // process asc
  if (!entries.length) return []

  const sessions = []
  let cur = null

  entries.forEach(log => {
    const t = toDate(log.created_at).getTime()
    if (!cur || t - cur.lastTime > SESSION_GAP_MS) {
      cur = { startTime: log.created_at, lastTime: t, entries: [], sentCount: 0, receiptCount: 0, overdueCount: 0, stageCounts: {} }
      sessions.push(cur)
    } else {
      cur.lastTime = t
    }
    cur.entries.push(log)
    if (log.action === 'invoice.auto_sent') {
      cur.sentCount++
      const s = log.detail?.stage
      if (s) cur.stageCounts[s] = (cur.stageCounts[s] ?? 0) + 1
    } else if (log.action === 'invoice.receipt_sent') {
      cur.receiptCount++
    } else {
      cur.overdueCount++
    }
  })

  // Reverse back to desc; entries inside each session also reversed (newest first)
  sessions.reverse().forEach(s => s.entries.reverse())

  // Group sessions by calendar date
  const byDate = new Map()
  sessions.forEach(s => {
    const key = toDate(s.entries[0].created_at).toLocaleDateString('id-ID', {
      weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    })
    if (!byDate.has(key)) byDate.set(key, [])
    byDate.get(key).push(s)
  })

  return [...byDate.entries()]
})

// ── Session expand / collapse ─────────────────────────────────
// Default: all expanded. We track which are manually collapsed.
const collapsedSessions = ref(new Set())

const isExpanded = (id) => !collapsedSessions.value.has(id)

function toggleSession(id) {
  const next = new Set(collapsedSessions.value)
  next.has(id) ? next.delete(id) : next.add(id)
  collapsedSessions.value = next
}

// ── Helpers ───────────────────────────────────────────────────
// Laravel mengirim timestamp tanpa timezone info — parse sebagai local time
const toDate = (dt) => dt ? new Date(String(dt).replace(' ', 'T')) : new Date(NaN)

const formatTime = (dt) =>
  toDate(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })

const STAGE_META = {
  send1: { label: 'Send 1', color: 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200/60' },
  send2: { label: 'Send 2', color: 'bg-blue-50 text-blue-600 ring-1 ring-blue-200/60' },
  send3: { label: 'Send 3', color: 'bg-amber-50 text-amber-600 ring-1 ring-amber-200/60' },
  send4: { label: 'Send 4', color: 'bg-orange-50 text-orange-600 ring-1 ring-orange-200/60' },
  send5: { label: 'Send 5', color: 'bg-red-50 text-red-600 ring-1 ring-red-200/60' },
  paid:  { label: 'Paid',   color: 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200/60' },
}
const stageLabel = (s) => STAGE_META[s]?.label ?? s
const stageColor = (s) => STAGE_META[s]?.color ?? 'bg-slate-50 text-slate-500 ring-1 ring-slate-200/60'

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
