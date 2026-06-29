<template>
  <AppLayout :title="client.company_name">
    <div class="space-y-6">

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

      <template v-else>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3.5">
            <p class="text-xs text-gray-400 font-medium">Total Invoice</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ invoices.length }}</p>
            <p class="text-xs text-gray-300 mt-0.5">semua status</p>
          </div>
          <div class="bg-emerald-50 rounded-xl border border-emerald-100 px-4 py-3.5">
            <p class="text-xs text-emerald-600 font-medium">Sudah Dibayar</p>
            <p class="text-xl font-bold text-emerald-700 mt-1 truncate">{{ fmtCurrency(summary.paid) }}</p>
            <p class="text-xs text-emerald-400 mt-0.5">{{ summary.paidCount }} invoice</p>
          </div>
          <div class="bg-amber-50 rounded-xl border border-amber-100 px-4 py-3.5">
            <p class="text-xs text-amber-600 font-medium">Menunggu Bayar</p>
            <p class="text-xl font-bold text-amber-700 mt-1 truncate">{{ fmtCurrency(summary.pending) }}</p>
            <p class="text-xs text-amber-400 mt-0.5">{{ summary.pendingCount }} invoice</p>
          </div>
          <div class="bg-indigo-50 rounded-xl border border-indigo-100 px-4 py-3.5">
            <p class="text-xs text-indigo-600 font-medium">Layanan Berulang</p>
            <p class="text-xl font-bold text-indigo-700 mt-1">{{ recurringGroups.length }}</p>
            <p class="text-xs text-indigo-400 mt-0.5">produk aktif</p>
          </div>
        </div>

        <!-- ── RECURRING CHAINS ────────────────────────────────────── -->
        <div v-if="recurringGroups.length > 0" class="space-y-3">

          <div class="flex items-center gap-2 px-1">
            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Layanan Berulang</p>
            <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-semibold">
              {{ recurringGroups.length }}
            </span>
          </div>

          <div v-for="group in recurringGroups" :key="group.root.id"
            class="bg-white rounded-2xl border shadow-sm overflow-hidden"
            :class="chainBorderClass(group)">

            <!-- Chain header -->
            <div class="px-5 py-4 flex items-center justify-between gap-4"
              :class="chainHeaderClass(group)">
              <div class="flex items-center gap-3 min-w-0">
                <!-- Icon -->
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                  :class="chainIconClass(group)">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-semibold text-sm text-gray-900">
                      {{ group.root.project_category?.name ?? '—' }}
                    </p>
                    <span v-if="group.invoices[0]?.interval_months"
                      class="inline-flex items-center gap-1 text-xs font-medium text-violet-700 bg-violet-100 px-2 py-0.5 rounded-full shrink-0">
                      ↻ tiap {{ group.invoices[0].interval_months }} bulan
                    </span>
                    <span v-if="hasOverdue(group)"
                      class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 bg-red-100 px-2 py-0.5 rounded-full shrink-0">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                      </svg>
                      Jatuh tempo
                    </span>
                  </div>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ group.invoices.length }} periode &bull; mulai {{ fmtDate(group.root.issue_date) }}
                  </p>
                </div>
              </div>

              <!-- Total chain -->
              <div class="text-right shrink-0">
                <p class="text-base font-bold text-gray-900">{{ fmtCurrency(chainTotal(group)) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  <span class="text-emerald-600 font-semibold">{{ fmtCurrency(chainPaidTotal(group)) }}</span>
                  &nbsp;terbayar
                </p>
              </div>
            </div>

            <!-- Invoice timeline rows -->
            <div class="relative">
              <!-- Vertical line -->
              <div class="absolute left-[34px] top-0 bottom-0 w-px bg-gray-100 pointer-events-none"/>

              <div v-for="(invoice, idx) in group.invoices" :key="invoice.id"
                class="relative flex items-center gap-3 px-5 py-3 border-t border-gray-50 hover:bg-gray-50/60 transition-colors group">

                <!-- Status dot -->
                <div class="relative z-10 w-7 h-7 rounded-full flex items-center justify-center shrink-0 ring-2 ring-white shadow-sm"
                  :class="dotClass(invoice)">
                  <svg v-if="invoice.status === 'paid'" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                  <div v-else class="w-2 h-2 rounded-full bg-white"/>
                </div>

                <!-- Invoice number + dates -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-mono text-sm font-semibold text-gray-800 truncate">{{ invoice.invoice_number }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium shrink-0"
                      :class="statusClass(invoice.status)">
                      {{ statusLabel(invoice.status) }}
                    </span>
                    <span v-if="isPastDue(invoice)"
                      class="text-xs font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-md shrink-0">
                      lewat {{ daysPastDue(invoice) }} hari
                    </span>
                  </div>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ fmtDateShort(invoice.issue_date) }} → {{ fmtDateShort(invoice.due_date) }}
                  </p>
                </div>

                <!-- Amount -->
                <div class="text-right shrink-0">
                  <p class="text-sm font-semibold text-gray-800">{{ fmtCurrency(invoiceTotal(invoice)) }}</p>
                  <p v-if="invoice.tax_percentage" class="text-xs text-violet-500 mt-0.5">+PPN {{ invoice.tax_percentage }}%</p>
                </div>

                <!-- Actions (show on hover) -->
                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-1">
                  <Link :href="route('invoices.show', invoice.id) + '?from=client'" title="Lihat"
                    class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>
                  <button @click.stop="toggleMenu($event, invoice)" title="Lainnya"
                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                    </svg>
                  </button>
                  <button @click="deleteInvoice(invoice)" title="Hapus"
                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ── STANDALONE INVOICES ─────────────────────────────────── -->
        <div v-if="standaloneInvoices.length > 0" class="space-y-3">

          <div class="flex items-center gap-2 px-1">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Mandiri</p>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-semibold">
              {{ standaloneInvoices.length }}
            </span>
          </div>

          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                  <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Status</th>
                  <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Invoice # / Project</th>
                  <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                  <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Issuer</th>
                  <th class="text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
                  <th class="px-5 py-3"/>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="invoice in standaloneInvoices" :key="invoice.id"
                  :class="isPastDue(invoice) ? 'bg-red-50/40 hover:bg-red-50/70' : 'hover:bg-gray-50/50'"
                  class="transition-colors group">

                  <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                      :class="statusClass(invoice.status)">
                      {{ statusLabel(invoice.status) }}
                    </span>
                  </td>

                  <td class="px-4 py-4">
                    <p class="font-mono text-sm font-semibold text-gray-800 whitespace-nowrap">{{ invoice.invoice_number }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ invoice.project_category?.name ?? '-' }}</p>
                  </td>

                  <td class="px-4 py-4">
                    <p class="text-sm text-gray-700">{{ fmtDate(invoice.issue_date) }}</p>
                    <p class="text-xs mt-0.5 font-medium" :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                      → {{ fmtDate(invoice.due_date) }}
                    </p>
                    <span v-if="isPastDue(invoice)"
                      class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded-md">
                      lewat {{ daysPastDue(invoice) }} hari
                    </span>
                  </td>

                  <td class="px-4 py-4">
                    <p class="text-sm text-gray-700 whitespace-nowrap">{{ invoice.document_issuer?.name ?? '—' }}</p>
                  </td>

                  <td class="px-4 py-4 text-right">
                    <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                      {{ fmtCurrency(invoiceTotal(invoice)) }}
                    </p>
                    <span v-if="invoice.tax_percentage"
                      class="inline-flex items-center mt-1 px-1.5 py-0.5 bg-violet-50 text-violet-600 text-xs font-semibold rounded-md ring-1 ring-violet-200">
                      +PPN {{ invoice.tax_percentage }}%
                    </span>
                  </td>

                  <td class="px-4 py-4">
                    <div class="flex items-center gap-1 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                      <Link :href="route('invoices.show', invoice.id) + '?from=client'" title="Lihat"
                        class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </Link>
                      <button @click.stop="toggleMenu($event, invoice)" title="Lainnya"
                        class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                          <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                        </svg>
                      </button>
                      <button @click="deleteInvoice(invoice)" title="Hapus"
                        class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

      </template>
    </div>

    <!-- Three-dot Dropdown -->
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

    <!-- Receipt Modal -->
    <Teleport to="body">
      <div v-if="receiptUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="receiptUrl = null"/>
        <div class="relative z-10 w-full max-w-4xl h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
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
          <iframe :src="receiptUrl" class="flex-1 w-full border-0 bg-gray-50"/>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({ client: Object, invoices: Array });

// ── Menu / Modal state ──────────────────────────────────────
const receiptUrl  = ref(null);
const activeMenu  = ref(null);
const menuInvoice = ref(null);
const menuPosition = ref({ top: 0, right: 0 });

function toggleMenu(event, invoice) {
  if (activeMenu.value === invoice.id) { activeMenu.value = null; return; }
  const rect = event.currentTarget.getBoundingClientRect();
  menuPosition.value = { top: rect.bottom + window.scrollY + 4, right: window.innerWidth - rect.right };
  menuInvoice.value  = invoice;
  activeMenu.value   = invoice.id;
}
function closeMenu() { activeMenu.value = null; }
onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

function openReceipt(invoice) { receiptUrl.value = route('invoices.receipt', invoice.id); }

// ── Grouping logic ──────────────────────────────────────────
function getDescendants(parentId) {
  return props.invoices
    .filter(inv => inv.parent_invoice_id === parentId)
    .sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date))
    .flatMap(child => [child, ...getDescendants(child.id)]);
}

const groups = computed(() => {
  const roots = props.invoices
    .filter(inv => !inv.parent_invoice_id)
    .sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date));

  return roots.map(root => {
    const descendants = getDescendants(root.id);
    const all = [root, ...descendants].sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date));
    const isRecurring = descendants.length > 0 || !!root.interval_months;
    return { root, invoices: all, isRecurring };
  });
});

const recurringGroups   = computed(() => groups.value.filter(g => g.isRecurring));
const standaloneInvoices = computed(() => groups.value.filter(g => !g.isRecurring).map(g => g.root));

// ── Financial helpers ───────────────────────────────────────
function invoiceTotal(inv) {
  // subtotal = sum of item.amount - item.discount per item
  const itemsSum = parseFloat(inv.items_sum_amount) || 0
  const itemsDiscountSum = parseFloat(inv.items_sum_discount) || 0
  const subtotal = Math.max(0, itemsSum - itemsDiscountSum)

  // invoice-level discount
  let afterDiscount = subtotal
  if (inv.discount_value) {
    const disc = inv.discount_type === 'percent'
      ? subtotal * (inv.discount_value / 100)
      : parseFloat(inv.discount_value)
    afterDiscount = Math.max(0, subtotal - disc)
  }

  // DPP + tax
  const dppBase = inv.is_dpp ? afterDiscount * (11 / 12) : afterDiscount
  const tax = inv.tax_percentage ? dppBase * (inv.tax_percentage / 100) : 0

  return afterDiscount + tax
}
function chainTotal(group)     { return group.invoices.reduce((s, inv) => s + invoiceTotal(inv), 0); }
function chainPaidTotal(group) { return group.invoices.filter(i => i.status === 'paid').reduce((s, inv) => s + invoiceTotal(inv), 0); }

const summary = computed(() => {
  const paid    = props.invoices.filter(i => i.status === 'paid');
  const pending = props.invoices.filter(i => i.status !== 'paid');
  return {
    paid:         paid.reduce((s, i) => s + invoiceTotal(i), 0),
    paidCount:    paid.length,
    pending:      pending.reduce((s, i) => s + invoiceTotal(i), 0),
    pendingCount: pending.length,
  };
});

// ── Date / currency helpers ─────────────────────────────────
const fmtDate = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtDateShort = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: '2-digit' })
  : '-';

const fmtCurrency = v => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

const isPastDue    = inv => inv.status !== 'paid' && new Date(inv.due_date) < new Date(new Date().toDateString());
const daysPastDue  = inv => Math.floor((new Date(new Date().toDateString()) - new Date(inv.due_date)) / 86400000);
const hasOverdue   = group => group.invoices.some(isPastDue);

// ── Status display ──────────────────────────────────────────
const statusLabel = s => ({ draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[s] ?? s);
const statusClass = s => ({
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid: 'bg-red-50 text-red-600 ring-1 ring-red-200',
}[s] ?? 'bg-gray-100 text-gray-500');

const dotClass = inv => {
  if (isPastDue(inv))            return 'bg-red-500';
  return { draft: 'bg-gray-300', sent: 'bg-blue-400', paid: 'bg-emerald-500', unpaid: 'bg-red-400' }[inv.status] ?? 'bg-gray-300';
};

// ── Chain card styling ──────────────────────────────────────
function chainBorderClass(group) {
  if (hasOverdue(group))              return 'border-red-200';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'border-emerald-200';
  return 'border-gray-100';
}
function chainHeaderClass(group) {
  if (hasOverdue(group))              return 'bg-red-50/60';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'bg-emerald-50/60';
  return 'bg-gray-50/60';
}
function chainIconClass(group) {
  if (hasOverdue(group))              return 'bg-red-100 text-red-500';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'bg-emerald-100 text-emerald-600';
  return 'bg-indigo-100 text-indigo-500';
}

// ── Actions ─────────────────────────────────────────────────
function deleteInvoice(invoice) {
  Swal.fire({
    title: 'Hapus invoice ini?',
    text: invoice.invoice_number,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('invoices.destroy', invoice.id));
  });
}
</script>
