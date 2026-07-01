<template>
  <AppLayout title="Dashboard">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">{{ greeting }}, {{ $page.props.auth.user.name }} 👋</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ currentDate }}</p>
        </div>
      </div>

      <!-- ── Stat Cards ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Revenue -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <div class="flex items-start justify-between">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
              <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span v-if="revenueTrend" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
              :class="revenueTrend.up ? 'text-emerald-600 bg-emerald-50' : 'text-red-500 bg-red-50'">
              {{ revenueTrend.up ? '▲' : '▼' }} {{ revenueTrend.pct }}%
            </span>
          </div>
          <p class="text-[11px] text-gray-400 font-medium mt-3">Revenue Bulan Ini</p>
          <p class="text-lg font-bold text-gray-900 mt-0.5 truncate">{{ fmtCurrencyShort(stats.revenue_this_month) }}</p>
          <p class="text-[10px] text-gray-300 mt-1">vs {{ fmtCurrencyShort(stats.revenue_last_month) }} bulan lalu</p>
          <p class="text-[9px] text-gray-300 mt-1.5 italic">💡 Dihitung dari invoice berstatus <strong>Paid</strong> bulan ini</p>
        </div>

        <!-- Outstanding -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <p class="text-[11px] text-gray-400 font-medium mt-3">Menunggu Bayar</p>
          <p class="text-lg font-bold text-gray-900 mt-0.5 truncate">{{ fmtCurrencyShort(stats.outstanding) }}</p>
          <p class="text-[10px] text-blue-400 mt-1">Belum jatuh tempo</p>
          <p class="text-[9px] text-gray-300 mt-1.5 italic">💡 Invoice <strong>Sent</strong> yang belum melewati due date</p>
        </div>

        <!-- Overdue -->
        <div class="rounded-2xl border shadow-sm p-4"
          :class="stats.overdue_count > 0 ? 'bg-red-50/60 border-red-100' : 'bg-white border-gray-100'">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center"
            :class="stats.overdue_count > 0 ? 'bg-red-100' : 'bg-gray-100'">
            <svg class="w-4.5 h-4.5" :class="stats.overdue_count > 0 ? 'text-red-500' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <p class="text-[11px] font-medium mt-3" :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-400'">Overdue</p>
          <p class="text-lg font-bold mt-0.5" :class="stats.overdue_count > 0 ? 'text-red-600' : 'text-gray-900'">
            {{ stats.overdue_count }} <span class="text-sm font-normal">invoice</span>
          </p>
          <p class="text-[10px] mt-1" :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-300'">
            {{ fmtCurrencyShort(stats.overdue_amount) }}
          </p>
          <p class="text-[9px] text-gray-300 mt-1.5 italic">💡 Invoice <strong>Sent/Unpaid</strong> yang sudah lewat due date</p>
        </div>

        <!-- Clients -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
            <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <p class="text-[11px] text-gray-400 font-medium mt-3">Client Aktif</p>
          <p class="text-lg font-bold text-gray-900 mt-0.5">{{ stats.active_clients }}</p>
          <p class="text-[10px] text-indigo-400 mt-1">{{ stats.invoices_this_month }} invoice bulan ini</p>
          <p class="text-[9px] text-gray-300 mt-1.5 italic">💡 Client dengan minimal 1 invoice tercatat</p>
        </div>

      </div>

      <!-- ── Charts Row ── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Area Chart: Revenue -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="text-sm font-semibold text-gray-800">Revenue Trend</p>
              <p class="text-xs text-gray-400 mt-0.5">6 bulan terakhir · Invoice Paid</p>
            </div>
            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg font-mono">
              {{ fmtCurrencyShort(totalRevenue6Month) }}
            </span>
          </div>
          <VueApexCharts
            v-if="isMounted"
            type="area"
            height="180"
            :options="areaOptions"
            :series="areaSeries"
          />
          <div v-else class="h-[180px] bg-gray-50 rounded-xl animate-pulse"/>
        </div>

        <!-- Donut Chart: Status -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="mb-2">
            <p class="text-sm font-semibold text-gray-800">Status Invoice</p>
            <p class="text-xs text-gray-400 mt-0.5">Distribusi semua invoice</p>
          </div>
          <VueApexCharts
            v-if="isMounted && totalStatusCount > 0"
            type="donut"
            height="210"
            :options="donutOptions"
            :series="donutSeries"
          />
          <div v-else-if="!isMounted" class="h-[210px] bg-gray-50 rounded-xl animate-pulse"/>
          <div v-else class="h-[210px] flex items-center justify-center text-sm text-gray-300">
            Belum ada data
          </div>
        </div>

      </div>

      <!-- ── Bottom Row ── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Upcoming due -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm font-semibold text-gray-800">Jatuh Tempo</p>
              <p class="text-xs text-gray-400 mt-0.5">7 hari ke depan</p>
            </div>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
              :class="upcoming_invoices.length > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-400'">
              {{ upcoming_invoices.length }}
            </span>
          </div>

          <div v-if="upcoming_invoices.length === 0" class="flex flex-col items-center justify-center h-24 text-center">
            <svg class="w-7 h-7 text-gray-200 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-gray-300">Aman, tidak ada jatuh tempo</p>
          </div>

          <div v-else class="space-y-1.5">
            <Link v-for="inv in upcoming_invoices" :key="inv.id"
              :href="route('invoices.show', inv.id)"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors group">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-[11px] font-black"
                :class="inv.days_until === 0 ? 'bg-red-100 text-red-600' : inv.days_until <= 2 ? 'bg-amber-100 text-amber-600' : 'bg-blue-50 text-blue-500'">
                {{ inv.days_until === 0 ? 'DUE' : 'H-' + inv.days_until }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[12px] font-semibold text-gray-700 truncate">{{ inv.client_name }}</p>
                <p class="text-[10px] text-gray-400 font-mono truncate">{{ inv.invoice_number }}</p>
              </div>
              <p class="text-[12px] font-semibold text-gray-600 shrink-0">{{ fmtCurrencyShort(inv.total) }}</p>
            </Link>
          </div>
        </div>

        <!-- Overdue table -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 rounded-full" :class="overdue_invoices.length > 0 ? 'bg-red-400 animate-pulse' : 'bg-gray-300'"/>
              <p class="text-sm font-semibold text-gray-800">Invoice Overdue</p>
            </div>
            <Link :href="route('invoices.index')" class="text-[11px] text-blue-500 hover:text-blue-700 font-medium transition-colors">
              Lihat semua →
            </Link>
          </div>

          <div v-if="overdue_invoices.length === 0" class="px-5 py-12 text-center">
            <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-300">Tidak ada invoice overdue</p>
          </div>

          <div v-else class="divide-y divide-gray-50">
            <div v-for="inv in overdue_invoices" :key="inv.id"
              class="flex items-center gap-4 px-5 py-3 hover:bg-red-50/20 transition-colors group">
              <div class="shrink-0 text-center w-12">
                <p class="text-base font-black text-red-500 leading-none">{{ inv.days_overdue }}</p>
                <p class="text-[9px] text-red-300 font-medium mt-0.5 uppercase tracking-wide">hari</p>
              </div>
              <div class="w-px h-7 bg-gray-100 shrink-0"/>
              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-gray-800 truncate">{{ inv.client_name }}</p>
                <p class="text-[10px] text-gray-400 font-mono truncate mt-0.5">{{ inv.invoice_number }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-[13px] font-semibold text-gray-800">{{ fmtCurrencyShort(inv.total) }}</p>
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded mt-0.5 inline-block"
                  :class="inv.status === 'unpaid' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600'">
                  {{ inv.status === 'unpaid' ? 'Unpaid' : 'Sent' }}
                </span>
              </div>
              <Link :href="route('invoices.show', inv.id)"
                class="opacity-0 group-hover:opacity-100 transition-opacity shrink-0 px-2.5 py-1.5 text-[11px] font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg">
                Buka →
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
  stats:                Object,
  status_distribution:  Object,
  monthly_revenue:      Array,
  overdue_invoices:     Array,
  upcoming_invoices:    Array,
})

const isMounted = ref(false)
onMounted(() => { isMounted.value = true })

// Greeting
const hour = new Date().getHours()
const greeting = hour < 11 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 18 ? 'Selamat sore' : 'Selamat malam'
const currentDate = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })

// Revenue trend vs last month
const revenueTrend = computed(() => {
  const last = props.stats.revenue_last_month
  const curr = props.stats.revenue_this_month
  if (!last || last === 0) return null
  const pct = ((curr - last) / last) * 100
  return { pct: Math.abs(pct).toFixed(1), up: pct >= 0 }
})

// Area chart
const totalRevenue6Month = computed(() => props.monthly_revenue.reduce((s, m) => s + m.value, 0))

const areaSeries = computed(() => [{
  name: 'Revenue',
  data: props.monthly_revenue.map(m => m.value),
}])

const areaOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'inherit',
    sparkline: { enabled: false },
    animations: { enabled: true, easing: 'easeinout', speed: 600 },
  },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.2,
      opacityTo: 0.01,
      stops: [0, 100],
    },
  },
  colors: ['#3b82f6'],
  xaxis: {
    categories: props.monthly_revenue.map(m => m.label),
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#9ca3af', fontSize: '11px', fontFamily: 'inherit' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#9ca3af', fontSize: '10px', fontFamily: 'inherit' },
      formatter: v => fmtCurrencyShort(v),
    },
  },
  grid: {
    borderColor: '#f3f4f6',
    strokeDashArray: 4,
    padding: { left: 4, right: 4, top: 0, bottom: 0 },
  },
  dataLabels: { enabled: false },
  markers: { size: 0, hover: { size: 5 } },
  tooltip: {
    theme: 'light',
    y: { formatter: v => fmtCurrency(v) },
    style: { fontFamily: 'inherit', fontSize: '12px' },
  },
}))

// Donut chart
const donutSeries = computed(() => [
  props.status_distribution.paid,
  props.status_distribution.sent,
  props.status_distribution.unpaid,
  props.status_distribution.draft,
  props.status_distribution.carried,
  props.status_distribution.frozen,
])

const totalStatusCount = computed(() => donutSeries.value.reduce((a, b) => a + b, 0))

const donutOptions = {
  chart: {
    type: 'donut',
    fontFamily: 'inherit',
    animations: { enabled: true, speed: 600 },
  },
  labels: ['Paid', 'Sent', 'Unpaid', 'Draft', 'Carried', 'Frozen'],
  colors: ['#10b981', '#3b82f6', '#ef4444', '#94a3b8', '#f59e0b', '#6366f1'],
  legend: {
    position: 'bottom',
    fontSize: '11px',
    fontFamily: 'inherit',
    markers: { width: 8, height: 8, radius: 4, offsetX: -2 },
    itemMargin: { horizontal: 6, vertical: 3 },
    labels: { colors: '#6b7280' },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '11px',
            fontFamily: 'inherit',
            color: '#9ca3af',
            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
          },
          value: {
            fontSize: '18px',
            fontWeight: 700,
            fontFamily: 'inherit',
            color: '#111827',
            offsetY: 4,
          },
        },
      },
    },
  },
  stroke: { show: false },
  dataLabels: { enabled: false },
  tooltip: {
    theme: 'light',
    style: { fontFamily: 'inherit', fontSize: '12px' },
  },
}

// Formatters
const fmtCurrency = v => new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
}).format(v || 0)

function fmtCurrencyShort(v) {
  if (!v) return 'Rp 0'
  if (v >= 1_000_000_000) return 'Rp ' + (v / 1_000_000_000).toFixed(1).replace('.0', '') + 'M'
  if (v >= 1_000_000)     return 'Rp ' + (v / 1_000_000).toFixed(1).replace('.0', '') + 'jt'
  if (v >= 1_000)         return 'Rp ' + (v / 1_000).toFixed(0) + 'rb'
  return 'Rp ' + v
}
</script>
