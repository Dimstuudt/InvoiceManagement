<template>
  <AppLayout :title="invoice.invoice_number">

    <!-- ACTION BAR -->
    <div class="no-print -mx-6 -mt-6 mb-6 bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between gap-4">

      <!-- Kembali -->
      <Link :href="route('invoices.index')"
        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </Link>

      <!-- Kiri: nomor invoice + status badge -->
      <div class="flex items-center gap-3 min-w-0">
        <div>
          <p class="text-xs text-gray-400">Invoice</p>
          <p class="font-mono text-sm font-semibold text-gray-800 truncate">{{ invoice.invoice_number }}</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium shrink-0"
          :class="{
            'bg-gray-100 text-gray-500': invoice.status === 'draft',
            'bg-blue-50 text-blue-600 ring-1 ring-blue-200': invoice.status === 'sent',
            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': invoice.status === 'paid',
            'bg-red-50 text-red-600 ring-1 ring-red-200': invoice.status === 'unpaid',
          }">
          {{ { draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[invoice.status] }}
        </span>
      </div>

      <!-- Kanan: tombol aksi -->
      <div class="flex items-center gap-2 shrink-0">

        <!-- Simpan item (disembunyikan kalau paid) -->
        <button v-if="invoice.status !== 'paid'" @click="saveItems" :disabled="savingItems"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          {{ savingItems ? 'Menyimpan...' : 'Simpan Item' }}
        </button>

        <!-- Ubah Status -->
        <select :value="invoice.status" @change="changeStatus($event.target.value)"
          class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="paid">Paid</option>
          <option value="unpaid">Unpaid</option>
        </select>

        <!-- Perpanjang -->
        <Link :href="route('invoices.create', { from: invoice.id })"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Perpanjang
        </Link>

        <!-- Edit (disembunyikan kalau paid) -->
        <Link v-if="invoice.status !== 'paid'" :href="route('invoices.edit', invoice.id)"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
          </svg>
          Edit
        </Link>

        <!-- Print -->
        <button @click="printPage"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print / PDF
        </button>

        <!-- Hapus -->
        <button @click="destroy"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-red-50 hover:bg-red-100 text-red-500 rounded-lg transition">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          Hapus
        </button>

      </div>
    </div>

    <!-- PAPER PREVIEW AREA -->
    <div class="invoice-bg -mx-6 -mb-6 min-h-screen py-8 px-6">
      <div class="invoice-paper mx-auto bg-white shadow-2xl relative" id="invoice-doc">

        <!-- CAP STATUS (layar saja, tidak ikut print) -->
        <div class="stamp no-print" :class="`stamp-${effectiveStamp}`" aria-hidden="true">
          {{ stampLabel[effectiveStamp] }}
        </div>

        <!-- KOP SURAT -->
        <div v-if="invoice.document_issuer?.header_image_url">
          <img :src="invoice.document_issuer.header_image_url"
            class="w-full object-contain" style="max-height:160px" alt="Kop Surat"/>
          <div class="border-b-4 border-gray-800"/>
        </div>
        <div v-else-if="invoice.document_issuer"
          class="border-b-2 border-gray-800 py-6" style="padding-left:5rem;padding-right:5rem">
          <p class="text-2xl font-black text-gray-900 tracking-wide uppercase">{{ invoice.document_issuer.name }}</p>
          <div class="flex flex-wrap gap-x-4 mt-1 text-xs text-gray-500">
            <span v-if="invoice.document_issuer.tax_id_name">{{ invoice.document_issuer.tax_id_name }}</span>
            <span v-if="invoice.document_issuer.tax_id_address">{{ invoice.document_issuer.tax_id_address }}</span>
          </div>
        </div>

        <!-- BODY -->
        <div style="padding: 2rem 5rem 3rem 5rem">

          <!-- Title + Meta -->
          <div class="flex items-start justify-between gap-6 mb-8">
            <div>
              <h1 class="text-4xl font-black text-gray-900 tracking-[0.2em] uppercase">Invoice</h1>
              <p class="font-mono text-sm text-gray-500 mt-1.5 tracking-wider">{{ invoice.invoice_number }}</p>
              <p v-if="invoice.spk_number" class="text-xs text-gray-400 mt-0.5">No. SPK: {{ invoice.spk_number }}</p>
            </div>
            <div class="shrink-0 text-right">
              <table class="text-sm border-separate" style="border-spacing: 0 5px">
                <tr>
                  <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Issue Date</td>
                  <td class="font-semibold text-gray-700">{{ fmt(invoice.issue_date) }}</td>
                </tr>
                <tr>
                  <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Due Date</td>
                  <td class="font-bold text-red-600">{{ fmt(invoice.due_date) }}</td>
                </tr>
                <tr v-if="invoice.project_category">
                  <td class="text-xs text-gray-400 font-medium pr-4 text-right whitespace-nowrap">Project</td>
                  <td class="text-gray-600">{{ invoice.project_category.name }}</td>
                </tr>
              </table>
            </div>
          </div>

          <hr class="border-gray-300 mb-7"/>

          <!-- Kepada -->
          <div class="mb-7">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Kepada Yth.</p>
            <p class="text-lg font-bold text-gray-900">{{ invoice.client?.company_name }}</p>
            <p v-if="invoice.client?.address" class="text-sm text-gray-600 mt-1 leading-relaxed">
              {{ invoice.client.address }}<span v-if="invoice.client.city">, {{ invoice.client.city }}</span>
            </p>
            <p v-if="invoice.attention" class="text-sm text-gray-500 mt-2">
              u.p. <span class="font-semibold text-gray-700">{{ invoice.attention }}</span>
            </p>
          </div>

          <!-- Notes -->
          <div v-if="invoice.notes" class="mb-7">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-2">Keterangan</p>
            <div class="border-l-4 border-gray-200 pl-4">
              <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ invoice.notes }}</p>
            </div>
          </div>

          <hr class="border-gray-200 mb-7"/>

          <!-- ITEMS TABLE (Editable) -->
          <div class="mb-8">
            <table class="w-full">
              <thead>
                <tr class="border-b-2 border-gray-800">
                  <th class="text-left text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em] pb-2.5 w-8">No</th>
                  <th class="text-left text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em] pb-2.5">Deskripsi</th>
                  <th class="text-right text-[10px] font-bold text-gray-500 uppercase tracking-[0.15em] pb-2.5 w-48">Harga</th>
                  <th class="w-6 no-print"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in items" :key="i"
                  class="group border-b border-gray-100 last:border-gray-300 hover:bg-indigo-50/20 transition-colors">
                  <td class="py-3 text-sm text-gray-400 align-middle pr-3">{{ i + 1 }}</td>
                  <td class="py-2.5 align-middle pr-6">
                    <input v-model="item.description" type="text"
                      placeholder="Klik untuk mengedit..."
                      class="w-full text-sm text-gray-800 bg-transparent border-b border-transparent group-hover:border-indigo-200 focus:border-indigo-400 focus:outline-none py-0.5 transition-colors placeholder:text-gray-300"/>
                  </td>
                  <td class="py-2.5 align-middle">
                    <input v-model="item.amount" type="number" min="0"
                      class="w-full text-sm text-right font-mono text-gray-800 bg-transparent border-b border-transparent group-hover:border-indigo-200 focus:border-indigo-400 focus:outline-none py-0.5 transition-colors"/>
                  </td>
                  <td class="py-2.5 align-middle pl-2 no-print">
                    <button type="button" @click="removeItem(i)" :disabled="items.length === 1"
                      class="opacity-0 group-hover:opacity-100 disabled:!opacity-0 text-gray-300 hover:text-red-400 transition-all">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Add item -->
            <button type="button" @click="addItem"
              class="no-print mt-3 text-xs text-indigo-500 hover:text-indigo-700 flex items-center gap-1.5 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah Item
            </button>

            <!-- Total -->
            <div class="mt-5 pt-4 border-t-2 border-gray-800 flex justify-end">
              <div class="text-right">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-1">Total</p>
                <p class="text-2xl font-black text-gray-900">{{ formatCurrency(total) }}</p>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 mb-7"/>

          <!-- Bank + Signature -->
          <div class="flex items-end justify-between gap-10">

            <!-- Bank -->
            <div v-if="invoice.bank_account">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-3">Pembayaran Ke</p>
              <div class="flex items-center gap-3 mb-2">
                <div v-if="invoice.bank_account.bank_logo_url"
                  class="w-12 h-12 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center p-1.5 shrink-0">
                  <img :src="invoice.bank_account.bank_logo_url" class="w-full h-full object-contain"/>
                </div>
                <div>
                  <p class="font-bold text-gray-900">{{ invoice.bank_account.bank_name }}</p>
                  <p class="text-xs text-gray-500">a/n {{ invoice.bank_account.name }}</p>
                </div>
              </div>
              <div class="inline-flex items-center gap-2 border border-gray-200 rounded-lg px-4 py-2 bg-gray-50">
                <span class="text-xs font-black text-gray-300">#</span>
                <span class="font-mono font-bold text-gray-800 tracking-widest">{{ invoice.bank_account.account_number }}</span>
              </div>
            </div>
            <div v-else class="flex-1"/>

            <!-- Signature -->
            <div v-if="invoice.with_signature && invoice.signature" class="text-center" style="min-width:180px">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-3">Hormat Kami,</p>
              <div class="flex items-center justify-center" style="height:80px">
                <img v-if="invoice.signature.signature_image_url"
                  :src="invoice.signature.signature_image_url"
                  class="max-h-full object-contain" style="max-width:180px" alt="TTD"/>
              </div>
              <div class="border-b border-gray-500 mt-1 mb-2 mx-4"/>
              <p class="font-bold text-gray-900 text-sm">{{ invoice.signature.name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ invoice.signature.position }}</p>
            </div>

          </div>
        </div>

        <!-- Tax ID footer -->
        <div v-if="invoice.document_issuer?.tax_id_name || invoice.document_issuer?.tax_id_address"
          class="border-t-2 border-gray-800 py-3 text-center" style="padding-left:5rem;padding-right:5rem">
          <p class="text-[10px] text-gray-500 leading-relaxed">
            <span v-if="invoice.document_issuer.tax_id_name">{{ invoice.document_issuer.tax_id_name }}</span>
            <span v-if="invoice.document_issuer.tax_id_name && invoice.document_issuer.tax_id_address"> &nbsp;|&nbsp; </span>
            <span v-if="invoice.document_issuer.tax_id_address">{{ invoice.document_issuer.tax_id_address }}</span>
            <span v-if="invoice.document_issuer.tax_id_number" class="ml-2 font-mono">NPWP: {{ invoice.document_issuer.tax_id_number }}</span>
          </p>
        </div>

      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({ invoice: Object })

const items       = ref(props.invoice.items.map(i => ({ ...i })))
const savingItems = ref(false)

const total = computed(() =>
  items.value.reduce((s, i) => s + (parseFloat(i.amount) || 0), 0)
)

const fmt = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
  : '—'

const formatCurrency = v =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)

const statusLabel = { draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }
const stampLabel  = { draft: 'DRAFT', sent: 'SENT', paid: 'PAID', unpaid: 'UNPAID', overdue: 'JATUH TEMPO' }

const effectiveStamp = computed(() => {
  const pastDue = new Date(props.invoice.due_date) < new Date()
  if (pastDue && props.invoice.status !== 'paid') return 'overdue'
  return props.invoice.status
})
const statusClass = {
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:   'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid: 'bg-red-50 text-red-600 ring-1 ring-red-200',
}
const statusDot = {
  draft: 'bg-gray-400', sent: 'bg-blue-500',
  paid: 'bg-emerald-500', unpaid: 'bg-red-500',
}

function addItem()     { items.value.push({ description: '', amount: 0 }) }
function removeItem(i) { if (items.value.length > 1) items.value.splice(i, 1) }

function saveItems() {
  savingItems.value = true
  router.patch(route('invoices.items', props.invoice.id),
    { items: items.value },
    { preserveScroll: true, onFinish: () => savingItems.value = false }
  )
}

function changeStatus(status) {
  router.patch(route('invoices.status', props.invoice.id), { status }, { preserveScroll: true })
}

function destroy() {
  Swal.fire({
    title: 'Hapus invoice ini?',
    text: props.invoice.invoice_number,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('invoices.destroy', props.invoice.id), {
      onSuccess: () => router.visit(route('invoices.index')),
    })
  })
}

function printPage() {
  const doc = document.getElementById('invoice-doc')
  const heightMm = Math.ceil(doc.scrollHeight * 0.264583) + 3
  const s = document.createElement('style')
  s.id = '__ph__'
  s.textContent = `@page { size: 210mm ${heightMm}mm; margin: 0; }`
  document.head.appendChild(s)
  window.print()
  document.getElementById('__ph__')?.remove()
}
</script>

<style>
.invoice-bg    { background: #e8eaed; }
.invoice-paper { width: 794px; border-radius: 0; }

.stamp {
  position: absolute;
  top: 160px;
  right: 72px;
  transform: rotate(-22deg);
  padding: 10px 22px;
  border: 5px solid;
  border-radius: 6px;
  font-size: 2rem;
  font-weight: 900;
  letter-spacing: 0.28em;
  font-family: 'Courier New', Courier, monospace;
  opacity: 0.18;
  pointer-events: none;
  user-select: none;
  z-index: 5;
  line-height: 1;
}
.stamp-draft  { border-color: #6b7280; color: #6b7280; }
.stamp-sent   { border-color: #2563eb; color: #2563eb; }
.stamp-paid   { border-color: #16a34a; color: #16a34a; }
.stamp-unpaid { border-color: #dc2626; color: #dc2626; }
.stamp-overdue { border-color: #b91c1c; color: #b91c1c; font-size: 1.5rem; letter-spacing: 0.15em; }

@page { size: A4 portrait; margin: 0; }

@media print {
  .no-print { display: none !important; }
  body * { visibility: hidden !important; }
  #invoice-doc, #invoice-doc * { visibility: visible !important; }
  #invoice-doc {
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 100% !important;
    box-shadow: none !important;
    background: white !important;
  }
  .invoice-bg {
    min-height: 0 !important;
    height: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
  }
  html { height: auto !important; }
  body {
    height: 0 !important;
    overflow: visible !important;
    background: white !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
