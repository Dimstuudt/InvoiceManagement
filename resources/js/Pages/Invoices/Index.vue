<template>
  <AppLayout title="Invoice">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            {{ invoices.length }} invoice &bull; issue bulan <span class="font-medium text-gray-600">{{ MONTHS[filters.month - 1] }} {{ filters.year }}</span>
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

      <!-- Filter bar -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 flex flex-wrap items-center gap-3">

        <!-- Bulan -->
        <select v-model="localMonth" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="(name, i) in MONTHS" :key="i" :value="i + 1">{{ name }}</option>
        </select>

        <!-- Tahun -->
        <select v-model="localYear" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>

        <div class="w-px h-5 bg-gray-200"/>

        <!-- Status -->
        <select v-model="localStatus" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="paid">Paid</option>
          <option value="unpaid">Unpaid</option>
        </select>

        <!-- Client -->
        <select v-model="localClientId" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option value="">Semua Client</option>
          <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
        </select>

        <button v-if="hasActiveFilters" @click="resetFilters"
          class="text-xs text-gray-400 hover:text-gray-600 underline transition-colors">
          Reset
        </button>
      </div>

      <!-- Empty -->
      <div v-if="invoices.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Tidak ada invoice di {{ MONTHS[filters.month - 1] }} {{ filters.year }}.</p>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Status</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Invoice # / Project</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Recipient / Creator</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Issue Date / Due Date</th>
              <th class="text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
              <th class="px-5 py-3"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="invoice in invoices" :key="invoice.id"
              class="hover:bg-gray-50/50 transition-colors group">

              <!-- Status -->
              <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                  :class="statusClass(invoice.status)">
                  {{ statusLabel(invoice.status) }}
                </span>
              </td>

              <!-- Invoice # / Project -->
              <td class="px-4 py-4">
                <p class="font-mono text-sm font-semibold text-gray-800 whitespace-nowrap">{{ invoice.invoice_number }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ invoice.project_category?.name ?? '-' }}</p>
              </td>

              <!-- Recipient / Creator -->
              <td class="px-4 py-4">
                <p class="text-sm font-medium text-gray-800">{{ invoice.client?.company_name ?? '-' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ invoice.user?.name ?? '-' }}</p>
              </td>

              <!-- Issue / Due Date -->
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700">{{ fmtShort(invoice.issue_date) }}</p>
                <p class="text-xs mt-0.5 font-medium" :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                  → {{ fmtShort(invoice.due_date) }}
                  <span v-if="isPastDue(invoice)" class="ml-1 inline-flex items-center gap-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block animate-pulse"/>
                  </span>
                </p>
              </td>

              <!-- Total -->
              <td class="px-4 py-4 text-right">
                <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">{{ fmtCurrency(invoice.items_sum_amount) }}</p>
              </td>

              <!-- Actions -->
              <td class="px-5 py-4">
                <div class="flex items-center gap-2 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('invoices.show', invoice.id)"
                    class="text-xs px-3 py-1.5 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors font-medium whitespace-nowrap">
                    Lihat
                  </Link>
                  <Link :href="route('invoices.create', { from: invoice.id })"
                    class="text-xs px-3 py-1.5 border border-gray-200 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors whitespace-nowrap">
                    Perpanjang
                  </Link>
                  <button @click="deleteInvoice(invoice)"
                    class="text-xs px-3 py-1.5 border border-red-100 text-red-400 hover:bg-red-50 rounded-lg transition-colors">
                    Hapus
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

const props = defineProps({ invoices: Array, clients: Array, filters: Object });

const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

const localMonth    = ref(props.filters.month);
const localYear     = ref(props.filters.year);
const localStatus   = ref(props.filters.status ?? '');
const localClientId = ref(props.filters.client_id ?? '');

const hasActiveFilters = computed(() => localStatus.value || localClientId.value);

function applyFilters() {
  router.get(route('invoices.index'), {
    month:     localMonth.value,
    year:      localYear.value,
    status:    localStatus.value,
    client_id: localClientId.value,
  }, { preserveState: true, replace: true });
}

function resetFilters() {
  localStatus.value   = '';
  localClientId.value = '';
  applyFilters();
}

const statusClass = (s) => ({
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid: 'bg-red-50 text-red-600 ring-1 ring-red-200',
}[s] ?? 'bg-gray-100 text-gray-500');

const statusLabel = (s) => ({ draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[s] ?? s);

const isPastDue = (inv) => inv.status !== 'paid' && new Date(inv.due_date) < new Date();

const fmtShort = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtCurrency = (v) => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

function deleteInvoice(invoice) {
  if (!confirm(`Hapus invoice ${invoice.invoice_number}?`)) return;
  router.delete(route('invoices.destroy', invoice.id));
}
</script>
