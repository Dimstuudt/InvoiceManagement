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
          <div class="px-5 pt-5 pb-4 flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
              <span class="text-indigo-600 font-bold text-sm">{{ client.company_name.charAt(0) }}</span>
            </div>
            <div class="min-w-0">
              <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ client.company_name }}</h3>
              <p class="text-xs text-gray-400 mt-0.5 truncate">
                {{ client.city ?? '—' }}
                <span v-if="client.category"> &bull; {{ client.category.name }}</span>
              </p>
            </div>
          </div>

          <!-- Status counts -->
          <div class="px-5 pb-4 flex flex-wrap gap-2">
            <span v-if="client.draft_count"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
              Draft <span class="font-bold">{{ client.draft_count }}</span>
            </span>
            <span v-if="client.sent_count"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 ring-1 ring-blue-200">
              Sent <span class="font-bold">{{ client.sent_count }}</span>
            </span>
            <span v-if="client.unpaid_count"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-600 ring-1 ring-red-200">
              Unpaid <span class="font-bold">{{ client.unpaid_count }}</span>
            </span>
            <span v-if="client.paid_count"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
              Paid <span class="font-bold">{{ client.paid_count }}</span>
            </span>
            <span v-if="!totalInvoices(client)"
              class="text-xs text-gray-300 italic">Belum ada invoice</span>
          </div>

          <!-- Info produk -->
          <div class="px-5 pb-4">
            <p class="text-xs text-gray-400">
              <span class="font-medium text-gray-600">{{ client.product_type_count }}</span>
              jenis produk
            </p>
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

defineProps({ clients: Array });

const totalInvoices = (c) => (c.draft_count ?? 0) + (c.sent_count ?? 0) + (c.paid_count ?? 0) + (c.unpaid_count ?? 0);
</script>
