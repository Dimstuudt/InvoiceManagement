<template>
  <AppLayout title="Invoice">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ clients.length }} client aktif</p>
        </div>
        <Link :href="route('invoices.create')"
          class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Buat Invoice
        </Link>
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
          :class="summary.total_overdue > 0
            ? 'bg-red-50 border-red-200'
            : 'bg-white border-gray-100'">
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

      <!-- Empty -->
      <div v-if="clients.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <p class="text-gray-400 text-sm">Belum ada client aktif.</p>
      </div>

      <!-- Client cards grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="client in clients" :key="client.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden hover:shadow-md transition-shadow">

          <!-- Card header -->
          <div class="px-5 pt-5 pb-3 flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
              <span class="text-indigo-600 font-bold text-sm">{{ client.company_name.charAt(0) }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ client.company_name }}</h3>
                <!-- Overdue badge -->
                <span v-if="client.overdue_count"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-600 shrink-0">
                  <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
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
            <span v-if="!totalInvoices(client)"
              class="text-xs text-gray-300 italic">Belum ada invoice</span>
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
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ clients: Array, summary: Object });

const totalInvoices = (c) => (c.draft_count ?? 0) + (c.sent_count ?? 0) + (c.paid_count ?? 0) + (c.unpaid_count ?? 0);

const formatCurrency = v =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);

const fmtDate = d =>
  new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>
