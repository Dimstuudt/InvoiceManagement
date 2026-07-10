<template>
  <AppLayout title="Activity Log">
    <div class="max-w-5xl mx-auto space-y-5">

      <!-- Stats Row -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div v-for="card in statCards" :key="card.key"
          :class="['bg-white rounded-2xl border shadow-sm p-4 flex items-center gap-3 cursor-pointer transition-shadow hover:shadow-md', card.border,
            kategori === (card.key === 'total' ? '' : card.key) ? 'ring-2 ring-offset-1 ' + card.ring : '']"
          @click="setKategori(card.key === 'total' ? '' : card.key)">
          <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', card.iconBg]">
            <svg class="w-5 h-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon"/>
            </svg>
          </div>
          <div>
            <p :class="['text-2xl font-extrabold leading-none tabular-nums', card.valueColor]">
              {{ (stats[card.key] ?? 0).toLocaleString('id-ID') }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">{{ card.label }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3 space-y-3">

        <!-- Row 1: Search + Kategori -->
        <div class="flex flex-col sm:flex-row gap-3 items-center">
          <div class="relative flex-1 w-full">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
              fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input v-model="search" type="text"
              placeholder="Cari aktivitas, user, IP, action..."
              class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400 transition"/>
          </div>
          <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <button v-for="pill in kategoriOptions" :key="pill.key"
              @click="setKategori(pill.key)"
              :class="['px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border',
                kategori === pill.key ? pill.active : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100 hover:text-slate-700']">
              {{ pill.label }}
            </button>
          </div>
        </div>

        <!-- Row 2: Date + User + Per Page -->
        <div class="flex flex-wrap gap-2 items-center border-t border-slate-100 pt-3">

          <!-- Quick date shortcuts -->
          <div class="flex items-center gap-1 flex-wrap">
            <button v-for="q in quickDates" :key="q.key"
              @click="setQuickDate(q)"
              :class="['px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all border',
                activeQuick === q.key
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100']">
              {{ q.label }}
            </button>
          </div>

          <!-- Divider -->
          <div class="h-5 w-px bg-slate-200 mx-1 hidden sm:block"/>

          <!-- Date range manual -->
          <div class="flex items-center gap-1.5">
            <input v-model="dateFrom" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
            <span class="text-slate-300 text-xs">–</span>
            <input v-model="dateTo" type="date"
              class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400"
              @change="applyFilters"/>
          </div>

          <!-- Divider -->
          <div class="h-5 w-px bg-slate-200 mx-1 hidden sm:block"/>

          <!-- User filter -->
          <select v-if="users.length > 1" v-model="userId" @change="applyFilters"
            class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400">
            <option value="">Semua User</option>
            <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
          </select>

          <!-- Per page -->
          <select v-model="perPage" @change="applyFilters"
            class="text-xs border border-slate-200 rounded-lg px-2 py-1 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-400">
            <option value="25">25 / hal</option>
            <option value="50">50 / hal</option>
            <option value="100">100 / hal</option>
            <option value="250">250 / hal</option>
          </select>

          <!-- Active filter count + clear -->
          <button v-if="hasActiveFilters" @click="clearFilters"
            class="ml-auto flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reset filter
          </button>
        </div>

        <!-- Active filter badges -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-1.5 border-t border-slate-100 pt-2.5">
          <span v-if="search" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            Cari: "{{ search }}"
          </span>
          <span v-if="kategori" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            Kategori: {{ kategoriOptions.find(k => k.key === kategori)?.label }}
          </span>
          <span v-if="dateFrom || dateTo" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            {{ dateFrom || '…' }} s/d {{ dateTo || '…' }}
          </span>
          <span v-if="userId" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
            User: {{ users.find(u => String(u.id) === userId)?.name }}
          </span>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
            {{ logs.total?.toLocaleString('id-ID') ?? 0 }} hasil
          </span>
        </div>
      </div>

      <!-- Feed -->
      <div v-if="logs.data.length > 0" class="space-y-7">
        <div v-for="[dateLabel, entries] in groupedLogs" :key="dateLabel">

          <!-- Date heading -->
          <div class="flex items-center gap-3">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ dateLabel }}</span>
            <div class="flex-1 h-px bg-slate-100"/>
            <span class="text-[11px] text-slate-300 tabular-nums">{{ entries.length }} entri</span>
          </div>

          <!-- Entries -->
          <div class="mt-3 space-y-2">
            <div v-for="log in entries" :key="log.id"
              class="bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow hover:border-slate-200 transition-all duration-150">
              <div class="flex gap-3 px-4 py-3">

                <!-- Time -->
                <div class="w-11 shrink-0 pt-0.5 text-center">
                  <span class="text-[11px] font-semibold text-slate-400 tabular-nums">{{ formatTime(log.created_at) }}</span>
                </div>

                <!-- Dot -->
                <div class="flex flex-col items-center pt-1.5 shrink-0">
                  <div :class="['w-2.5 h-2.5 rounded-full ring-2 ring-white shrink-0', dotClass(log.action)]"/>
                </div>

                <!-- Body -->
                <div class="flex-1 min-w-0">
                  <!-- Top row: user + badge -->
                  <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-1">
                    <div class="flex items-center gap-2 min-w-0">
                      <div class="w-6 h-6 rounded-full shrink-0 overflow-hidden bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                        <img v-if="log.user?.avatar_url" :src="log.user.avatar_url" :alt="log.user.name" class="w-full h-full object-cover"/>
                        <span v-else class="text-indigo-600 text-[10px] font-bold">{{ log.user ? log.user.name[0].toUpperCase() : '?' }}</span>
                      </div>
                      <span class="text-sm font-semibold text-slate-800 leading-tight truncate">
                        {{ log.user?.name ?? 'System' }}
                      </span>
                    </div>
                    <span :class="['inline-flex items-center rounded-lg px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset shrink-0', badgeClass(log.action)]">
                      {{ log.action_label }}
                    </span>
                  </div>

                  <!-- Description -->
                  <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ describe(log) }}</p>

                  <!-- Meta: IP + device + subject link -->
                  <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400">
                    <span v-if="log.ip_address" class="flex items-center gap-1">
                      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                      </svg>
                      <span class="font-mono">{{ log.ip_address }}</span>
                    </span>
                    <span v-if="log.user_agent" class="flex items-center gap-1 min-w-0">
                      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path v-if="isMobile(log.user_agent)" stroke-linecap="round" stroke-linejoin="round"
                          d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/>
                      </svg>
                      <span class="truncate max-w-xs" :title="log.user_agent">
                        {{ parseDevice(log.user_agent) }}
                      </span>
                    </span>
                    <Link v-if="log.subject_type === 'Invoice' && log.subject_id"
                      :href="route('invoices.show', log.subject_id) + '?back=' + encodeURIComponent($page.url)"
                      class="flex items-center gap-1 text-indigo-400 hover:text-indigo-600 transition-colors font-mono">
                      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                      </svg>
                      {{ log.subject_label }}
                    </Link>
                    <Link v-else-if="log.subject_type === 'Client' && log.subject_id"
                      :href="route('invoices.client', log.subject_id)"
                      class="flex items-center gap-1 text-indigo-400 hover:text-indigo-600 transition-colors">
                      <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                      </svg>
                      {{ log.subject_label }}
                    </Link>

                    <!-- Detail toggle -->
                    <button v-if="log.detail && Object.keys(log.detail).length"
                      @click="toggleDetail(log.id)"
                      class="flex items-center gap-1 text-slate-400 hover:text-indigo-500 transition-colors">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          :d="expandedLogs.has(log.id)
                            ? 'M4.5 15.75l7.5-7.5 7.5 7.5'
                            : 'M19.5 8.25l-7.5 7.5-7.5-7.5'"/>
                      </svg>
                      {{ expandedLogs.has(log.id) ? 'Tutup detail' : 'Lihat detail' }}
                    </button>
                  </div>

                  <!-- Expandable detail -->
                  <div v-if="expandedLogs.has(log.id) && log.detail" class="mt-2 bg-slate-50 rounded-lg border border-slate-100 p-3">
                    <div v-for="(val, key) in log.detail" :key="key"
                      class="flex gap-3 py-0.5 text-[11px]">
                      <span class="text-slate-400 font-medium w-36 shrink-0">{{ key }}</span>
                      <span v-if="Array.isArray(val)" class="text-slate-700 font-mono break-all">
                        {{ val.join(' · ') }}
                      </span>
                      <span v-else class="text-slate-700 font-mono break-all">{{ val }}</span>
                    </div>
                  </div>
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
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
          </svg>
        </div>
        <h3 class="mt-4 text-sm font-semibold text-slate-700">Tidak ada aktivitas ditemukan</h3>
        <p class="mt-1 text-xs text-slate-400">Coba ubah filter atau pilih rentang tanggal yang berbeda.</p>
        <button v-if="hasActiveFilters" @click="clearFilters"
          class="mt-4 px-4 py-1.5 text-xs rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium transition">
          Reset Semua Filter
        </button>
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
import { ref, computed, watch } from 'vue'

const props = defineProps({
  logs:    { type: Object, default: () => ({ data: [] }) },
  stats:   { type: Object, default: () => ({}) },
  users:   { type: Array,  default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

// ── Filter state ──────────────────────────────────────────────────────────
const search   = ref(props.filters.search    ?? '')
const kategori = ref(props.filters.kategori  ?? '')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo   = ref(props.filters.date_to   ?? '')
const userId   = ref(props.filters.user_id   ?? '')
const perPage  = ref(String(props.filters.per_page ?? '25'))
const activeQuick = ref('')

// ── Apply all filters ─────────────────────────────────────────────────────
function buildParams() {
  return {
    ...(search.value   ? { search:    search.value   } : {}),
    ...(kategori.value ? { kategori:  kategori.value } : {}),
    ...(dateFrom.value ? { date_from: dateFrom.value } : {}),
    ...(dateTo.value   ? { date_to:   dateTo.value   } : {}),
    ...(userId.value   ? { user_id:   userId.value   } : {}),
    ...(perPage.value !== '25' ? { per_page: perPage.value } : {}),
  }
}

function applyFilters() {
  router.get(route('logs.index'), buildParams(), { preserveState: true, replace: true })
}

// Search debounce
let debounceTimer = null
watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 420)
})

function setKategori(k) {
  kategori.value = k
  applyFilters()
}

const goToPage = (url) => { if (url) router.get(url, {}, { preserveState: false }) }

// ── Quick date shortcuts ──────────────────────────────────────────────────
const quickDates = [
  { key: 'today',   label: 'Hari Ini' },
  { key: 'yesterday', label: 'Kemarin' },
  { key: '7d',      label: '7 Hari' },
  { key: '30d',     label: '30 Hari' },
  { key: 'month',   label: 'Bulan Ini' },
]

function toISO(d) {
  return d.toISOString().substring(0, 10)
}

function setQuickDate(q) {
  if (activeQuick.value === q.key) {
    // Toggle off
    activeQuick.value = ''
    dateFrom.value = ''
    dateTo.value   = ''
    applyFilters()
    return
  }
  activeQuick.value = q.key
  const now = new Date()
  const today = toISO(now)
  if (q.key === 'today') {
    dateFrom.value = today
    dateTo.value   = today
  } else if (q.key === 'yesterday') {
    const y = new Date(now); y.setDate(y.getDate() - 1)
    const ys = toISO(y)
    dateFrom.value = ys
    dateTo.value   = ys
  } else if (q.key === '7d') {
    const d = new Date(now); d.setDate(d.getDate() - 6)
    dateFrom.value = toISO(d)
    dateTo.value   = today
  } else if (q.key === '30d') {
    const d = new Date(now); d.setDate(d.getDate() - 29)
    dateFrom.value = toISO(d)
    dateTo.value   = today
  } else if (q.key === 'month') {
    const d = new Date(now.getFullYear(), now.getMonth(), 1)
    dateFrom.value = toISO(d)
    dateTo.value   = today
  }
  applyFilters()
}

// Reset active quick if date inputs changed manually
watch([dateFrom, dateTo], () => {
  const now   = new Date()
  const today = toISO(now)
  if (dateFrom.value === today && dateTo.value === today && activeQuick.value !== 'today') {
    activeQuick.value = 'today'
  } else if (!dateFrom.value && !dateTo.value) {
    activeQuick.value = ''
  }
}, { immediate: false })

// ── Clear all filters ─────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  search.value || kategori.value || dateFrom.value || dateTo.value || userId.value || perPage.value !== '25'
)

function clearFilters() {
  search.value      = ''
  kategori.value    = ''
  dateFrom.value    = ''
  dateTo.value      = ''
  userId.value      = ''
  perPage.value     = '25'
  activeQuick.value = ''
  applyFilters()
}

// ── Expandable detail ─────────────────────────────────────────────────────
const expandedLogs = ref(new Set())
function toggleDetail(id) {
  const s = new Set(expandedLogs.value)
  s.has(id) ? s.delete(id) : s.add(id)
  expandedLogs.value = s
}

// ── Group by date ─────────────────────────────────────────────────────────
const groupedLogs = computed(() => {
  const groups = {}
  ;(props.logs.data ?? []).forEach(log => {
    const key = toDate(log.created_at).toLocaleDateString('id-ID', {
      weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    })
    ;(groups[key] ??= []).push(log)
  })
  return Object.entries(groups)
})

// ── Color helpers ─────────────────────────────────────────────────────────
const COLOR = (action = '') => {
  if (action.includes('deleted'))                                      return 'red'
  if (action.includes('created') || action.includes('recurring'))      return 'green'
  if (action.includes('updated') || action.includes('saved') || action.includes('meta')) return 'yellow'
  if (action.includes('email'))                                        return 'purple'
  if (action.includes('status'))                                       return 'amber'
  if (action.includes('marked'))                                       return 'orange'
  if (action.includes('downloaded') || action.includes('printed'))     return 'slate'
  if (action === 'invoice.reset_all')                                  return 'red'
  if (action === 'invoice.carried')                                    return 'orange'
  if (action === 'invoice.reactivated')                                return 'purple'
  if (action === 'user.login')                                         return 'blue'
  if (action === 'user.logout')                                        return 'slate'
  return 'indigo'
}

const BADGE = {
  red:    'bg-red-50    text-red-700    ring-red-600/20',
  amber:  'bg-amber-50  text-amber-700  ring-amber-600/20',
  orange: 'bg-orange-50 text-orange-700 ring-orange-600/20',
  yellow: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
  green:  'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
  purple: 'bg-purple-50 text-purple-700  ring-purple-600/20',
  blue:   'bg-blue-50   text-blue-700   ring-blue-600/20',
  slate:  'bg-slate-100 text-slate-600  ring-slate-600/20',
  indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
}
const DOT = {
  red: 'bg-red-400', amber: 'bg-amber-400', orange: 'bg-orange-400',
  yellow: 'bg-yellow-400', green: 'bg-emerald-400', purple: 'bg-purple-400',
  blue: 'bg-blue-400', slate: 'bg-slate-400', indigo: 'bg-indigo-400',
}

const badgeClass = (action) => BADGE[COLOR(action)] ?? BADGE.indigo
const dotClass   = (action) => DOT[COLOR(action)]   ?? DOT.indigo

// ── Description generator ─────────────────────────────────────────────────
const MONTH_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des']
const fmtMonth = (dateStr) => {
  if (!dateStr) return '?'
  const [y, m] = dateStr.split('-')
  return `${MONTH_ID[parseInt(m) - 1]} ${y}`
}

function describe(log) {
  const s = log.subject_label ?? ''
  const d = log.detail ?? {}

  switch (log.action) {
    case 'invoice.created':           return `Invoice ${s} dibuat${d.client ? ' untuk ' + d.client : ''}`
    case 'invoice.updated':           return `Invoice ${s} diperbarui`
    case 'invoice.deleted':           return `Invoice ${s} dihapus`
    case 'invoice.saved':             return `Perubahan item & total disimpan pada ${s}`
    case 'invoice.status_changed':    return `Status ${s} berubah dari ${d.from ?? '?'} → ${d.to ?? '?'}${d.note ? ' (' + d.note + ')' : ''}`
    case 'invoice.email_sent':        return `Invoice ${s} dikirim ke ${Array.isArray(d.to) ? d.to.join(', ') : d.to}`
    case 'invoice.marked':            return `Invoice ${s} masuk antrean kirim otomatis`
    case 'invoice.unmarked':          return `Antrean kirim dibatalkan dari ${s}`
    case 'invoice.recurring_created': return `Invoice perpanjangan ${s} dibuat dari ${d.parent ?? '?'}`
    case 'invoice.downloaded':        return `PDF ${s} diunduh`
    case 'invoice.printed':           return `${s} dibuka untuk print`
    case 'invoice.interval_changed':  return `Interval ${s} diubah ke ${d.interval ?? '?'} bulan`
    case 'invoice.meta_updated':      return `SPK / catatan ${s} diperbarui`
    case 'invoice.items_updated':     return `Item invoice ${s} diperbarui`
    case 'invoice.totals_updated':    return `Total & diskon ${s} diperbarui`
    case 'invoice.tax_updated':       return `Pajak ${s} diubah ke ${d.tax ?? '?'}%`
    case 'invoice.frozen':            return `Invoice ${s} dibekukan`
    case 'invoice.resumed':           return `Invoice ${s} dilanjutkan dari status frozen`
    case 'invoice.carried':
      return `${d.original_number ?? s} → ${d.renamed_to ?? '?'} · tunggakan lanjut ke ${d.child_number ?? '?'} · ${d.jumlah ?? ''} · ${d.periode_asal ?? ''} → ${d.periode_baru ?? ''}`
    case 'invoice.reactivated':
      return `${d.original_number ?? s} → ${d.renamed_to ?? '?'} · head: ${d.head_number ?? '?'} · ${d.total_dibuat ?? 0} invoice dibuat · total ${d.total_semua ?? ''} · ${d.periode_dari ?? ''} s/d ${d.periode_sampai ?? ''}`
    case 'invoice.reset_all':
      return `Semua invoice dihapus (${d.total_dihapus ?? 0} invoice) — data demo direset`
    case 'invoice.exported': {
      const from       = fmtMonth(d.from)
      const to         = fmtMonth(d.to)
      const statusText = d.statuses === 'all' ? 'semua status' : d.statuses
      return `Export ${d.count ?? 0} invoice · ${from} s/d ${to} · filter: ${statusText}`
    }
    case 'client.created':            return `Client ${s} ditambahkan`
    case 'client.updated':            return `Data client ${s} diperbarui`
    case 'client.deleted':            return `Client ${s} dihapus`
    case 'user.login':                return `Login berhasil`
    case 'user.logout':               return `Logout dari aplikasi`
    case 'user.created':              return `User ${s} ditambahkan`
    case 'user.updated':              return `Data user ${s} diperbarui`
    case 'user.deleted':              return `User ${s} dihapus`
    case 'profile.updated':           return 'Profil diperbarui'
    case 'profile.avatar_updated':    return 'Avatar profil diperbarui'
    case 'profile.password_changed':  return 'Password diubah'
    default:
      return s ? `${log.action_label}: ${s}` : log.action_label
  }
}

// ── Device parser ─────────────────────────────────────────────────────────
const isMobile = (ua = '') => {
  const l = ua.toLowerCase()
  return l.includes('mobile') || l.includes('android') || l.includes('iphone')
}

function parseDevice(ua = '') {
  if (!ua) return '—'
  if (ua.includes('Chrome') && !ua.includes('Edg'))  return 'Chrome'
  if (ua.includes('Firefox'))                         return 'Firefox'
  if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari'
  if (ua.includes('Edg'))                             return 'Edge'
  if (ua.length > 60) return ua.substring(0, 60) + '…'
  return ua
}

// Laravel mengirim timestamp tanpa timezone info — parse sebagai local time
const toDate = (dt) => dt ? new Date(String(dt).replace(' ', 'T')) : new Date(NaN)

const formatTime = (dt) =>
  toDate(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })

// ── Static config ─────────────────────────────────────────────────────────
const statCards = [
  {
    key: 'total', label: 'Total Log', ring: 'ring-slate-400',
    border: 'border-slate-200', iconBg: 'bg-slate-100', iconColor: 'text-slate-500', valueColor: 'text-slate-800',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  },
  {
    key: 'invoice', label: 'Invoice', ring: 'ring-indigo-400',
    border: 'border-indigo-100', iconBg: 'bg-indigo-50', iconColor: 'text-indigo-500', valueColor: 'text-indigo-700',
    icon: 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z',
  },
  {
    key: 'client', label: 'Client', ring: 'ring-emerald-400',
    border: 'border-emerald-100', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-500', valueColor: 'text-emerald-700',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  },
  {
    key: 'auth', label: 'Auth', ring: 'ring-blue-400',
    border: 'border-blue-100', iconBg: 'bg-blue-50', iconColor: 'text-blue-500', valueColor: 'text-blue-700',
    icon: 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
  },
  {
    key: 'master', label: 'Master Data', ring: 'ring-amber-400',
    border: 'border-amber-100', iconBg: 'bg-amber-50', iconColor: 'text-amber-500', valueColor: 'text-amber-700',
    icon: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
  },
]

const kategoriOptions = [
  { key: '',        label: 'Semua',       active: 'bg-slate-800   text-white border-slate-800'   },
  { key: 'invoice', label: 'Invoice',     active: 'bg-indigo-600  text-white border-indigo-600'  },
  { key: 'client',  label: 'Client',      active: 'bg-emerald-600 text-white border-emerald-600' },
  { key: 'auth',    label: 'Auth',        active: 'bg-blue-600    text-white border-blue-600'    },
  { key: 'master',  label: 'Master Data', active: 'bg-amber-500   text-white border-amber-500'   },
]
</script>
