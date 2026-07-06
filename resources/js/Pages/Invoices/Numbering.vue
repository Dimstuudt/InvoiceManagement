<template>
  <AppLayout title="Urutan Nomor Invoice">
    <div class="space-y-5">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Urutan Nomor Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            Ledger penomoran ·
            <span v-if="month > 0">{{ monthName(month) }} {{ year }}</span>
            <span v-else>tahun {{ year }}</span>
            · {{ invoices.length }} invoice
          </p>
        </div>

        <!-- Year pills -->
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

      <!-- Month filter -->
      <div v-if="availableMonths.length > 0" class="flex items-center gap-1.5 flex-wrap">
        <Link
          :href="route('invoices.numbering', { year })"
          :class="[
            'px-3 py-1 rounded-lg text-xs font-medium transition-colors',
            month === 0
              ? 'bg-gray-800 text-white'
              : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
          ]"
        >Semua</Link>
        <Link
          v-for="m in availableMonths" :key="m"
          :href="route('invoices.numbering', { year, month: m })"
          :class="[
            'px-3 py-1 rounded-lg text-xs font-medium transition-colors',
            m === month
              ? 'bg-gray-800 text-white'
              : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
          ]"
        >{{ monthName(m) }}</Link>
      </div>

      <!-- Summary cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="bg-indigo-600 rounded-2xl p-4 text-white shadow-sm">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-200">Nomor Berikutnya</p>
          <p class="text-3xl font-bold mt-1 tabular-nums">{{ pad(nextSeq) }}</p>
          <p class="text-[11px] text-indigo-300 mt-1.5">global tahun {{ year }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Total Nilai</p>
          <p class="text-xl font-bold text-gray-900 mt-1 tabular-nums truncate">{{ fmtRp(summary.total_nilai) }}</p>
          <p class="text-[11px] text-gray-400 mt-1.5">{{ invoices.length }} invoice</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Sudah Lunas</p>
          <p class="text-xl font-bold text-emerald-600 mt-1 tabular-nums truncate">{{ fmtRp(summary.total_lunas) }}</p>
          <p class="text-[11px] text-emerald-500 mt-1.5">{{ invoices.filter(i => i.status === 'paid').length }} invoice paid</p>
        </div>

        <div :class="['rounded-2xl border shadow-sm p-4', summary.total_outstanding > 0 ? 'bg-amber-50 border-amber-100' : 'bg-white border-gray-100']">
          <p :class="['text-[11px] font-semibold uppercase tracking-wide', summary.total_outstanding > 0 ? 'text-amber-500' : 'text-gray-400']">Outstanding</p>
          <p :class="['text-xl font-bold mt-1 tabular-nums truncate', summary.total_outstanding > 0 ? 'text-amber-600' : 'text-gray-900']">{{ fmtRp(summary.total_outstanding) }}</p>
          <p :class="['text-[11px] mt-1.5 font-medium', summary.count_overdue > 0 ? 'text-red-500' : 'text-gray-400']">
            {{ summary.count_overdue > 0 ? `${summary.count_overdue} melewati jatuh tempo` : 'belum terbayar' }}
          </p>
        </div>

      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div v-if="invoices.length === 0" class="py-20 text-center">
          <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-sm text-gray-400">
            Tidak ada invoice
            {{ month > 0 ? `di ${monthName(month)} ${year}` : `di tahun ${year}` }}
          </p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/60">
                <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide w-16">No.</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nomor Invoice</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Client</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Terbit · Jatuh Tempo</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nominal</th>
                <th class="w-28"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="inv in invoices" :key="inv.id"
                :class="[
                  'border-b border-gray-50 transition-colors group',
                  inv.is_overdue ? 'bg-red-50/40 hover:bg-red-50/70' : 'hover:bg-gray-50/60'
                ]"
              >
                <!-- Seq -->
                <td class="px-5 py-4">
                  <span class="text-sm font-bold text-gray-800 tabular-nums">{{ pad(inv.seq) }}</span>
                </td>

                <!-- Invoice number — clickable -->
                <td class="px-4 py-4 cursor-pointer" @click="router.visit(route('invoices.show', inv.id))">
                  <span class="text-xs font-mono text-indigo-600 hover:text-indigo-800 hover:underline bg-indigo-50 px-2 py-1 rounded-lg whitespace-nowrap transition-colors">
                    {{ inv.invoice_number }}
                  </span>
                </td>

                <!-- Client -->
                <td class="px-4 py-4">
                  <span class="text-sm text-gray-800 font-medium">{{ inv.client?.company_name ?? '-' }}</span>
                </td>

                <!-- Dates -->
                <td class="px-4 py-4">
                  <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-gray-500 tabular-nums">{{ fmtDate(inv.issue_date) }}</span>
                    <span class="text-xs tabular-nums" :class="inv.is_overdue ? 'text-red-500 font-semibold' : 'text-gray-400'">
                      {{ inv.is_overdue ? '⚠ ' : '' }}{{ fmtDate(inv.due_date) }}
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-4 py-4">
                  <div class="flex flex-col gap-1">
                    <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold w-fit', statusClass(inv.status)]">
                      {{ statusLabel(inv.status) }}
                    </span>
                    <span v-if="inv.is_overdue" class="text-[10px] text-red-500 font-medium">Lewati jatuh tempo</span>
                    <span v-if="inv.is_marked" class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-yellow-700 bg-yellow-50 border border-yellow-200 px-1.5 py-0.5 rounded w-fit">
                      <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                      Dalam Antrean
                    </span>
                  </div>
                </td>

                <!-- Nominal -->
                <td class="px-5 py-4 text-right">
                  <div class="flex flex-col items-end gap-0.5">
                    <span class="text-sm font-bold tabular-nums" :class="inv.status === 'paid' ? 'text-emerald-600' : 'text-gray-900'">
                      {{ fmtRp(inv.computed_total) }}
                    </span>
                    <!-- chain indicator -->
                    <span v-if="inv.carried_from_id" class="text-[10px] text-orange-500 font-medium">incl. tunggakan carry</span>
                    <span v-else-if="inv.is_prepay && !inv.prepay_chain_id" class="text-[10px] text-teal-500 font-medium">incl. prepay chain</span>
                    <span v-else-if="inv.is_reaktivasi && !inv.reaktivasi_chain_id" class="text-[10px] text-violet-500 font-medium">incl. reaktivasi</span>
                    <!-- payment status -->
                    <span v-if="inv.status === 'paid'" class="text-[10px] text-emerald-500 font-medium">✓ Lunas</span>
                    <span v-else-if="inv.status === 'draft'" class="text-[10px] text-gray-400">Belum dikirim</span>
                    <span v-else-if="inv.status === 'frozen'" class="text-[10px] text-violet-400">Frozen</span>
                    <span v-else class="text-[10px] font-semibold" :class="inv.is_overdue ? 'text-red-500' : 'text-amber-500'">Belum dibayar</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-3 py-4">
                  <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                    <!-- Receipt -->
                    <button
                      @click.stop="openReceipt(inv)"
                      class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                      title="Receipt"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </button>
                    <!-- Download PDF -->
                    <a
                      :href="route('invoices.download', inv.id)"
                      class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                      title="Download PDF"
                      @click.stop
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                      </svg>
                    </a>
                    <!-- Go to detail -->
                    <button
                      @click.stop="router.visit(route('invoices.show', inv.id))"
                      class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                      title="Lihat detail"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Footer -->
            <tfoot>
              <tr class="bg-gray-50/80 border-t-2 border-gray-100">
                <td colspan="5" class="px-5 py-3 text-xs font-semibold text-gray-500">
                  Total {{ month > 0 ? monthName(month) : 'Tahun ' + year }}
                </td>
                <td class="px-5 py-3 text-right">
                  <div class="flex flex-col items-end gap-0.5">
                    <span class="text-sm font-bold text-gray-900 tabular-nums">{{ fmtRp(summary.total_nilai) }}</span>
                    <span class="text-[10px] text-gray-400 tabular-nums whitespace-nowrap">
                      Lunas {{ fmtRp(summary.total_lunas) }} · Sisa {{ fmtRp(summary.total_outstanding) }}
                    </span>
                  </div>
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>

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
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  invoices:        Array,
  year:            Number,
  month:           Number,
  years:           Array,
  availableMonths: Array,
  nextSeq:         Number,
  summary:         Object,
})

const receiptUrl = ref(null)
function openReceipt(inv) {
  receiptUrl.value = route('invoices.receipt', inv.id)
}

function pad(n) {
  return String(n).padStart(3, '0')
}

const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']
function monthName(m) {
  return MONTHS[(m - 1)] ?? ''
}

function fmtDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function fmtRp(v) {
  if (!v && v !== 0) return '-'
  return 'Rp ' + Number(v).toLocaleString('id-ID', { maximumFractionDigits: 0 })
}

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
