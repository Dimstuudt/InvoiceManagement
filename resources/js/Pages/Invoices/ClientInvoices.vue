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

              <!-- Dates -->
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700">{{ fmtDate(invoice.issue_date) }}</p>
                <p class="text-xs mt-0.5 font-medium"
                  :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                  → {{ fmtDate(invoice.due_date) }}
                  <span v-if="isPastDue(invoice)"
                    class="ml-1 w-1.5 h-1.5 rounded-full bg-red-400 inline-block animate-pulse"/>
                </p>
              </td>

              <!-- Total -->
              <td class="px-4 py-4 text-right">
                <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                  {{ fmtCurrency(invoice.items_sum_amount) }}
                </p>
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

defineProps({ client: Object, invoices: Array });

const statusClass = (s) => ({
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid: 'bg-red-50 text-red-600 ring-1 ring-red-200',
}[s] ?? 'bg-gray-100 text-gray-500');

const statusLabel = (s) => ({ draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[s] ?? s);

const isPastDue = (inv) => inv.status !== 'paid' && new Date(inv.due_date) < new Date();

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
