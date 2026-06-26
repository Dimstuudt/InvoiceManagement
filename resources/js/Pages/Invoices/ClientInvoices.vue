<template>
  <AppLayout :title="client.company_name">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link :href="route('invoices.index')"
            class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ client.company_name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5">
              {{ client.city ?? '—' }}
              <span v-if="client.category"> &bull; {{ client.category.name }}</span>
              &bull; {{ invoices.length }} invoice
            </p>
          </div>
        </div>
        <Link :href="route('invoices.create', { client_id: client.id })"
          class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shrink-0">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Buat Invoice
        </Link>
      </div>

      <!-- Empty -->
      <div v-if="invoices.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada invoice untuk client ini.</p>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Status</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Invoice # / Project</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Issue / Due Date</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Issuer</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Dibuat oleh</th>
              <th class="text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
              <th class="px-5 py-3"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="invoice in invoices" :key="invoice.id"
              :class="isPastDue(invoice) ? 'bg-red-50/60 hover:bg-red-50' : 'hover:bg-gray-50/50'"
              class="transition-colors group">

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

              <!-- Dates -->
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700">{{ fmtDate(invoice.issue_date) }}</p>
                <p class="text-xs mt-0.5 font-medium" :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                  → {{ fmtDate(invoice.due_date) }}
                </p>
                <span v-if="isPastDue(invoice)"
                  class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded-md">
                  <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> lewat {{ daysPastDue(invoice) }} hari
                </span>
              </td>

              <!-- Issuer -->
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700 whitespace-nowrap">{{ invoice.document_issuer?.name ?? '—' }}</p>
              </td>

              <!-- Dibuat oleh -->
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700 whitespace-nowrap">{{ invoice.user?.name ?? '—' }}</p>
              </td>

              <!-- Total -->
              <td class="px-4 py-4 text-right">
                <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                  {{ fmtCurrency(invoice.items_sum_amount) }}
                </p>
                <span v-if="invoice.tax_percentage"
                  class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-violet-50 text-violet-600 text-xs font-semibold rounded-md ring-1 ring-violet-200">
                  PPN {{ invoice.tax_percentage }}%
                </span>
                <span v-else class="inline-flex items-center mt-1 px-1.5 py-0.5 text-xs text-gray-300">
                  No tax
                </span>
              </td>

              <!-- Actions -->
              <td class="px-4 py-4">
                <div class="flex items-center gap-1 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                  <!-- Lihat -->
                  <Link :href="route('invoices.show', invoice.id)" title="Lihat Invoice"
                    class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>
                  <!-- Perpanjang -->
                  <Link :href="route('invoices.create', { from: invoice.id })" title="Perpanjang Invoice"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                  </Link>
                  <!-- Hapus -->
                  <button @click="deleteInvoice(invoice)" title="Hapus Invoice"
                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                  <!-- Three-dot menu -->
                  <button @click.stop="toggleMenu($event, invoice)"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  <!-- Three-dot Dropdown (teleported to avoid overflow-hidden clip) -->
  <Teleport to="body">
    <div v-if="activeMenu !== null && menuInvoice"
      class="fixed w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
      :style="{ top: menuPosition.top + 'px', right: menuPosition.right + 'px' }">
      <a :href="route('invoices.download', menuInvoice.id)"
        class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Download PDF
      </a>
      <button @click="openReceipt(menuInvoice); activeMenu = null"
        class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Receipt
      </button>
      <a :href="route('invoices.print-view', menuInvoice.id)" target="_blank"
        class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Invoice
      </a>
    </div>
  </Teleport>

  <!-- Receipt Preview Modal -->
  <Teleport to="body">
    <div v-if="receiptUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="receiptUrl = null"/>
      <!-- Panel -->
      <div class="relative z-10 w-full max-w-4xl h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
          <p class="text-sm font-semibold text-gray-700">Preview Receipt</p>
          <div class="flex items-center gap-2">
            <a :href="receiptUrl" target="_blank"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
              Buka di tab baru
            </a>
            <button @click="receiptUrl = null"
              class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <!-- iframe -->
        <iframe :src="receiptUrl" class="flex-1 w-full border-0 bg-gray-50"/>
      </div>
    </div>
  </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({ client: Object, invoices: Array });

const receiptUrl = ref(null);
const activeMenu = ref(null);
const menuInvoice = ref(null);
const menuPosition = ref({ top: 0, right: 0 });

function toggleMenu(event, invoice) {
  if (activeMenu.value === invoice.id) {
    activeMenu.value = null;
    return;
  }
  const rect = event.currentTarget.getBoundingClientRect();
  menuPosition.value = {
    top: rect.bottom + window.scrollY + 4,
    right: window.innerWidth - rect.right,
  };
  menuInvoice.value = invoice;
  activeMenu.value = invoice.id;
}

function closeMenu() { activeMenu.value = null; }
onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

function openReceipt(invoice) {
  receiptUrl.value = route('invoices.receipt', invoice.id);
}

const statusClass = (s) => ({
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid: 'bg-red-50 text-red-600 ring-1 ring-red-200',
}[s] ?? 'bg-gray-100 text-gray-500');

const statusLabel = (s) => ({ draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[s] ?? s);

const isPastDue = (inv) => inv.status !== 'paid' && new Date(inv.due_date) < new Date(new Date().toDateString());

const daysPastDue = (inv) => Math.floor((new Date(new Date().toDateString()) - new Date(inv.due_date)) / 86400000);

const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtCurrency = (v) => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

function deleteInvoice(invoice) {
  if (!confirm(`Hapus invoice ${invoice.invoice_number}?`)) return;
  router.delete(route('invoices.destroy', invoice.id), {
    onSuccess: () => router.reload(),
  });
}
</script>
