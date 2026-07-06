<template>
  <AppLayout title="Urutan Nomor Invoice">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Urutan Nomor Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ invoices.length }} invoice · tahun {{ year }}</p>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
          <Link
            v-for="y in years" :key="y"
            :href="route('invoices.numbering', { year: y })"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors',
              y === year
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600'
            ]"
          >{{ y }}</Link>
        </div>
      </div>

      <!-- Next number banner -->
      <div class="bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wide">Nomor urut berikutnya</p>
          <p class="text-2xl font-bold text-indigo-700 tabular-nums mt-0.5">{{ pad(nextSeq) }}</p>
        </div>
        <p class="text-xs text-indigo-400 ml-2">— akan otomatis ter-generate saat membuat invoice baru</p>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="invoices.length === 0" class="py-20 text-center">
          <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-sm text-gray-400">Belum ada invoice di tahun {{ year }}</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/60">
                <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide w-20">No.</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nomor Invoice</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Client</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Tanggal Terbit</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Jatuh Tempo</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                <th class="w-8"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="inv in invoices" :key="inv.id"
                class="border-b border-gray-50 hover:bg-indigo-50/40 transition-colors cursor-pointer group"
                @click="router.visit(route('invoices.show', inv.id))"
              >
                <td class="px-5 py-3.5">
                  <span class="text-sm font-bold text-gray-800 tabular-nums">{{ pad(inv.seq) }}</span>
                </td>
                <td class="px-4 py-3.5">
                  <span class="text-xs font-mono text-gray-700 bg-gray-100 px-2 py-1 rounded-lg whitespace-nowrap">{{ inv.invoice_number }}</span>
                </td>
                <td class="px-4 py-3.5 text-sm text-gray-700">{{ inv.client?.company_name ?? '-' }}</td>
                <td class="px-4 py-3.5 text-sm text-gray-500 tabular-nums whitespace-nowrap">{{ fmtDate(inv.issue_date) }}</td>
                <td class="px-4 py-3.5 text-sm text-gray-500 tabular-nums whitespace-nowrap">{{ fmtDate(inv.due_date) }}</td>
                <td class="px-4 py-3.5">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold', statusClass(inv.status)]">
                    {{ statusLabel(inv.status) }}
                  </span>
                </td>
                <td class="px-3 py-3.5">
                  <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  invoices: Array,
  year: Number,
  years: Array,
})

function pad(n) {
  return String(n).padStart(3, '0')
}

function fmtDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

const maxSeq  = computed(() => props.invoices.length ? Math.max(...props.invoices.map(i => i.seq)) : 0)
const nextSeq = computed(() => maxSeq.value + 1)

function statusLabel(s) {
  return { draft: 'Draft', sent: 'Terkirim', paid: 'Lunas', unpaid: 'Belum Bayar', frozen: 'Frozen' }[s] ?? s
}

function statusClass(s) {
  return {
    draft:  'bg-gray-100 text-gray-500',
    sent:   'bg-blue-100 text-blue-600',
    paid:   'bg-emerald-100 text-emerald-600',
    unpaid: 'bg-red-100 text-red-600',
    frozen: 'bg-violet-100 text-violet-600',
  }[s] ?? 'bg-gray-100 text-gray-500'
}
</script>
