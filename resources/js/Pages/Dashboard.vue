<template>
  <AppLayout title="Dashboard">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">{{ greeting }}, {{ $page.props.auth.user.name }} 👋</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ currentDate }}</p>
        </div>
        <Link :href="route('invoices.create')"
          class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Invoice Baru
        </Link>
      </div>

      <!-- ── Stat Cards ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Revenue -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 border-l-[3px] border-l-emerald-400">
          <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Revenue Bulan Ini</p>
            <span v-if="revenueTrend" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
              :class="revenueTrend.up ? 'text-emerald-600 bg-emerald-50' : 'text-red-500 bg-red-50'">
              {{ revenueTrend.up ? '↑' : '↓' }} {{ revenueTrend.pct }}%
            </span>
          </div>
          <p class="text-[22px] font-black text-gray-900 leading-none truncate tracking-tight">{{ fmtCurrencyShort(stats.revenue_this_month) }}</p>
          <p class="text-[11px] text-gray-400 mt-2.5">
            vs <span class="font-semibold text-gray-600">{{ fmtCurrencyShort(stats.revenue_last_month) }}</span> bulan lalu
          </p>
        </div>

        <!-- Outstanding -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 border-l-[3px] border-l-sky-400">
          <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Outstanding</p>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-sky-50 text-sky-500">Aktif</span>
          </div>
          <p class="text-[22px] font-black text-gray-900 leading-none truncate tracking-tight">{{ fmtCurrencyShort(stats.outstanding) }}</p>
          <p class="text-[11px] text-gray-400 mt-2.5">Invoice terkirim, belum lunas</p>
        </div>

        <!-- Overdue -->
        <div class="rounded-2xl border shadow-sm p-4 border-l-[3px] transition-colors"
          :class="stats.overdue_count > 0
            ? 'bg-red-50/50 border-gray-100 border-l-red-400'
            : 'bg-white border-gray-100 border-l-gray-200'">
          <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] font-bold uppercase tracking-wider"
              :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-400'">Overdue</p>
            <span v-if="stats.overdue_count > 0"
              class="flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-500">
              <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse shrink-0"/>
              Perlu aksi
            </span>
          </div>
          <p class="text-[22px] font-black leading-none tracking-tight"
            :class="stats.overdue_count > 0 ? 'text-red-600' : 'text-gray-900'">
            {{ stats.overdue_count }}
            <span class="text-sm font-medium text-gray-400">invoice</span>
          </p>
          <p class="text-[11px] mt-2.5 font-semibold"
            :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-400'">
            {{ fmtCurrencyShort(stats.overdue_amount) }}
          </p>
        </div>

        <!-- Clients -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 border-l-[3px] border-l-indigo-400">
          <div class="flex items-center justify-between mb-3">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Client Aktif</p>
          </div>
          <p class="text-[22px] font-black text-gray-900 leading-none tracking-tight">{{ stats.active_clients }}</p>
          <p class="text-[11px] text-gray-400 mt-2.5">
            <span class="font-semibold text-indigo-500">{{ stats.invoices_this_month }}</span> invoice bulan ini
          </p>
        </div>

      </div>

      <!-- ── Charts Row ── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Area Chart -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-start justify-between mb-1">
            <div>
              <p class="text-sm font-bold text-gray-800">Revenue Trend</p>
              <p class="text-xs text-gray-400 mt-0.5">6 bulan terakhir · Invoice Paid</p>
            </div>
            <div class="text-right">
              <p class="text-[10px] text-gray-400">Total 6 bulan</p>
              <p class="text-sm font-bold text-gray-800 tabular-nums">{{ fmtCurrencyShort(totalRevenue6Month) }}</p>
            </div>
          </div>
          <VueApexCharts v-if="isMounted" type="area" height="190" :options="areaOptions" :series="areaSeries"/>
          <div v-else class="h-[190px] bg-gray-50 rounded-xl animate-pulse"/>
        </div>

        <!-- Status Breakdown -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="mb-4">
            <p class="text-sm font-bold text-gray-800">Status Invoice</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ totalStatusCount }} total · semua periode</p>
          </div>

          <div v-if="totalStatusCount > 0">
            <!-- Stacked bar -->
            <div class="flex h-2 rounded-full overflow-hidden mb-5 gap-px">
              <div v-for="s in statusBreakdown" :key="s.key"
                :style="{ width: ((s.count / totalStatusCount) * 100) + '%', background: s.color }"
                :title="s.label + ': ' + s.count"/>
            </div>
            <!-- Legend list -->
            <div class="space-y-2">
              <div v-for="s in statusBreakdown" :key="s.key" class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: s.color }"/>
                <span class="text-xs text-gray-600 flex-1 truncate">{{ s.label }}</span>
                <span class="text-xs font-bold text-gray-800 tabular-nums">{{ s.count }}</span>
                <span class="text-[10px] text-gray-400 tabular-nums w-8 text-right">
                  {{ ((s.count / totalStatusCount) * 100).toFixed(0) }}%
                </span>
              </div>
            </div>
          </div>

          <div v-else class="flex items-center justify-center h-32 text-xs text-gray-300">
            Belum ada data
          </div>
        </div>

      </div>

      <!-- ── Bottom Row ── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Upcoming -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
            <div>
              <p class="text-sm font-bold text-gray-800">Jatuh Tempo</p>
              <p class="text-xs text-gray-400 mt-0.5">7 hari ke depan</p>
            </div>
            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
              :class="upcoming_invoices.length > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-400'">
              {{ upcoming_invoices.length }}
            </span>
          </div>

          <div v-if="upcoming_invoices.length === 0"
            class="flex flex-col items-center justify-center py-10 px-5 text-center">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
              <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-xs font-medium text-gray-500">Semua aman</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Tidak ada jatuh tempo minggu ini</p>
          </div>

          <div v-else class="divide-y divide-gray-50">
            <Link v-for="inv in upcoming_invoices" :key="inv.id"
              :href="route('invoices.client', inv.client_id) + '?highlight=' + inv.id"
              class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50/80 transition-colors">
              <div class="w-11 h-11 rounded-xl flex flex-col items-center justify-center shrink-0"
                :class="inv.days_until === 0
                  ? 'bg-red-500'
                  : inv.days_until <= 2 ? 'bg-amber-400'
                  : 'bg-sky-400'">
                <span v-if="inv.days_until === 0" class="text-xs font-black text-white leading-none">DUE</span>
                <template v-else>
                  <span class="text-base font-black text-white leading-none">{{ inv.days_until }}</span>
                  <span class="text-[8px] font-semibold text-white/80 uppercase tracking-wide">hari lagi</span>
                </template>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-700 truncate">{{ inv.client_name }}</p>
                <p class="text-[10px] text-gray-400 font-mono truncate mt-0.5">{{ inv.invoice_number }}</p>
                <p class="text-[10px] mt-0.5"
                  :class="inv.days_until === 0 ? 'text-red-400 font-semibold' : inv.days_until <= 2 ? 'text-amber-500 font-semibold' : 'text-gray-400'">
                  Due {{ fmtDate(inv.due_date) }}
                </p>
              </div>
              <p class="text-xs font-bold text-gray-700 shrink-0 tabular-nums">{{ fmtCurrencyShort(inv.total) }}</p>
            </Link>
          </div>
        </div>

        <!-- Overdue table -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 rounded-full shrink-0"
                :class="overdue_invoices.length > 0 ? 'bg-red-400 animate-pulse' : 'bg-gray-200'"/>
              <p class="text-sm font-bold text-gray-800">Invoice Overdue</p>
            </div>
            <Link :href="route('invoices.numbering')"
              class="text-[11px] text-indigo-500 hover:text-indigo-700 font-semibold transition-colors">
              Lihat semua →
            </Link>
          </div>

          <div v-if="overdue_invoices.length === 0"
            class="flex flex-col items-center justify-center py-12 px-5 text-center">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
              <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-xs font-medium text-gray-500">Bersih</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Tidak ada invoice overdue saat ini</p>
          </div>

          <div v-else class="divide-y divide-gray-50">
            <div v-for="inv in overdue_invoices" :key="inv.id"
              class="flex items-center gap-3 px-5 py-3 transition-colors group"
              :class="inv.days_overdue >= 60 ? 'hover:bg-red-50/50' : inv.days_overdue >= 30 ? 'hover:bg-orange-50/40' : 'hover:bg-gray-50/60'">

              <!-- Urgency badge -->
              <div class="shrink-0 w-11 h-11 rounded-xl flex flex-col items-center justify-center"
                :class="inv.days_overdue >= 60 ? 'bg-red-500' : inv.days_overdue >= 30 ? 'bg-orange-400' : 'bg-amber-400'">
                <span class="text-sm font-black text-white leading-none">{{ inv.days_overdue }}</span>
                <span class="text-[8px] font-semibold text-white/80 uppercase tracking-wide">hari</span>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-gray-800 truncate">{{ inv.client_name }}</p>
                <p class="text-[10px] text-gray-400 font-mono truncate mt-0.5">{{ inv.invoice_number }}</p>
              </div>

              <div class="text-right shrink-0">
                <p class="text-[13px] font-bold text-gray-800 tabular-nums">{{ fmtCurrencyShort(inv.total) }}</p>
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md mt-0.5 inline-block"
                  :class="inv.document_status === 'draft'
                    ? 'bg-gray-100 text-gray-500'
                    : inv.send_status !== 'unsent' ? 'bg-amber-50 text-amber-600'
                    : 'bg-violet-50 text-violet-500'">
                  {{ inv.document_status === 'draft' ? 'Draft' : inv.send_status !== 'unsent' ? 'Terkirim' : 'Antrean' }}
                </span>
              </div>

              <Link :href="route('invoices.client', inv.client_id) + '?highlight=' + inv.id"
                class="opacity-0 group-hover:opacity-100 transition-opacity shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  stats:               Object,
  status_distribution: Object,
  monthly_revenue:     Array,
  overdue_invoices:    Array,
  upcoming_invoices:   Array,
})

const isMounted = ref(false)
onMounted(() => { isMounted.value = true })

const hour        = new Date().getHours()
const greeting    = hour < 11 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 18 ? 'Selamat sore' : 'Selamat malam'
const currentDate = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

// Revenue trend
const revenueTrend = computed(() => {
  const last = props.stats.revenue_last_month
  const curr = props.stats.revenue_this_month
  if (!last) return null
  const pct = ((curr - last) / last) * 100
  return { pct: Math.abs(pct).toFixed(1), up: pct >= 0 }
})

// Area chart
const totalRevenue6Month = computed(() => props.monthly_revenue.reduce((s, m) => s + m.value, 0))

const areaSeries = computed(() => [{ name: 'Revenue', data: props.monthly_revenue.map(m => m.value) }])

const areaOptions = computed(() => ({
  chart: {
    type: 'area', toolbar: { show: false }, zoom: { enabled: false },
    fontFamily: 'inherit', animations: { enabled: true, easing: 'easeinout', speed: 600 },
  },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.18, opacityTo: 0.01, stops: [0, 100] },
  },
  colors: ['#6366f1'],
  xaxis: {
    categories: props.monthly_revenue.map(m => m.label),
    axisBorder: { show: false }, axisTicks: { show: false },
    labels: { style: { colors: '#9ca3af', fontSize: '11px', fontFamily: 'inherit' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#9ca3af', fontSize: '10px', fontFamily: 'inherit' },
      formatter: v => fmtCurrencyShort(v),
    },
  },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 4, padding: { left: 4, right: 4, top: 0, bottom: 0 } },
  dataLabels: { enabled: false },
  markers: { size: 0, hover: { size: 5 } },
  tooltip: {
    theme: 'light',
    y: { formatter: v => fmtCurrency(v) },
    style: { fontFamily: 'inherit', fontSize: '12px' },
  },
}))

// Status breakdown (replaces donut)
const STATUS_META = [
  { key: 'paid',     label: 'Lunas',        color: '#10b981' },
  { key: 'sent',     label: 'Terkirim',     color: '#3b82f6' },
  { key: 'antrean',  label: 'Antrean Kirim',color: '#a78bfa' },
  { key: 'draft',    label: 'Draft',        color: '#94a3b8' },
  { key: 'carried',  label: 'Carried',      color: '#f59e0b' },
  { key: 'frozen',   label: 'Frozen',       color: '#6366f1' },
  { key: 'inactive', label: 'Tidak Aktif',  color: '#e5e7eb' },
]

const statusBreakdown = computed(() =>
  STATUS_META
    .map(s => ({ ...s, count: props.status_distribution[s.key] ?? 0 }))
    .filter(s => s.count > 0)
    .sort((a, b) => b.count - a.count)
)

const totalStatusCount = computed(() => statusBreakdown.value.reduce((s, x) => s + x.count, 0))

// Formatters
const fmtCurrency = v => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
}).format(v || 0)

const fmtDate = d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })

function fmtCurrencyShort(v) {
  if (!v) return 'Rp 0'
  if (v >= 1_000_000_000) return 'Rp ' + (v / 1_000_000_000).toFixed(1).replace('.0', '') + 'M'
  if (v >= 1_000_000)     return 'Rp ' + (v / 1_000_000).toFixed(1).replace('.0', '') + 'jt'
  if (v >= 1_000)         return 'Rp ' + (v / 1_000).toFixed(0) + 'rb'
  return 'Rp ' + v
}
</script>
