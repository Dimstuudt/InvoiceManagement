<template>
  <AppLayout :title="invoice.invoice_number">

    <!-- ACTION BAR -->
    <div class="no-print -mx-6 -mt-6 mb-0 bg-white border-b border-gray-100 px-6 py-2.5 flex items-center gap-2 min-w-0">

      <!-- Kembali -->
      <Link :href="backRoute === 'invoices.client'
        ? route('invoices.client', props.invoice.client_id)
        : route(backRoute)"
        class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </Link>

      <div class="w-px h-5 bg-gray-200 shrink-0"/>

      <!-- Nomor invoice + status -->
      <div class="flex items-center gap-2 min-w-0 shrink-0">
        <p class="font-mono text-sm font-semibold text-gray-800 truncate max-w-[240px]">{{ invoice.invoice_number }}</p>
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium shrink-0"
          :class="{
            'bg-gray-100 text-gray-500': invoice.status === 'draft',
            'bg-blue-50 text-blue-600 ring-1 ring-blue-200': invoice.status === 'sent',
            'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': invoice.status === 'paid',
            'bg-red-50 text-red-600 ring-1 ring-red-200': invoice.status === 'unpaid',
          }">
          {{ { draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[invoice.status] }}
        </span>
        <template v-if="invoice.status !== 'paid'">
          <select :value="localInterval" @change="saveInterval($event.target.value)"
            class="text-xs border border-indigo-200 bg-indigo-50 text-indigo-600 rounded-md px-1.5 py-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer shrink-0">
            <option value="">↻ —</option>
            <option v-for="n in 12" :key="n" :value="n">↻ {{ n }} bln</option>
          </select>
        </template>
        <span v-else-if="invoice.interval_months"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-500 shrink-0">
          ↻ {{ invoice.interval_months }} bln
        </span>
      </div>

      <!-- Genealogi: ikon kompak -->
      <template v-if="invoice.parent || invoice.children?.length">
        <div class="w-px h-5 bg-gray-200 shrink-0"/>
        <div class="flex items-center gap-1 shrink-0">
          <!-- Parent (warisan dari) -->
          <Link v-if="invoice.parent" :href="route('invoices.show', invoice.parent.id)"
            :title="`Warisan dari: ${invoice.parent.invoice_number}`"
            class="p-1.5 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-100 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
          </Link>
          <!-- Child (perpanjangan ke) -->
          <Link v-if="invoice.children?.length" :href="route('invoices.show', invoice.children[0].id)"
            :title="`Perpanjangan: ${invoice.children[0].invoice_number}`"
            class="p-1.5 rounded-lg bg-indigo-50 text-indigo-500 hover:bg-indigo-100 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>
      </template>

      <!-- Spacer -->
      <div class="flex-1"/>

      <!-- GRUP 1: Simpan item (terpisah jelas, hanya muncul kalau bisa edit) -->
      <template v-if="invoice.status !== 'paid' && !invoice.is_marked">
        <button @click="saveItems" :disabled="savingItems"
          class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-lg transition shadow-sm shrink-0">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          {{ savingItems ? 'Menyimpan...' : 'Simpan Item' }}
        </button>
        <div class="w-px h-5 bg-gray-200 shrink-0"/>
      </template>

      <!-- GRUP 2: Status + navigasi dokumen -->
      <select :value="invoice.status" @change="changeStatus($event.target.value)"
        class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer shrink-0">
        <option value="draft">Draft</option>
        <option value="sent">Sent</option>
        <option value="paid">Paid</option>
        <option value="unpaid">Unpaid</option>
      </select>

      <Link v-if="invoice.status !== 'paid' && !invoice.is_marked" :href="route('invoices.edit', invoice.id)"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition shrink-0">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
        </svg>
        Edit
      </Link>

      <div class="w-px h-5 bg-gray-200 shrink-0"/>

      <!-- GRUP 3: Tandai + Kirim Email -->
      <button @click="toggleMark"
        :class="invoice.is_marked
          ? 'bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300'
          : 'bg-white border border-gray-200 hover:bg-gray-50 text-gray-500'"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition shrink-0">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        {{ invoice.is_marked ? 'Ditandai' : 'Tandai' }}
      </button>

      <button v-if="invoice.status !== 'paid' && invoice.is_marked" @click="emailModal = true"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition shrink-0">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
        </svg>
        Kirim Email
      </button>

      <div class="w-px h-5 bg-gray-200 shrink-0"/>

      <!-- GRUP 4: Output + Hapus -->
      <button @click="printPage"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm shrink-0">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print
      </button>

      <button @click="destroy"
        class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shrink-0">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Hapus
      </button>

    </div>

    <!-- NOTIF DITANDAI -->
    <div v-if="invoice.is_marked"
      class="no-print -mx-6 mb-6 bg-amber-50 border-b border-amber-200 px-6 py-2.5 flex items-center gap-2.5">
      <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
      </svg>
      <p class="text-xs text-amber-700 font-medium">
        Invoice ini sedang ditandai untuk batch kirim. Matikan tanda centang terlebih dahulu jika ingin mengedit.
      </p>
      <button @click="toggleMark" class="ml-auto text-xs text-amber-600 hover:text-amber-800 underline font-medium shrink-0">
        Hapus tanda
      </button>
    </div>
    <div v-else class="mb-6"/>

    <!-- PAPER PREVIEW AREA -->
    <div class="invoice-bg -mx-6 -mb-6 min-h-screen py-8 px-6">
      <div class="invoice-paper mx-auto bg-white shadow-2xl relative" id="invoice-doc">

        <!-- CAP STATUS (layar saja, tidak ikut print) -->
        <div class="stamp no-print" :class="`stamp-${effectiveStamp}`" aria-hidden="true">
          {{ stampLabel[effectiveStamp] }}
        </div>

        <!-- KOP SURAT -->
        <div v-if="invoice.document_issuer?.header_image_url">
          <img :src="invoice.document_issuer.header_image_url" class="w-full object-contain" style="max-height:160px" alt="Kop Surat"/>
        </div>
        <div v-else-if="invoice.document_issuer" class="px-10 py-6 border-b-4 border-blue-700">
          <p class="text-2xl font-black text-blue-800 uppercase tracking-wide">{{ invoice.document_issuer.name }}</p>
        </div>

        <!-- INVOICE TO -->
        <div class="px-10 pt-6 pb-4">
          <p class="text-xs text-gray-500 mb-1">Invoice To</p>
          <p class="text-2xl font-black text-blue-400 uppercase mb-5 leading-tight text-center">{{ invoice.client?.company_name }}</p>

          <!-- Dua kolom -->
          <div class="flex gap-8">
            <!-- Kiri: address -->
            <div class="flex-1">
              <table>
                <tr v-if="invoice.client?.address">
                  <td class="text-sm font-bold text-gray-900 pr-2 align-top pb-2 whitespace-nowrap">Address</td>
                  <td class="text-sm text-gray-700 pb-2 align-top">
                    : {{ invoice.client.address }}<span v-if="invoice.client.city">, {{ invoice.client.city }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-sm font-bold text-gray-900 pr-2 align-top whitespace-nowrap pt-0.5">Attention</td>
                  <td class="text-sm text-gray-700 align-top pt-0.5">:
                    <template v-if="invoice.status !== 'paid'">
                      <input v-model="localAttention" @blur="saveMeta"
                        placeholder="—"
                        class="no-print ml-1 text-sm text-gray-700 bg-transparent border-b border-transparent hover:border-blue-200 focus:border-blue-400 focus:outline-none transition-colors w-48"/>
                      <span class="print-only">{{ localAttention || '—' }}</span>
                    </template>
                    <span v-else class="ml-1">{{ localAttention || '—' }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-sm font-bold text-gray-900 pr-2 align-top whitespace-nowrap pt-1">Notes</td>
                  <td class="text-sm text-gray-700 align-top pt-1">:
                    <template v-if="invoice.status !== 'paid'">
                      <input v-model="localNotes" @blur="saveMeta"
                        placeholder="—"
                        class="no-print ml-1 text-sm text-gray-700 bg-transparent border-b border-transparent hover:border-blue-200 focus:border-blue-400 focus:outline-none transition-colors w-48"/>
                      <span class="print-only">{{ localNotes || '—' }}</span>
                    </template>
                    <span v-else class="ml-1">{{ localNotes || '—' }}</span>
                  </td>
                </tr>
              </table>
            </div>
            <!-- Kanan: meta invoice -->
            <div class="shrink-0">
              <table>
                <tr>
                  <td class="text-sm font-bold text-gray-900 pr-3 pb-1.5 whitespace-nowrap">No Invoice</td>
                  <td class="text-sm text-gray-700 pb-1.5">: {{ invoice.invoice_number }}</td>
                </tr>
                <tr>
                  <td class="text-sm font-bold text-gray-900 pr-3 pb-1.5 whitespace-nowrap">Invoice Date</td>
                  <td class="text-sm text-gray-700 pb-1.5">: {{ fmtShort(invoice.issue_date) }}</td>
                </tr>
                <tr :class="isOverdue ? 'bg-red-50 rounded-lg' : ''">
                  <td class="pr-3 pb-1.5 pt-1 whitespace-nowrap text-sm font-bold rounded-l-lg"
                    :class="isOverdue ? 'text-red-500' : 'text-gray-900'">Due Date</td>
                  <td class="pb-1.5 pt-1 pr-1.5 text-sm rounded-r-lg" :class="isOverdue ? 'text-red-500' : 'text-gray-700'">
                    : {{ fmtShort(invoice.due_date) }}
                    <span v-if="isOverdue"
                      class="ml-2 inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded-md">
                      <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> lewat {{ overdueDays }} hari
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="text-sm font-bold text-gray-900 pr-3 whitespace-nowrap">SPK No</td>
                  <td class="text-sm text-gray-700">: {{ invoice.spk_number ?? '-' }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="mx-10 mt-2 mb-0 bg-gray-50 rounded-xl overflow-hidden border border-gray-200">
          <table class="w-full">
            <thead>
              <tr>
                <th class="text-left text-sm font-bold text-blue-600 px-4 pt-2.5 pb-2">Description</th>
                <th class="text-right text-sm font-bold text-blue-600 px-4 pt-2.5 pb-2 w-44">Price</th>
                <th class="w-6 no-print"></th>
              </tr>
              <tr>
                <td colspan="3" class="px-3 pb-0">
                  <div class="border-b-2 border-blue-600"></div>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in items" :key="i"
                class="group border-b border-gray-200 hover:bg-blue-50/30 transition-colors">
                <td class="px-4 py-2.5 align-middle">
                  <input v-if="invoice.status !== 'paid'" v-model="item.description" type="text"
                    placeholder="Klik untuk mengedit..."
                    class="w-full text-sm text-gray-800 bg-transparent border-b border-transparent group-hover:border-blue-200 focus:border-blue-400 focus:outline-none py-0.5 transition-colors placeholder:text-gray-300"/>
                  <span v-else class="text-sm text-gray-800">{{ item.description }}</span>
                </td>
                <td class="px-4 py-2.5 align-middle">
                  <input v-if="invoice.status !== 'paid'" v-model="item.amount" type="number" min="0"
                    class="w-full text-sm text-right font-mono text-gray-800 bg-transparent border-b border-transparent group-hover:border-blue-200 focus:border-blue-400 focus:outline-none py-0.5 transition-colors"/>
                  <span v-else class="block text-sm text-right font-mono text-gray-800">{{ formatCurrency(item.amount) }}</span>
                </td>
                <td v-if="invoice.status !== 'paid'" class="py-2.5 pl-2 align-middle no-print">
                  <button type="button" @click="removeItem(i)" :disabled="items.length === 1"
                    class="opacity-0 group-hover:opacity-100 disabled:!opacity-0 text-gray-300 hover:text-red-400 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <!-- Tax toggle – no-print, hanya tampil kalau bukan paid -->
              <tr v-if="invoice.status !== 'paid'" class="no-print border-t border-gray-200">
                <td colspan="3" class="px-4 py-2.5">
                  <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400 font-medium">Tambahkan Pajak</span>
                    <button type="button"
                      @click="toggleTax"
                      class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none"
                      :class="localTax !== null ? 'bg-indigo-600' : 'bg-gray-200'">
                      <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow"
                        :class="localTax !== null ? 'translate-x-[18px]' : 'translate-x-0.5'"/>
                    </button>
                    <template v-if="localTax !== null">
                      <input v-model.number="localTax" type="number" min="0" max="100" step="0.01"
                        class="w-20 text-xs font-mono px-2 py-1 border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-center"
                        placeholder="11" />
                      <span class="text-xs text-gray-400">%</span>
                      <button @click="saveTax"
                        class="text-xs px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        Simpan
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
              <tr class="border-t border-gray-200">
                <td class="px-4 py-2 text-right text-sm text-gray-500" colspan="3">
                  <span class="mr-8">Sub Total</span>
                  <span class="font-mono text-gray-800">{{ formatCurrency(subtotal) }}</span>
                </td>
              </tr>
              <tr v-if="localTax !== null">
                <td class="px-4 py-1.5 text-right text-sm text-gray-500" colspan="3">
                  <span class="mr-8">Pajak {{ localTax }}%</span>
                  <span class="font-mono text-gray-800">{{ formatCurrency(taxAmount) }}</span>
                </td>
              </tr>
              <tr>
                <td colspan="3" class="px-4 pb-3 pt-1 text-right">
                  <span class="inline-flex items-center gap-8 bg-blue-700 rounded-lg px-5 py-2.5 font-black text-white">
                    <span class="uppercase tracking-wider text-sm">TOTAL</span>
                    <span class="font-mono text-base">{{ formatCurrency(total) }}</span>
                  </span>
                </td>
              </tr>
            </tfoot>
          </table>

          <!-- Add item – hanya tampil kalau bukan paid -->
          <button v-if="invoice.status !== 'paid'" type="button" @click="addItem"
            class="no-print px-4 py-2 text-xs text-blue-500 hover:text-blue-700 flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Item
          </button>
        </div>

        <!-- PAYMENT METHOD + SIGNATURE -->
        <div class="flex items-start gap-6 px-10 pt-8 pb-8">

          <!-- Payment Method -->
          <div v-if="invoice.bank_account" class="flex-1">
            <p class="text-sm font-bold text-gray-800 mb-3">Payment Method</p>
            <p class="text-xs text-gray-500 leading-relaxed">
              Pembayaran dapat dilakukan melalui transfer atau setoran<br/>tunai ke {{ invoice.bank_account.bank_name }}
            </p>
            <p class="text-sm font-bold text-gray-900 mt-1 mb-1">{{ invoice.document_issuer?.name ?? invoice.bank_account.bank_name }}</p>
            <p class="text-sm font-semibold text-blue-700 font-mono mb-3">
              {{ invoice.bank_account.account_number }}
              <span class="font-normal text-gray-400 font-sans">(atas nama {{ invoice.bank_account.name }})</span>
            </p>
            <template v-if="invoice.document_issuer?.tax_id_number">
              <p class="text-sm font-bold text-gray-900">NPWP : {{ invoice.document_issuer.tax_id_number }}</p>
              <p class="text-sm font-bold text-gray-900">{{ invoice.document_issuer.name }}</p>
            </template>
            <p v-if="invoice.document_issuer?.tax_id_address" class="text-xs text-gray-500 mt-0.5">{{ invoice.document_issuer.tax_id_address }}</p>
          </div>
          <div v-else class="flex-1"/>

          <!-- Signature -->
          <div v-if="invoice.with_signature && invoice.signature" class="text-center shrink-0" style="min-width:200px">
            <div class="flex items-center justify-center" style="height:80px">
              <img v-if="invoice.signature.signature_image_url"
                :src="invoice.signature.signature_image_url"
                class="max-h-full object-contain" style="max-width:200px" alt="TTD"/>
            </div>
            <div class="border-b border-gray-500 mt-1 mb-2 mx-6"/>
            <p class="font-bold text-gray-900 text-sm">{{ invoice.signature.name }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ invoice.signature.position }}</p>
            <p v-if="invoice.document_issuer" class="text-xs text-gray-500 mt-0.5">{{ invoice.document_issuer.name }}</p>
          </div>

        </div>

      </div>
    </div>

  </AppLayout>
  <!-- Send Email Modal -->
  <SendEmailModal
    :show="emailModal"
    :invoice="invoice"
    :client-emails="invoice.client_emails ?? []"
    :email-templates="emailTemplates ?? []"
    @close="emailModal = false"
  />

</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SendEmailModal from '@/Components/SendEmailModal.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({ invoice: Object, emailTemplates: Array })

const emailModal  = ref(false)
const backRoute   = ref('invoices.index')

onMounted(() => {
  const from = new URLSearchParams(window.location.search).get('from')
  if (from === 'schedule') backRoute.value = 'invoices.schedule'
  if (from === 'client')   backRoute.value = 'invoices.client'
})

const items          = ref(props.invoice.items.map(i => ({ ...i })))
const savingItems    = ref(false)
const localTax       = ref(props.invoice.tax_percentage ?? null)
const localAttention = ref(props.invoice.attention ?? '')
const localNotes     = ref(props.invoice.notes ?? '')
const localInterval  = ref(props.invoice.interval_months ?? '')

function saveInterval(val) {
  localInterval.value = val === '' ? '' : parseInt(val)
  router.patch(route('invoices.interval', props.invoice.id),
    { interval_months: val === '' ? null : parseInt(val) },
    { preserveScroll: true }
  )
}

function saveMeta() {
  router.patch(route('invoices.meta', props.invoice.id),
    { attention: localAttention.value || null, notes: localNotes.value || null },
    { preserveScroll: true }
  )
}

const subtotal = computed(() =>
  items.value.reduce((s, i) => s + (parseFloat(i.amount) || 0), 0)
)

const taxAmount = computed(() => {
  if (localTax.value === null || localTax.value === 0) return 0
  return subtotal.value * (localTax.value / 100)
})

const total = computed(() => subtotal.value + taxAmount.value)

const fmt = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
  : '—'

const fmtShort = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })
  : '-'

const formatCurrency = v =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)

const statusLabel = { draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }
const stampLabel  = { draft: 'DRAFT', sent: 'SENT', paid: 'PAID', unpaid: 'UNPAID', overdue: 'JATUH TEMPO' }

const isOverdue = computed(() => {
  if (props.invoice.status === 'paid') return false
  return new Date(props.invoice.due_date) < new Date(new Date().toDateString())
})

const overdueDays = computed(() => {
  const diff = new Date(new Date().toDateString()) - new Date(props.invoice.due_date)
  return Math.floor(diff / 86400000)
})

const effectiveStamp = computed(() => {
  if (isOverdue.value) return 'overdue'
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

function toggleTax() {
  localTax.value = localTax.value !== null ? null : 11
  router.patch(route('invoices.tax', props.invoice.id),
    { tax_percentage: localTax.value },
    { preserveScroll: true }
  )
}

function saveTax() {
  router.patch(route('invoices.tax', props.invoice.id),
    { tax_percentage: localTax.value },
    { preserveScroll: true }
  )
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

function toggleMark() {
  router.patch(route('invoices.mark', props.invoice.id), {}, { preserveScroll: true })
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
