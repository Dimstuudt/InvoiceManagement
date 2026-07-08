<template>
  <AppLayout title="Semua Invoice">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Semua Invoice</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            Invoice bulan <span class="font-bold text-gray-800">{{ nextMonthLabel }}</span>
          </p>
        </div>
        <Link :href="route('invoices.create')"
          class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Buat Invoice
        </Link>
      </div>

      <!-- Filter bulan invoice -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 flex flex-wrap items-center gap-3">
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Tampilkan bulan</span>
        <select v-model="displayMonth" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="(name, i) in MONTHS" :key="i" :value="i + 1">{{ name }}</option>
        </select>
        <select v-model="displayYear" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <div class="relative ml-auto">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Cari nomor invoice..."
            class="pl-8 pr-8 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition w-56"/>
          <button v-if="search" @click="search = ''"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- PRIORITY SECTION -->
      <div>
        <div class="flex items-center gap-3 mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse inline-block"/>
            <span class="text-sm text-gray-600">
              Invoice bulan <span class="font-bold text-indigo-700">{{ nextMonthLabel }}</span>
            </span>
          </div>
          <span class="text-xs text-gray-400">{{ filteredPriority.length }} invoice</span>
        </div>

        <div v-if="filteredPriority.length === 0"
          class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-8 text-center">
          <p class="text-sm text-indigo-300">{{ search ? `Tidak ditemukan "${search}"` : 'Tidak ada invoice untuk bulan ' + nextMonthLabel + '.' }}</p>
        </div>

        <div v-else class="bg-white rounded-2xl border border-indigo-100 shadow-sm overflow-hidden">
          <table class="w-full">
            <thead>
              <tr class="border-b border-indigo-50 bg-indigo-50/40">
                <th class="w-10 px-3 py-3"/>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Client / Invoice</th>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Periode</th>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Status Kirim</th>
                <th class="text-right text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Total</th>
                <th class="w-28 px-4 py-3"/>
              </tr>
            </thead>
            <tbody class="divide-y divide-indigo-50/50">
              <tr v-for="invoice in filteredPriority" :key="invoice.id"
                :class="invoice.document_status === 'verified' ? 'bg-amber-50/40' : isPastDue(invoice) ? 'bg-red-50/50 hover:bg-red-50/80' : 'hover:bg-indigo-50/20'"
                class="transition-colors group">
                <!-- Checkbox -->
                <td class="px-3 py-3.5 align-top pt-4">
                  <input type="checkbox" :checked="invoice.document_status === 'verified'"
                    @change="toggleMark(invoice)"
                    class="w-4 h-4 rounded border-gray-300 text-amber-500 cursor-pointer focus:ring-amber-400"/>
                </td>
                <!-- Client / Invoice -->
                <td class="px-4 py-3.5">
                  <p class="text-sm font-semibold text-gray-800 leading-tight">{{ invoice.client?.company_name ?? '-' }}</p>
                  <button @click="invoice.client?.id ? router.visit(route('invoices.client', invoice.client.id) + '?highlight=' + invoice.id) : null"
                    class="font-mono text-xs text-indigo-500 hover:text-indigo-700 hover:underline mt-0.5 text-left leading-tight">
                    {{ invoice.invoice_number }}
                  </button>
                  <div class="flex items-center gap-1 flex-wrap mt-1">
                    <span class="text-[10px] text-gray-400">{{ invoice.project_category?.name ?? '-' }}</span>
                    <span v-if="invoice.interval_months"
                      class="inline-flex items-center gap-0.5 px-1 py-0.5 bg-indigo-50 text-indigo-400 text-[10px] font-medium rounded">
                      ↻ {{ invoice.invoice_type === 'yearly' ? (invoice.interval_months / 12) + 'thn' : invoice.interval_months + 'bln' }}
                    </span>
                    <span v-if="invoice.is_chain_head"
                      class="inline-flex items-center px-1 py-0.5 text-[10px] font-medium rounded"
                      :class="chainBadgeClass(invoice.chain_type)">
                      {{ invoice.chain_type }}
                    </span>
                  </div>
                </td>
                <!-- Periode -->
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <p class="text-xs text-gray-600">{{ fmtDate(invoice.chain_issue_date ?? invoice.issue_date) }}</p>
                  <p class="text-xs mt-0.5" :class="isPastDue(invoice) ? 'text-red-500 font-semibold' : 'text-gray-400'">
                    → {{ fmtDate(invoice.chain_due_date ?? invoice.due_date) }}
                  </p>
                  <span v-if="isPastDue(invoice)"
                    class="mt-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-semibold rounded">
                    ⚠ {{ daysPastDue(invoice) }}h
                  </span>
                </td>
                <!-- Status Kirim -->
                <td class="px-4 py-3.5">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium shrink-0" :class="statusClass(computedStatus(invoice))">
                      {{ statusLabel(computedStatus(invoice)) }}
                    </span>
                    <template v-if="invoice.send_status !== 'unsent' || (invoice.document_status === 'verified' && invoice.payment_status === 'unpaid')">
                      <div class="flex items-center gap-0.5">
                        <span v-for="st in ['send1','send2','send3','send4','send5']" :key="st"
                          class="w-1.5 h-1.5 rounded-full"
                          :class="stageReached(invoice.send_status, st)
                            ? (invoice.payment_status === 'paid' ? 'bg-emerald-300' : st === 'send5' ? 'bg-red-400' : 'bg-indigo-400')
                            : 'bg-gray-200'"/>
                      </div>
                    </template>
                  </div>
                  <template v-if="invoice.send_status !== 'unsent' || (invoice.document_status === 'verified' && invoice.payment_status === 'unpaid')">
                    <p v-if="nextSendInfo(invoice)" class="text-[10px] mt-1"
                      :class="nextSendInfo(invoice).soon ? 'text-amber-500 font-semibold' : 'text-gray-400'">
                      {{ nextSendInfo(invoice).stage }}: {{ nextSendInfo(invoice).soon ? 'secepatnya' : fmtDateShort(nextSendInfo(invoice).date) }}
                    </p>
                    <p v-else-if="invoice.send_status === 'send5' && invoice.payment_status === 'unpaid'" class="text-[10px] mt-1 text-red-400">5× kirim, belum lunas</p>
                    <p v-else-if="invoice.payment_status === 'paid' && invoice.send_status !== 'unsent'" class="text-[10px] mt-1 text-emerald-500">lunas · tidak dilanjutkan</p>
                  </template>
                </td>
                <!-- Total -->
                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                  <p class="text-sm font-semibold text-gray-800">{{ fmtCurrency(invoice.is_chain_head ? invoice.chain_total : invoice.items_sum_amount) }}</p>
                  <span v-if="invoice.is_chain_head"
                    class="inline-flex items-center mt-0.5 px-1.5 py-0.5 text-[10px] font-semibold rounded"
                    :class="chainBadgeClass(invoice.chain_type)">
                    total {{ invoice.chain_type }}
                  </span>
                  <span v-else-if="invoice.tax_percentage"
                    class="inline-flex items-center mt-0.5 px-1.5 py-0.5 bg-violet-50 text-violet-500 text-[10px] font-semibold rounded">
                    PPN {{ invoice.tax_percentage }}%
                  </span>
                </td>
                <!-- Actions -->
                <td class="px-4 py-3.5">
                  <div class="flex items-center gap-1.5 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                    <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                      class="text-[11px] px-2.5 py-1.5 border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 rounded-lg transition-colors font-medium">
                      Lihat
                    </Link>
                    <button v-if="invoice.payment_status !== 'paid' && invoice.document_status === 'verified'"
                      @click="changeStatus(invoice, 'paid')"
                      class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors font-semibold">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                      Paid
                    </button>
                    <button @click="deleteInvoice(invoice)"
                      class="p-1.5 text-gray-300 hover:text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- DIVIDER -->
      <div class="flex items-center gap-3 pt-2">
        <div class="flex-1 h-px bg-gray-200"/>
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Invoice Bulan Lain</span>
        <div class="flex-1 h-px bg-gray-200"/>
      </div>

      <!-- OTHER SECTION -->
      <div v-if="filteredOther.length === 0" class="text-center py-6">
        <p class="text-sm text-gray-300">{{ search ? `Tidak ditemukan "${search}"` : 'Tidak ada invoice lainnya.' }}</p>
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="w-10 px-3 py-3"/>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Client / Invoice</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Periode</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status Kirim</th>
              <th class="text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
              <th class="w-28 px-4 py-3"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="invoice in filteredOther" :key="invoice.id"
              :class="invoice.document_status === 'verified' ? 'bg-amber-50/40' : isPastDue(invoice) ? 'bg-red-50/50 hover:bg-red-50/80' : 'hover:bg-gray-50/50'"
              class="transition-colors group">
              <!-- Checkbox -->
              <td class="px-3 py-3.5 align-top pt-4">
                <input type="checkbox" :checked="invoice.document_status === 'verified'"
                  @change="toggleMark(invoice)"
                  class="w-4 h-4 rounded border-gray-300 text-amber-500 cursor-pointer focus:ring-amber-400"/>
              </td>
              <!-- Client / Invoice -->
              <td class="px-4 py-3.5">
                <p class="text-sm font-semibold text-gray-800 leading-tight">{{ invoice.client?.company_name ?? '-' }}</p>
                <button @click="invoice.client?.id ? router.visit(route('invoices.client', invoice.client.id) + '?highlight=' + invoice.id) : null"
                  class="font-mono text-xs text-indigo-500 hover:text-indigo-700 hover:underline mt-0.5 text-left leading-tight">
                  {{ invoice.invoice_number }}
                </button>
                <div class="flex items-center gap-1 flex-wrap mt-1">
                  <span class="text-[10px] text-gray-400">{{ invoice.project_category?.name ?? '-' }}</span>
                  <span v-if="invoice.interval_months"
                    class="inline-flex items-center gap-0.5 px-1 py-0.5 bg-indigo-50 text-indigo-400 text-[10px] font-medium rounded">
                    ↻ {{ invoice.invoice_type === 'yearly' ? (invoice.interval_months / 12) + 'thn' : invoice.interval_months + 'bln' }}
                  </span>
                  <span v-if="invoice.is_chain_head"
                    class="inline-flex items-center px-1 py-0.5 text-[10px] font-medium rounded"
                    :class="chainBadgeClass(invoice.chain_type)">
                    {{ invoice.chain_type }}
                  </span>
                </div>
              </td>
              <!-- Periode -->
              <td class="px-4 py-3.5 whitespace-nowrap">
                <p class="text-xs text-gray-600">{{ fmtDate(invoice.chain_issue_date ?? invoice.issue_date) }}</p>
                <p class="text-xs mt-0.5" :class="isPastDue(invoice) ? 'text-red-500 font-semibold' : 'text-gray-400'">
                  → {{ fmtDate(invoice.chain_due_date ?? invoice.due_date) }}
                </p>
                <span v-if="isPastDue(invoice)"
                  class="mt-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-semibold rounded">
                  ⚠ {{ daysPastDue(invoice) }}h
                </span>
              </td>
              <!-- Status Kirim -->
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium shrink-0" :class="statusClass(computedStatus(invoice))">
                    {{ statusLabel(computedStatus(invoice)) }}
                  </span>
                  <template v-if="invoice.send_status !== 'unsent' || (invoice.document_status === 'verified' && invoice.payment_status === 'unpaid')">
                    <div class="flex items-center gap-0.5">
                      <span v-for="st in ['send1','send2','send3','send4','send5']" :key="st"
                        class="w-1.5 h-1.5 rounded-full"
                        :class="stageReached(invoice.send_status, st)
                          ? (invoice.payment_status === 'paid' ? 'bg-emerald-300' : st === 'send5' ? 'bg-red-400' : 'bg-indigo-400')
                          : 'bg-gray-200'"/>
                    </div>
                  </template>
                </div>
                <template v-if="invoice.send_status !== 'unsent' || (invoice.document_status === 'verified' && invoice.payment_status === 'unpaid')">
                  <p v-if="nextSendInfo(invoice)" class="text-[10px] mt-1"
                    :class="nextSendInfo(invoice).soon ? 'text-amber-500 font-semibold' : 'text-gray-400'">
                    {{ nextSendInfo(invoice).stage }}: {{ nextSendInfo(invoice).soon ? 'secepatnya' : fmtDateShort(nextSendInfo(invoice).date) }}
                  </p>
                  <p v-else-if="invoice.send_status === 'send5' && invoice.payment_status === 'unpaid'" class="text-[10px] mt-1 text-red-400">5× kirim, belum lunas</p>
                  <p v-else-if="invoice.payment_status === 'paid' && invoice.send_status !== 'unsent'" class="text-[10px] mt-1 text-emerald-500">lunas · tidak dilanjutkan</p>
                </template>
              </td>
              <!-- Total -->
              <td class="px-4 py-3.5 text-right whitespace-nowrap">
                <p class="text-sm font-semibold text-gray-800">{{ fmtCurrency(invoice.is_chain_head ? invoice.chain_total : invoice.items_sum_amount) }}</p>
                <span v-if="invoice.is_chain_head"
                  class="inline-flex items-center mt-0.5 px-1.5 py-0.5 text-[10px] font-semibold rounded"
                  :class="chainBadgeClass(invoice.chain_type)">
                  total {{ invoice.chain_type }}
                </span>
                <span v-else-if="invoice.tax_percentage"
                  class="inline-flex items-center mt-0.5 px-1.5 py-0.5 bg-violet-50 text-violet-500 text-[10px] font-semibold rounded">
                  PPN {{ invoice.tax_percentage }}%
                </span>
              </td>
              <!-- Actions -->
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-1.5 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                    class="text-[11px] px-2.5 py-1.5 border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 rounded-lg transition-colors font-medium">
                    Lihat
                  </Link>
                  <button v-if="invoice.payment_status !== 'paid' && invoice.document_status === 'verified'"
                    @click="changeStatus(invoice, 'paid')"
                    class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 rounded-lg transition-colors font-semibold">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Paid
                  </button>
                  <button @click="deleteInvoice(invoice)"
                    class="p-1.5 text-gray-300 hover:text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  priorityInvoices: Array,
  otherInvoices:    Array,
  nextMonthLabel:   String,
  filters:          Object,
});

const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 6 }, (_, i) => currentYear - 2 + i);

const search = ref('');

// displayMonth/displayYear = bulan TARGET yang user lihat (referensi + 1)
// Backend menerima bulan referensi (1 bulan sebelumnya)
const initDisplay = props.filters.month === 12
  ? { month: 1, year: props.filters.year + 1 }
  : { month: props.filters.month + 1, year: props.filters.year }
const displayMonth = ref(initDisplay.month)
const displayYear  = ref(initDisplay.year)

const matchSearch = (inv) => !search.value ||
  inv.invoice_number.toLowerCase().includes(search.value.toLowerCase())

const filteredPriority = computed(() => props.priorityInvoices.filter(matchSearch))
const filteredOther    = computed(() => props.otherInvoices.filter(matchSearch))

function applyFilters() {
  // Konversi bulan tampilan (target) ke bulan referensi untuk backend
  const refMonth = displayMonth.value === 1 ? 12 : displayMonth.value - 1
  const refYear  = displayMonth.value === 1 ? displayYear.value - 1 : displayYear.value
  router.get(route('invoices.schedule'), {
    month: refMonth,
    year:  refYear,
  }, { preserveState: true, replace: true });
}

function computedStatus(inv) {
  if (!inv) return 'draft'
  if (inv.document_status === 'frozen')  return 'frozen'
  if (inv.document_status === 'carried') return 'carried'
  if (inv.payment_status  === 'paid')    return 'paid'
  if (inv.document_status === 'draft')   return 'draft'
  return inv.send_status !== 'unsent' ? 'sent' : 'verified'
}

const statusClass = (s) => ({
  draft:    'bg-gray-100 text-gray-500',
  sent:     'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  verified: 'bg-amber-50 text-amber-600 ring-1 ring-amber-200',
  paid:     'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  frozen:   'bg-sky-100 text-sky-600 ring-1 ring-sky-200',
  carried:  'bg-orange-50 text-orange-600 ring-1 ring-orange-200',
}[s] ?? 'bg-gray-100 text-gray-500')

const statusLabel = (s) => ({
  draft:    'Draft',
  sent:     'Sent',
  verified: 'Terverifikasi',
  paid:     'Paid',
  frozen:   'Frozen',
  carried:  'Carried',
}[s] ?? s)

const isPastDue = (inv) => inv.payment_status !== 'paid' && inv.document_status !== 'frozen' && inv.document_status !== 'carried' && new Date(inv.chain_due_date ?? inv.due_date) < new Date(new Date().toDateString())

const daysPastDue = (inv) => Math.floor((new Date(new Date().toDateString()) - new Date(inv.chain_due_date ?? inv.due_date)) / 86400000);

const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtDateShort = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: '2-digit' })
  : '-';

const SEND_ORDER  = { unsent: 0, send1: 1, send2: 2, send3: 3, send4: 4, send5: 5 }
const SEND_NEXT   = { unsent: 'send1', send1: 'send2', send2: 'send3', send3: 'send4', send4: 'send5' }
const SEND_OFFSET = { send1: 0, send2: 14, send3: 21, send4: 28, send5: 35 }
const stageReached  = (current, target) => (SEND_ORDER[current] ?? 0) >= (SEND_ORDER[target] ?? 99)
const chainBadgeClass = (type) => ({
  carry:      'bg-orange-50 text-orange-600',
  prepay:     'bg-teal-50 text-teal-600',
  reaktivasi: 'bg-violet-50 text-violet-600',
}[type] ?? 'bg-gray-100 text-gray-500')
const nextSendInfo  = (inv) => {
  if (inv.document_status !== 'verified' || inv.payment_status !== 'unpaid') return null
  const nextStage = SEND_NEXT[inv.send_status]
  if (!nextStage) return null
  const d = new Date(inv.issue_date)
  d.setDate(d.getDate() + SEND_OFFSET[nextStage])
  return { stage: nextStage, date: d, soon: d <= new Date(new Date().toDateString()) }
}

const fmtCurrency = (v) => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

function toggleMark(invoice) {
  router.patch(route('invoices.mark', invoice.id), {}, { preserveScroll: true });
}

function changeStatus(invoice, status) {
  const payload = status === 'paid' ? { payment_status: 'paid' } : { document_status: status }
  router.patch(route('invoices.status', invoice.id), payload, { preserveScroll: true })
}

const allInvoices = computed(() => [...(props.priorityInvoices ?? []), ...(props.otherInvoices ?? [])])
const hasChild = (invoice) => allInvoices.value.some(inv => inv.parent_invoice_id === invoice.id)

function deleteInvoice(invoice) {
  if (hasChild(invoice)) {
    alert(`Tidak bisa menghapus ${invoice.invoice_number} — ada invoice lanjutan di atasnya.\nHapus dari HEAD rantai terlebih dahulu.`)
    return
  }
  if (!confirm(`Hapus invoice ${invoice.invoice_number}?`)) return;
  router.delete(route('invoices.destroy', invoice.id), { preserveScroll: true });
}
</script>
