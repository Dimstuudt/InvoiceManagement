<template>
  <AppLayout title="Semua Invoice">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Semua Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            Referensi bulan <span class="font-medium text-gray-600">{{ MONTHS[filters.month - 1] }} {{ filters.year }}</span>
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

      <!-- Filter bulan referensi -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 flex flex-wrap items-center gap-3">
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Bulan referensi</span>
        <select v-model="localMonth" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="(name, i) in MONTHS" :key="i" :value="i + 1">{{ name }}</option>
        </select>
        <select v-model="localYear" @change="applyFilters"
          class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <span class="text-[10px] text-gray-400">→ prioritas issue <span class="font-semibold text-indigo-500">{{ nextMonthLabel }}</span></span>
        <div class="relative ml-auto">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Cari nomor invoice..."
            class="pl-8 pr-8 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition w-56"/>
          <button v-if="search" @click="search = ''"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- PRIORITY SECTION -->
      <div>
        <div class="flex items-center gap-3 mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse inline-block"/>
            <span class="text-sm font-semibold text-gray-700">Akan dikirim {{ nextMonthLabel }}</span>
          </div>
          <span class="text-xs text-gray-400">{{ filteredPriority.length }} invoice</span>
        </div>

        <div v-if="filteredPriority.length === 0"
          class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-8 text-center">
          <p class="text-sm text-indigo-300">{{ search ? `Tidak ditemukan "${search}"` : 'Tidak ada invoice yang akan dikirim di ' + nextMonthLabel + '.' }}</p>
        </div>

        <div v-else class="bg-white rounded-2xl border border-indigo-100 shadow-sm overflow-hidden">
          <table class="w-full">
            <thead>
              <tr class="border-b border-indigo-50 bg-indigo-50/40">
                <th class="px-4 py-3 w-8"/>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-3 py-3">Status</th>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Invoice # / Project</th>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Recipient</th>
                <th class="text-left text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Issue / Due Date</th>
                <th class="text-right text-[10px] font-semibold text-indigo-300 uppercase tracking-wider px-4 py-3">Total</th>
                <th class="px-5 py-3"/>
              </tr>
            </thead>
            <tbody class="divide-y divide-indigo-50/50">
              <tr v-for="invoice in filteredPriority" :key="invoice.id"
                :class="invoice.document_status === 'verified' ? 'bg-amber-50/60' : isPastDue(invoice) ? 'bg-red-50/60 hover:bg-red-50' : 'hover:bg-indigo-50/30'"
                class="transition-colors group">
                <!-- Checkbox -->
                <td class="px-4 py-4">
                  <input type="checkbox" :checked="invoice.document_status === 'verified'"
                    @change="toggleMark(invoice)"
                    class="w-4 h-4 rounded border-gray-300 text-amber-500 cursor-pointer focus:ring-amber-400"/>
                </td>
                <td class="px-3 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium" :class="statusClass(computedStatus(invoice))">
                    {{ statusLabel(computedStatus(invoice)) }}
                  </span>
                  <template v-if="invoice.send_status !== 'unsent' || (invoice.document_status === 'verified' && invoice.payment_status === 'unpaid')">
                    <div class="flex items-center gap-0.5 mt-1.5">
                      <span v-for="st in ['send1','send2','send3','send4','send5']" :key="st"
                        class="w-2 h-2 rounded-full"
                        :class="stageReached(invoice.send_status, st)
                          ? (invoice.payment_status === 'paid' ? 'bg-emerald-300' : st === 'send5' ? 'bg-red-400' : 'bg-indigo-400')
                          : 'bg-gray-200'"/>
                      <span class="text-[10px] font-mono ml-1"
                        :class="invoice.payment_status === 'paid' && invoice.send_status !== 'unsent' ? 'text-emerald-500' : invoice.send_status === 'unsent' ? 'text-gray-400' : invoice.send_status === 'send5' ? 'text-red-500 font-semibold' : 'text-indigo-500 font-semibold'">
                        {{ invoice.send_status }}
                      </span>
                    </div>
                    <p v-if="nextSendInfo(invoice)" class="text-[10px] mt-1 leading-none"
                      :class="nextSendInfo(invoice).soon ? 'text-amber-500 font-semibold' : 'text-gray-400'">
                      {{ nextSendInfo(invoice).stage }}: {{ nextSendInfo(invoice).soon ? 'secepatnya' : fmtDateShort(nextSendInfo(invoice).date) }}
                    </p>
                    <p v-else-if="invoice.send_status === 'send5' && invoice.payment_status === 'unpaid'" class="text-[10px] mt-1 text-red-400">sudah 5× kirim</p>
                    <p v-else-if="invoice.payment_status === 'paid' && invoice.send_status !== 'unsent'" class="text-[10px] mt-1 text-emerald-600">tidak dilanjutkan · lunas</p>
                  </template>
                </td>
                <td class="px-4 py-4">
                  <p class="font-mono text-sm font-semibold text-gray-800 whitespace-nowrap">{{ invoice.invoice_number }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ invoice.project_category?.name ?? '-' }}</p>
                  <span v-if="invoice.interval_months"
                    class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-500 text-xs font-medium rounded-md">
                    ↻ {{ invoice.invoice_type === 'yearly' ? (invoice.interval_months / 12) + ' thn' : invoice.interval_months + ' bln' }}
                  </span>
                  <span v-if="invoice.is_chain_head"
                    class="mt-1 ml-1 inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded-md"
                    :class="chainBadgeClass(invoice.chain_type)">
                    {{ invoice.chain_type }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <p class="text-sm font-medium text-gray-800">{{ invoice.client?.company_name ?? '-' }}</p>
                </td>
                <td class="px-4 py-4">
                  <p class="text-sm text-gray-700">{{ fmtDate(invoice.chain_issue_date ?? invoice.issue_date) }}</p>
                  <p class="text-xs mt-0.5 font-medium" :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                    → {{ fmtDate(invoice.chain_due_date ?? invoice.due_date) }}
                  </p>
                  <span v-if="isPastDue(invoice)"
                    class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded-md">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> lewat {{ daysPastDue(invoice) }} hari
                  </span>
                </td>
                <td class="px-4 py-4 text-right">
                  <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">{{ fmtCurrency(invoice.is_chain_head ? invoice.chain_total : invoice.items_sum_amount) }}</p>
                  <span v-if="invoice.is_chain_head"
                    class="inline-flex items-center mt-1 px-1.5 py-0.5 text-xs font-semibold rounded-md"
                    :class="chainBadgeClass(invoice.chain_type)">
                    total {{ invoice.chain_type }}
                  </span>
                  <template v-else>
                    <span v-if="invoice.tax_percentage"
                      class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-violet-50 text-violet-600 text-xs font-semibold rounded-md ring-1 ring-violet-200">
                      PPN {{ invoice.tax_percentage }}%
                    </span>
                    <span v-else class="inline-flex items-center mt-1 px-1.5 py-0.5 text-xs text-gray-300">
                      No tax
                    </span>
                  </template>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                      class="text-xs px-3 py-1.5 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors font-medium whitespace-nowrap">
                      Lihat
                    </Link>
                    <!-- Paid / Unpaid -->
                    <button v-if="invoice.payment_status !== 'paid' && invoice.document_status === 'verified'"
                      @click="changeStatus(invoice, 'paid')"
                      class="inline-flex items-center gap-1 text-xs px-3 py-1.5 border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors font-semibold whitespace-nowrap">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                      Paid
                    </button>
                    <template v-if="invoice.payment_status !== 'paid'">
                      <button v-if="invoice.document_status === 'verified'"
                        @click="openEmailModal(invoice)"
                        class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-emerald-200 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Email
                      </button>
                      <span v-else
                        class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-gray-100 text-gray-300 rounded-lg cursor-not-allowed whitespace-nowrap"
                        title="Antre kirim dulu untuk kirim email">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Email
                      </span>
                    </template>
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

      <!-- DIVIDER -->
      <div class="flex items-center gap-3 pt-2">
        <div class="flex-1 h-px bg-gray-200"/>
        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Invoice Lainnya</span>
        <div class="flex-1 h-px bg-gray-200"/>
      </div>

      <!-- OTHER SECTION -->
      <div v-if="filteredOther.length === 0" class="text-center py-6">
        <p class="text-sm text-gray-300">{{ search ? `Tidak ditemukan "${search}"` : 'Tidak ada invoice lainnya.' }}</p>
      </div>

      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="px-4 py-3 w-8"/>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-3 py-3">Status</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Invoice # / Project</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Recipient</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Issue / Due Date</th>
              <th class="text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
              <th class="px-5 py-3"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="invoice in filteredOther" :key="invoice.id"
              :class="invoice.document_status === 'verified' ? 'bg-amber-50/60' : isPastDue(invoice) ? 'bg-red-50/60 hover:bg-red-50' : 'hover:bg-gray-50/50'"
              class="transition-colors group">
              <!-- Checkbox -->
              <td class="px-4 py-4">
                <input type="checkbox" :checked="invoice.document_status === 'verified'"
                  @change="toggleMark(invoice)"
                  class="w-4 h-4 rounded border-gray-300 text-amber-500 cursor-pointer focus:ring-amber-400"/>
              </td>
              <td class="px-3 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium" :class="statusClass(computedStatus(invoice))">
                  {{ statusLabel(computedStatus(invoice)) }}
                </span>
              </td>
              <td class="px-4 py-4">
                <p class="font-mono text-sm font-semibold text-gray-800 whitespace-nowrap">{{ invoice.invoice_number }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ invoice.project_category?.name ?? '-' }}</p>
                <span v-if="invoice.interval_months"
                  class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-500 text-xs font-medium rounded-md">
                  ↻ {{ invoice.interval_months }} bln
                </span>
                <span v-if="invoice.is_chain_head"
                  class="mt-1 ml-1 inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded-md"
                  :class="chainBadgeClass(invoice.chain_type)">
                  {{ invoice.chain_type }}
                </span>
              </td>
              <td class="px-4 py-4">
                <p class="text-sm font-medium text-gray-800">{{ invoice.client?.company_name ?? '-' }}</p>
              </td>
              <td class="px-4 py-4">
                <p class="text-sm text-gray-700">{{ fmtDate(invoice.chain_issue_date ?? invoice.issue_date) }}</p>
                <p class="text-xs mt-0.5 font-medium" :class="isPastDue(invoice) ? 'text-red-500' : 'text-gray-400'">
                  → {{ fmtDate(invoice.chain_due_date ?? invoice.due_date) }}
                </p>
                <span v-if="isPastDue(invoice)"
                  class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded-md">
                  <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> lewat {{ daysPastDue(invoice) }} hari
                </span>
              </td>
              <td class="px-4 py-4 text-right">
                <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">{{ fmtCurrency(invoice.is_chain_head ? invoice.chain_total : invoice.items_sum_amount) }}</p>
                <span v-if="invoice.is_chain_head"
                  class="inline-flex items-center mt-1 px-1.5 py-0.5 text-xs font-semibold rounded-md"
                  :class="chainBadgeClass(invoice.chain_type)">
                  total {{ invoice.chain_type }}
                </span>
                <template v-else>
                  <span v-if="invoice.tax_percentage"
                    class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 bg-violet-50 text-violet-600 text-xs font-semibold rounded-md ring-1 ring-violet-200">
                    PPN {{ invoice.tax_percentage }}%
                  </span>
                  <span v-else class="inline-flex items-center mt-1 px-1.5 py-0.5 text-xs text-gray-300">
                    No tax
                  </span>
                </template>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                    class="text-xs px-3 py-1.5 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors font-medium whitespace-nowrap">
                    Lihat
                  </Link>
                  <!-- Paid -->
                  <button v-if="invoice.payment_status !== 'paid' && invoice.document_status === 'verified'"
                    @click="changeStatus(invoice, 'paid')"
                    class="inline-flex items-center gap-1 text-xs px-3 py-1.5 border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors font-semibold whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Paid
                  </button>
                  <button v-if="invoice.document_status === 'verified'"
                    @click="openEmailModal(invoice)"
                    class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-emerald-200 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Email
                  </button>
                  <span v-else
                    class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-gray-100 text-gray-300 rounded-lg cursor-not-allowed whitespace-nowrap"
                    title="Antre kirim dulu untuk kirim email">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Email
                  </span>
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

  <SendEmailModal
    :show="emailModal"
    :invoice="emailModalInvoice"
    :client-emails="emailModalEmails"
    :email-templates="emailTemplates ?? []"
    @close="emailModal = false"
  />

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SendEmailModal from '@/Components/SendEmailModal.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  priorityInvoices: Array,
  otherInvoices:    Array,
  nextMonthLabel:   String,
  filters:          Object,
  emailTemplates:   Array,
});

const emailModal        = ref(false);
const emailModalInvoice = ref(null);
const emailModalEmails  = ref([]);

function openEmailModal(invoice) {
  emailModalInvoice.value = invoice;
  emailModalEmails.value  = invoice.client?.emails?.map(e => e.email) ?? [];
  emailModal.value = true;
}

const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

const localMonth = ref(props.filters.month);
const localYear  = ref(props.filters.year);
const search     = ref('');

const matchSearch = (inv) => !search.value ||
  inv.invoice_number.toLowerCase().includes(search.value.toLowerCase())

const filteredPriority = computed(() => props.priorityInvoices.filter(matchSearch))
const filteredOther    = computed(() => props.otherInvoices.filter(matchSearch))

function applyFilters() {
  router.get(route('invoices.schedule'), {
    month: localMonth.value,
    year:  localYear.value,
  }, { preserveState: true, replace: true });
}

function computedStatus(inv) {
  if (!inv) return 'draft'
  if (inv.document_status === 'frozen')  return 'frozen'
  if (inv.document_status === 'carried') return 'carried'
  if (inv.payment_status  === 'paid')    return 'paid'
  if (inv.document_status === 'draft')   return 'draft'
  return inv.send_status !== 'unsent' ? 'sent' : 'verified'
}

const statusClass = (s) => ({
  draft:    'bg-gray-100 text-gray-500',
  sent:     'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  verified: 'bg-amber-50 text-amber-600 ring-1 ring-amber-200',
  paid:     'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  frozen:   'bg-sky-100 text-sky-600 ring-1 ring-sky-200',
  carried:  'bg-orange-50 text-orange-600 ring-1 ring-orange-200',
}[s] ?? 'bg-gray-100 text-gray-500')

const statusLabel = (s) => ({
  draft:    'Draft',
  sent:     'Sent',
  verified: 'Terverifikasi',
  paid:     'Paid',
  frozen:   'Frozen',
  carried:  'Carried',
}[s] ?? s)

const isPastDue = (inv) => inv.payment_status !== 'paid' && inv.document_status !== 'frozen' && inv.document_status !== 'carried' && new Date(inv.chain_due_date ?? inv.due_date) < new Date(new Date().toDateString())

const daysPastDue = (inv) => Math.floor((new Date(new Date().toDateString()) - new Date(inv.chain_due_date ?? inv.due_date)) / 86400000);

const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtDateShort = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: '2-digit' })
  : '-';

const SEND_ORDER  = { unsent: 0, send1: 1, send2: 2, send3: 3, send4: 4, send5: 5 }
const SEND_NEXT   = { unsent: 'send1', send1: 'send2', send2: 'send3', send3: 'send4', send4: 'send5' }
const SEND_OFFSET = { send1: 0, send2: 14, send3: 21, send4: 28, send5: 35 }
const stageReached  = (current, target) => (SEND_ORDER[current] ?? 0) >= (SEND_ORDER[target] ?? 99)
const chainBadgeClass = (type) => ({
  carry:      'bg-orange-50 text-orange-600',
  prepay:     'bg-teal-50 text-teal-600',
  reaktivasi: 'bg-violet-50 text-violet-600',
}[type] ?? 'bg-gray-100 text-gray-500')
const nextSendInfo  = (inv) => {
  if (inv.document_status !== 'verified' || inv.payment_status !== 'unpaid') return null
  const nextStage = SEND_NEXT[inv.send_status]
  if (!nextStage) return null
  const d = new Date(inv.issue_date)
  d.setDate(d.getDate() + SEND_OFFSET[nextStage])
  return { stage: nextStage, date: d, soon: d <= new Date(new Date().toDateString()) }
}

const fmtCurrency = (v) => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

function toggleMark(invoice) {
  router.patch(route('invoices.mark', invoice.id), {}, { preserveScroll: true });
}

function changeStatus(invoice, status) {
  const payload = status === 'paid' ? { payment_status: 'paid' } : { document_status: status }
  router.patch(route('invoices.status', invoice.id), payload, { preserveScroll: true })
}

function deleteInvoice(invoice) {
  if (!confirm(`Hapus invoice ${invoice.invoice_number}?`)) return;
  router.delete(route('invoices.destroy', invoice.id), { preserveScroll: true });
}
</script>
