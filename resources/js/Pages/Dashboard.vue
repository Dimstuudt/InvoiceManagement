<template>
  <AppLayout title="Dashboard">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Dashboard</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ greeting }}, {{ $page.props.auth.user.name }}</p>
        </div>
        <span class="text-sm font-medium text-gray-500 bg-white border border-gray-100 shadow-sm rounded-xl px-4 py-2">
          {{ currentMonth }}
        </span>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-400 font-medium">Revenue Bulan Ini</p>
          <p class="text-xl font-bold text-gray-900 mt-1 truncate">{{ fmtCurrency(stats.revenue_this_month) }}</p>
          <p class="text-xs text-emerald-500 mt-1 font-medium">● Paid</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-400 font-medium">Menunggu Bayar</p>
          <p class="text-xl font-bold text-gray-900 mt-1 truncate">{{ fmtCurrency(stats.outstanding) }}</p>
          <p class="text-xs text-blue-500 mt-1 font-medium">● Belum jatuh tempo</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4"
          :class="stats.overdue_count > 0 ? 'border-red-100 bg-red-50/30' : ''">
          <p class="text-xs font-medium" :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-400'">Jatuh Tempo</p>
          <div class="flex items-end gap-2 mt-1">
            <p class="text-xl font-bold" :class="stats.overdue_count > 0 ? 'text-red-600' : 'text-gray-900'">
              {{ stats.overdue_count }}
            </p>
            <p class="text-sm text-gray-400 mb-0.5">invoice</p>
          </div>
          <p class="text-xs mt-1 font-medium" :class="stats.overdue_count > 0 ? 'text-red-400' : 'text-gray-300'">
            {{ fmtCurrency(stats.overdue_amount) }}
          </p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
          <p class="text-xs text-gray-400 font-medium">Invoice Bulan Ini</p>
          <div class="flex items-end gap-2 mt-1">
            <p class="text-xl font-bold text-gray-900">{{ stats.invoices_this_month }}</p>
            <p class="text-sm text-gray-400 mb-0.5">dibuat</p>
          </div>
          <p class="text-xs text-indigo-400 mt-1 font-medium">{{ stats.active_clients }} client aktif</p>
        </div>

      </div>

      <!-- Revenue Chart + Upcoming -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Revenue Bar Chart -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-5">
            <div>
              <p class="text-sm font-semibold text-gray-800">Revenue 6 Bulan Terakhir</p>
              <p class="text-xs text-gray-400 mt-0.5">Invoice berstatus Paid</p>
            </div>
            <span class="text-xs font-mono font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">
              {{ fmtCurrencyShort(totalRevenue6Month) }}
            </span>
          </div>

          <!-- Bars -->
          <div class="flex items-end gap-2 h-36">
            <div v-for="m in monthly_revenue" :key="m.month + '-' + m.year"
              class="flex-1 flex flex-col items-center gap-1.5 min-w-0">
              <p class="text-[10px] font-mono text-gray-400 leading-none truncate w-full text-center">
                {{ m.value > 0 ? fmtCurrencyShort(m.value) : '' }}
              </p>
              <div class="w-full rounded-t-lg transition-all duration-500 relative"
                :style="{ height: barPx(m.value) + 'px' }"
                :class="m.current ? 'bg-indigo-500' : 'bg-indigo-100'">
              </div>
              <p class="text-[10px] font-medium leading-none"
                :class="m.current ? 'text-indigo-600' : 'text-gray-400'">
                {{ m.label }}
              </p>
            </div>
          </div>
        </div>

        <!-- Upcoming due -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-800">Jatuh Tempo 7 Hari</p>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
              :class="upcoming_invoices.length > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-400'">
              {{ upcoming_invoices.length }} invoice
            </span>
          </div>

          <div v-if="upcoming_invoices.length === 0"
            class="flex flex-col items-center justify-center h-28 text-center">
            <svg class="w-8 h-8 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-gray-300">Tidak ada yang jatuh tempo</p>
          </div>

          <div v-else class="space-y-2">
            <Link v-for="inv in upcoming_invoices" :key="inv.id"
              :href="route('invoices.show', inv.id)"
              class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors group">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-bold text-xs"
                :class="inv.days_until === 0 ? 'bg-red-100 text-red-600' : inv.days_until <= 2 ? 'bg-amber-100 text-amber-600' : 'bg-blue-50 text-blue-500'">
                {{ inv.days_until === 0 ? 'HR' : 'H-' + inv.days_until }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-700 truncate">{{ inv.client_name }}</p>
                <p class="text-[10px] text-gray-400 font-mono truncate">{{ inv.invoice_number }}</p>
              </div>
              <p class="text-xs font-semibold text-gray-700 shrink-0">{{ fmtCurrencyShort(inv.total) }}</p>
            </Link>
          </div>
        </div>

      </div>

      <!-- Overdue -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full" :class="overdue_invoices.length > 0 ? 'bg-red-400' : 'bg-gray-300'"/>
            <p class="text-sm font-semibold text-gray-800">Invoice Overdue</p>
          </div>
          <Link :href="route('invoices.index')"
            class="text-xs text-indigo-500 hover:text-indigo-700 font-medium transition-colors">
            Lihat semua →
          </Link>
        </div>

        <div v-if="overdue_invoices.length === 0"
          class="px-5 py-10 text-center text-sm text-gray-300">
          Tidak ada invoice overdue.
        </div>

        <div v-else>
          <div v-for="inv in overdue_invoices" :key="inv.id"
            class="flex items-center gap-4 px-5 py-3 border-t border-gray-50 hover:bg-red-50/30 transition-colors group">

            <!-- Days overdue badge -->
            <div class="shrink-0 w-14 text-center">
              <p class="text-sm font-black text-red-500">{{ inv.days_overdue }}</p>
              <p class="text-[10px] text-red-300 font-medium leading-none">hari</p>
            </div>

            <div class="w-px h-8 bg-gray-100 shrink-0"/>

            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-800 truncate">{{ inv.client_name }}</p>
              <p class="text-xs font-mono text-gray-400 mt-0.5 truncate">{{ inv.invoice_number }}</p>
            </div>

            <div class="text-right shrink-0">
              <p class="text-sm font-semibold text-gray-800">{{ fmtCurrency(inv.total) }}</p>
              <p class="text-[10px] mt-0.5">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold"
                  :class="inv.status === 'unpaid' ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-500'">
                  {{ inv.status === 'unpaid' ? 'Unpaid' : 'Sent' }}
                </span>
              </p>
            </div>

            <Link :href="route('invoices.show', inv.id)"
              class="opacity-0 group-hover:opacity-100 transition-opacity shrink-0 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg">
              Buka →
            </Link>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  stats:             Object,
  monthly_revenue:   Array,
  overdue_invoices:  Array,
  upcoming_invoices: Array,
})

// Greeting
const hour = new Date().getHours()
const greeting = hour < 12 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 18 ? 'Selamat sore' : 'Selamat malam'

const currentMonth = new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })

// Revenue chart
const maxRevenue = computed(() => Math.max(...props.monthly_revenue.map(m => m.value), 1))
const totalRevenue6Month = computed(() => props.monthly_revenue.reduce((s, m) => s + m.value, 0))

const BAR_MAX_PX = 112
const BAR_MIN_PX = 4

function barPx(value) {
  if (value <= 0) return BAR_MIN_PX
  return Math.max(BAR_MIN_PX, Math.round((value / maxRevenue.value) * BAR_MAX_PX))
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
