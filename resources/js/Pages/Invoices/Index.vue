<template>
  <AppLayout title="Invoice">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            <template v-if="search || statusFilter !== 'all'">
              {{ filteredClients.length }} dari {{ clients.length }} client
            </template>
            <template v-else>{{ clients.length }} client aktif</template>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="showExport = true"
            class="inline-flex items-center px-3 py-2 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export
          </button>
          <Link :href="route('invoices.create')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Invoice
          </Link>
        </div>
      </div>

      <!-- Summary stats -->
      <div class="grid grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3.5">
          <p class="text-xs text-gray-400 mb-1">Total Invoice</p>
          <p class="text-xl font-bold text-gray-900">{{ summary.total_invoices }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3.5">
          <p class="text-xs text-gray-400 mb-1">Belum Dibayar</p>
          <p class="text-xl font-bold text-red-500">{{ summary.total_unpaid }}</p>
        </div>
        <div class="rounded-xl border shadow-sm px-4 py-3.5"
          :class="summary.total_overdue > 0 ? 'bg-red-50 border-red-200' : 'bg-white border-gray-100'">
          <p class="text-xs mb-1" :class="summary.total_overdue > 0 ? 'text-red-400' : 'text-gray-400'">Jatuh Tempo</p>
          <p class="text-xl font-bold" :class="summary.total_overdue > 0 ? 'text-red-600' : 'text-gray-900'">
            {{ summary.total_overdue }}
          </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3.5">
          <p class="text-xs text-gray-400 mb-1">Outstanding</p>
          <p class="text-base font-bold text-gray-900 truncate">{{ formatCurrency(summary.total_outstanding) }}</p>
        </div>
      </div>

      <!-- Search + Filter -->
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Cari client, kota, PIC..."
            class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 shadow-sm"/>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
          <button v-for="f in filterOptions" :key="f.value" @click="statusFilter = f.value"
            class="px-3 py-2 text-xs font-medium rounded-xl transition-colors whitespace-nowrap"
            :class="statusFilter === f.value
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- Empty filtered -->
      <div v-if="filteredClients.length === 0 && clients.length > 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <svg class="w-8 h-8 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <p class="text-gray-400 text-sm">Tidak ada client yang sesuai filter.</p>
        <button @click="search = ''; statusFilter = 'all'"
          class="mt-2 text-xs text-indigo-500 hover:underline">
          Reset filter
        </button>
      </div>

      <!-- Empty base -->
      <div v-else-if="clients.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <p class="text-gray-400 text-sm">Belum ada client aktif.</p>
      </div>

      <!-- Client cards grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="client in filteredClients" :key="client.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden hover:shadow-md transition-shadow">

          <!-- Card header -->
          <div class="px-5 pt-5 pb-3 flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
              <span class="text-indigo-600 font-bold text-sm">{{ client.company_name.charAt(0) }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ client.company_name }}</h3>
                <span v-if="client.overdue_count"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-600 shrink-0">
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"/>
                  </svg>
                  {{ client.overdue_count }} overdue
                </span>
              </div>
              <p class="text-xs text-gray-400 mt-0.5 truncate">
                {{ client.city ?? '—' }}<span v-if="client.category"> &bull; {{ client.category.name }}</span>
              </p>
              <p v-if="client.pic" class="text-xs text-gray-500 mt-0.5 truncate">
                <span class="text-gray-400">PIC:</span> {{ client.pic }}
              </p>
            </div>
          </div>

          <!-- Status counts -->
          <div class="px-5 pb-3 flex flex-wrap gap-1.5">
            <span v-if="client.draft_count"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
              Draft <span class="font-bold">{{ client.draft_count }}</span>
            </span>
            <span v-if="client.sent_count"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-600 ring-1 ring-blue-200">
              Sent <span class="font-bold">{{ client.sent_count }}</span>
            </span>
            <span v-if="client.unpaid_count"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-600 ring-1 ring-red-200">
              Unpaid <span class="font-bold">{{ client.unpaid_count }}</span>
            </span>
            <span v-if="client.paid_count"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
              Paid <span class="font-bold">{{ client.paid_count }}</span>
            </span>
            <span v-if="!totalInvoices(client)" class="text-xs text-gray-300 italic">Belum ada invoice</span>
          </div>

          <!-- Nilai & meta -->
          <div class="px-5 pb-4 grid grid-cols-2 gap-x-4 gap-y-2">
            <div>
              <p class="text-xs text-gray-400">Outstanding</p>
              <p class="text-sm font-semibold" :class="client.unpaid_amount > 0 ? 'text-red-500' : 'text-gray-400'">
                {{ client.unpaid_amount > 0 ? formatCurrency(client.unpaid_amount) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Total Lifetime</p>
              <p class="text-sm font-semibold text-gray-700">
                {{ client.total_amount > 0 ? formatCurrency(client.total_amount) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Invoice Terakhir</p>
              <p class="text-sm font-medium text-gray-600">
                {{ client.invoices_max_issue_date ? fmtDate(client.invoices_max_issue_date) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400">Jenis Produk</p>
              <p class="text-sm font-medium text-gray-600">{{ client.product_type_count || '—' }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-auto border-t border-gray-50 px-5 py-3 flex items-center gap-2">
            <Link :href="route('invoices.create', { client_id: client.id })"
              class="flex-1 text-center text-xs px-3 py-2 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors font-medium">
              + Buat Invoice
            </Link>
            <Link :href="route('invoices.client', client.id)"
              class="flex-1 text-center text-xs px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-medium">
              Lihat Invoice →
            </Link>
          </div>

        </div>
      </div>

    </div>

    <!-- Export Modal -->
    <Teleport to="body">
      <div v-if="showExport" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showExport = false"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

          <!-- Modal header -->
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-base font-semibold text-gray-900">Export Invoice</h3>
              <p class="text-xs text-gray-400 mt-0.5">Download laporan ke format XLSX</p>
            </div>
            <button @click="showExport = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="space-y-4">

            <!-- Rentang bulan -->
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Rentang Bulan</p>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="text-xs text-gray-400 mb-1 block">Dari</label>
                  <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="month" v-model="exportFrom"
                      class="w-full pl-8 pr-2 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-colors"/>
                  </div>
                </div>
                <div>
                  <label class="text-xs text-gray-400 mb-1 block">Sampai</label>
                  <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="month" v-model="exportTo" :min="exportFrom"
                      class="w-full pl-8 pr-2 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-colors"/>
                  </div>
                </div>
              </div>
            </div>

            <!-- Filter status -->
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Status Invoice</p>
              <div class="flex flex-wrap gap-1.5">
                <button v-for="s in exportStatusOptions" :key="s.value"
                  @click="toggleExportStatus(s.value)"
                  class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors"
                  :class="exportStatuses.includes(s.value)
                    ? s.activeClass
                    : 'border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'">
                  {{ s.label }}
                </button>
              </div>
              <p class="text-[10px] text-gray-400 mt-1.5">
                {{ exportStatuses.length === 0 ? 'Semua status akan di-export' : exportStatuses.length + ' status dipilih' }}
              </p>
            </div>

          </div>

          <div class="flex gap-2 mt-6">
            <button @click="showExport = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="doExport"
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download XLSX
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

const props = defineProps({ clients: Array, summary: Object })

// Filter
const search = ref('')
const statusFilter = ref('all')

const filterOptions = [
  { value: 'all',     label: 'Semua' },
  { value: 'overdue', label: 'Ada Overdue' },
  { value: 'unpaid',  label: 'Ada Unpaid' },
  { value: 'sent',    label: 'Ada Sent' },
]

const filteredClients = computed(() => {
  let list = props.clients
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(c =>
      c.company_name.toLowerCase().includes(q) ||
      (c.city  || '').toLowerCase().includes(q) ||
      (c.pic   || '').toLowerCase().includes(q)
    )
  }
  if (statusFilter.value === 'overdue') list = list.filter(c => c.overdue_count > 0)
  if (statusFilter.value === 'unpaid')  list = list.filter(c => c.unpaid_count  > 0)
  if (statusFilter.value === 'sent')    list = list.filter(c => c.sent_count    > 0)
  return list
})

// Export
const showExport    = ref(false)
const now           = new Date()
const thisMonth     = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const exportFrom    = ref(thisMonth)
const exportTo      = ref(thisMonth)
const exportStatuses = ref([])

const exportStatusOptions = [
  { value: 'paid',    label: 'Lunas',        activeClass: 'border-emerald-400 bg-emerald-50 text-emerald-700' },
  { value: 'sent',    label: 'Terkirim',     activeClass: 'border-blue-400 bg-blue-50 text-blue-700' },
  { value: 'unpaid',  label: 'Belum Bayar',  activeClass: 'border-red-400 bg-red-50 text-red-700' },
  { value: 'draft',   label: 'Draft',        activeClass: 'border-gray-400 bg-gray-100 text-gray-700' },
  { value: 'frozen',  label: 'Dibekukan',    activeClass: 'border-slate-400 bg-slate-100 text-slate-700' },
  { value: 'carried', label: 'Carry',        activeClass: 'border-orange-400 bg-orange-50 text-orange-700' },
]

function toggleExportStatus(val) {
  const idx = exportStatuses.value.indexOf(val)
  if (idx === -1) exportStatuses.value.push(val)
  else exportStatuses.value.splice(idx, 1)
}

function doExport() {
  const params = new URLSearchParams({ from: exportFrom.value, to: exportTo.value })
  exportStatuses.value.forEach(s => params.append('status[]', s))
  window.location.href = `/invoices/export?${params.toString()}`
  showExport.value = false
}

// Helpers
const totalInvoices = c =>
  (c.draft_count ?? 0) + (c.sent_count ?? 0) + (c.paid_count ?? 0) + (c.unpaid_count ?? 0)

const formatCurrency = v =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)

const fmtDate = d =>
  new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
</script>
