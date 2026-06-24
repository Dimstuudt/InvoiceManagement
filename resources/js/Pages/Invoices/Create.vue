<template>
  <AppLayout title="Buat Invoice">
    <div class="max-w-2xl mx-auto space-y-4">

      <Link :href="route('invoices.index')"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </Link>

      <div v-if="fromInvoice"
        class="flex items-center gap-3 px-4 py-3 bg-indigo-50 border border-indigo-100 rounded-xl text-sm text-indigo-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Perpanjang dari <span class="font-mono font-semibold ml-1">{{ fromInvoice.invoice_number }}</span>
      </div>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Buat Invoice Baru</h2>
            <p class="text-sm text-gray-400 mt-0.5">Nomor invoice digenerate otomatis setelah disimpan</p>
          </div>

          <div class="px-6 py-6 space-y-5">

            <!-- Client + Project -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipient <span class="text-red-400">*</span></label>
                <select v-model="form.client_id" class="field" :class="form.errors.client_id && 'field-error'">
                  <option value="">— Pilih Client —</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                </select>
                <p v-if="form.errors.client_id" class="err">{{ form.errors.client_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Project Category <span class="text-red-400">*</span></label>
                <select v-model="form.project_category_id" class="field" :class="form.errors.project_category_id && 'field-error'">
                  <option value="">— Pilih Kategori —</option>
                  <option v-for="pc in projectCategories" :key="pc.id" :value="pc.id">{{ pc.name }} ({{ pc.code }})</option>
                </select>
                <p v-if="form.errors.project_category_id" class="err">{{ form.errors.project_category_id }}</p>
              </div>
            </div>

            <!-- Issue + Due Date -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date <span class="text-red-400">*</span></label>
                <input v-model="form.issue_date" type="date" class="field" :class="form.errors.issue_date && 'field-error'" />
                <p v-if="form.errors.issue_date" class="err">{{ form.errors.issue_date }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Due Date <span class="text-red-400">*</span>
                  <span class="text-gray-400 font-normal ml-1 text-xs">· otomatis +14 hari</span>
                </label>
                <input v-model="form.due_date" type="date" class="field" :class="form.errors.due_date && 'field-error'" />
                <p v-if="form.errors.due_date" class="err">{{ form.errors.due_date }}</p>
              </div>
            </div>

            <!-- SPK + Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. SPK <span class="text-gray-400 font-normal text-xs ml-1">· opsional</span></label>
                <input v-model="form.spk_number" type="text" placeholder="e.g. SPK/001/2026" class="field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select v-model="form.status" class="field">
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="paid">Paid</option>
                  <option value="unpaid">Unpaid</option>
                </select>
              </div>
            </div>

            <!-- Attention + Notes -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Attention <span class="text-gray-400 font-normal text-xs ml-1">· opsional</span></label>
                <input v-model="form.attention" type="text" placeholder="Kepada Yth. ..." class="field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes <span class="text-gray-400 font-normal text-xs ml-1">· opsional</span></label>
                <input v-model="form.notes" type="text" placeholder="Catatan tambahan" class="field" />
              </div>
            </div>

            <!-- Dokumen -->
            <div class="border-t border-gray-100 pt-5">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Kelengkapan Dokumen</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Document Issuer <span class="text-red-400">*</span></label>
                  <select v-model="form.document_issuer_id" class="field" :class="form.errors.document_issuer_id && 'field-error'">
                    <option value="">— Pilih Issuer —</option>
                    <option v-for="i in documentIssuers" :key="i.id" :value="i.id">{{ i.name }}</option>
                  </select>
                  <p v-if="form.errors.document_issuer_id" class="err">{{ form.errors.document_issuer_id }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account <span class="text-red-400">*</span></label>
                  <select v-model="form.bank_account_id" class="field" :class="form.errors.bank_account_id && 'field-error'">
                    <option value="">— Pilih Bank —</option>
                    <option v-for="b in bankAccounts" :key="b.id" :value="b.id">{{ b.bank_name }} · {{ b.name }}</option>
                  </select>
                  <p v-if="form.errors.bank_account_id" class="err">{{ form.errors.bank_account_id }}</p>
                </div>
              </div>
            </div>

            <!-- Signature -->
            <div class="border-t border-gray-100 pt-5">
              <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanda Tangan</p>
                <button type="button" @click="form.with_signature = !form.with_signature"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                  :class="form.with_signature ? 'bg-indigo-600' : 'bg-gray-200'">
                  <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                    :class="form.with_signature ? 'translate-x-6' : 'translate-x-1'"/>
                </button>
              </div>
              <div v-if="form.with_signature">
                <select v-model="form.signature_id" class="field">
                  <option value="">— Pilih Signature —</option>
                  <option v-for="s in signatures" :key="s.id" :value="s.id">{{ s.name }} · {{ s.position }}</option>
                </select>
              </div>
            </div>

            <!-- Items -->
            <div class="border-t border-gray-100 pt-5">
              <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Item Invoice</p>
                <button type="button" @click="addItem"
                  class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Tambah Item
                </button>
              </div>
              <p v-if="form.errors.items" class="mb-3 text-xs text-red-500">{{ form.errors.items }}</p>

              <div class="space-y-2">
                <div class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-400 px-1">
                  <div class="col-span-8">Deskripsi</div>
                  <div class="col-span-4">Harga</div>
                </div>
                <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 items-center">
                  <div class="col-span-8">
                    <input v-model="item.description" type="text" placeholder="Deskripsi pekerjaan"
                      class="field" />
                  </div>
                  <div class="col-span-3">
                    <input v-model="item.amount" type="number" min="0" placeholder="0"
                      class="field font-mono" />
                  </div>
                  <div class="col-span-1 flex justify-center">
                    <button type="button" @click="removeItem(i)" :disabled="form.items.length === 1"
                      class="text-gray-300 hover:text-red-400 disabled:opacity-30 transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <div class="mt-4 flex justify-end border-t border-gray-100 pt-4">
                <div class="text-right">
                  <p class="text-xs text-gray-400 mb-1">Total</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(total) }}</p>
                </div>
              </div>
            </div>

          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <Link :href="route('invoices.index')"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:border-gray-300 rounded-xl transition-colors bg-white">
              Batal
            </Link>
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Invoice' }}
            </button>
          </div>

        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
  clients: Array, projectCategories: Array,
  documentIssuers: Array, bankAccounts: Array,
  signatures: Array, fromInvoice: Object,
});

const prefill      = props.fromInvoice ?? {};
const queryClientId = new URLSearchParams(window.location.search).get('client_id') ?? '';

const form = useForm({
  client_id:           prefill.client_id           ?? queryClientId,
  project_category_id: prefill.project_category_id ?? '',
  document_issuer_id:  prefill.document_issuer_id  ?? '',
  bank_account_id:     prefill.bank_account_id     ?? '',
  signature_id:        prefill.signature_id         ?? '',
  with_signature:      prefill.with_signature       ?? false,
  spk_number:          '',
  status:              'draft',
  issue_date:          '',
  due_date:            '',
  attention:           prefill.attention ?? '',
  notes:               prefill.notes     ?? '',
  items: prefill.items?.length
    ? prefill.items.map(i => ({ description: i.description, amount: i.amount }))
    : [{ description: '', amount: '' }],
});

watch(() => form.issue_date, (val) => {
  if (!val) return;
  const d = new Date(val);
  d.setDate(d.getDate() + 14);
  form.due_date = d.toISOString().slice(0, 10);
});

const total = computed(() =>
  form.items.reduce((s, i) => s + (parseFloat(i.amount) || 0), 0)
);

const formatCurrency = v =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);

function addItem()     { form.items.push({ description: '', amount: '' }); }
function removeItem(i) { if (form.items.length > 1) form.items.splice(i, 1); }
function submit()      { form.post(route('invoices.store')); }
</script>

<style scoped>
@reference "tailwindcss";
.field      { @apply w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition; }
.field-error{ @apply border-red-300 bg-red-50; }
.err        { @apply mt-1.5 text-xs text-red-500; }
</style>
