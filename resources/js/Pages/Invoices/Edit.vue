<template>
  <AppLayout title="Edit Invoice">
    <div class="max-w-2xl mx-auto space-y-4">

      <Link :href="route('invoices.show', invoice.id)"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Invoice
      </Link>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Edit Invoice</h2>
            <p class="text-sm font-mono text-indigo-500 mt-0.5">{{ invoice.invoice_number }}</p>
          </div>

          <div class="px-6 py-6 space-y-5">

            <!-- Client + Project -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipient <span class="text-red-400">*</span></label>
                <select v-model="form.client_id" class="field" :class="form.errors.client_id && 'field-error'">
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                </select>
                <p v-if="form.errors.client_id" class="err">{{ form.errors.client_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Project Category <span class="text-red-400">*</span></label>
                <select v-model="form.project_category_id" class="field" :class="form.errors.project_category_id && 'field-error'">
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
                  <span class="text-gray-400 font-normal text-xs ml-1">· otomatis +14 hari</span>
                </label>
                <input v-model="form.due_date" type="date" class="field" :class="form.errors.due_date && 'field-error'" />
                <p v-if="form.errors.due_date" class="err">{{ form.errors.due_date }}</p>
              </div>
            </div>

            <!-- SPK + Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No. SPK <span class="text-gray-400 font-normal text-xs ml-1">· opsional</span></label>
                <input v-model="form.spk_number" type="text" class="field" />
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


            <!-- Tipe Invoice -->
            <div class="border-t border-gray-100 pt-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Invoice <span class="text-red-400">*</span></label>
              <div class="flex gap-3">
                <button type="button" @click="form.invoice_type = 'monthly'"
                  class="flex-1 flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 transition-colors text-left"
                  :class="form.invoice_type === 'monthly' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                    :class="form.invoice_type === 'monthly' ? 'bg-indigo-100' : 'bg-gray-100'">
                    <svg class="w-4 h-4" :class="form.invoice_type === 'monthly' ? 'text-indigo-600' : 'text-gray-400'"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold" :class="form.invoice_type === 'monthly' ? 'text-indigo-700' : 'text-gray-700'">Bulanan</p>
                    <p class="text-xs text-gray-400 mt-0.5">Format: 001/KODE/INV/MVC/VII/2026</p>
                  </div>
                </button>
                <button type="button" @click="form.invoice_type = 'yearly'"
                  class="flex-1 flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 transition-colors text-left"
                  :class="form.invoice_type === 'yearly' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                    :class="form.invoice_type === 'yearly' ? 'bg-amber-100' : 'bg-gray-100'">
                    <svg class="w-4 h-4" :class="form.invoice_type === 'yearly' ? 'text-amber-600' : 'text-gray-400'"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold" :class="form.invoice_type === 'yearly' ? 'text-amber-700' : 'text-gray-700'">Tahunan</p>
                    <p class="text-xs text-gray-400 mt-0.5">Format: 001/KODE/INV/MVC/VII/2026</p>
                  </div>
                </button>
              </div>
            </div>

            <!-- Dokumen -->
            <div class="border-t border-gray-100 pt-5">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Kelengkapan Dokumen</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Document Issuer <span class="text-red-400">*</span></label>
                  <select v-model="form.document_issuer_id" class="field" :class="form.errors.document_issuer_id && 'field-error'">
                    <option v-for="i in documentIssuers" :key="i.id" :value="i.id">{{ i.name }}</option>
                  </select>
                  <p v-if="form.errors.document_issuer_id" class="err">{{ form.errors.document_issuer_id }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bank Account <span class="text-red-400">*</span></label>
                  <select v-model="form.bank_account_id" class="field" :class="form.errors.bank_account_id && 'field-error'">
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

          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <Link :href="route('invoices.show', invoice.id)"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:border-gray-300 rounded-xl transition-colors bg-white">
              Batal
            </Link>
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
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
import { watch } from 'vue';

const props = defineProps({
  invoice: Object, clients: Array, projectCategories: Array,
  documentIssuers: Array, bankAccounts: Array, signatures: Array,
});

const form = useForm({
  client_id:           props.invoice.client_id,
  project_category_id: props.invoice.project_category_id,
  document_issuer_id:  props.invoice.document_issuer_id,
  bank_account_id:     props.invoice.bank_account_id,
  signature_id:        props.invoice.signature_id  ?? '',
  with_signature:      props.invoice.with_signature,
  spk_number:          props.invoice.spk_number    ?? '',
  invoice_type:        props.invoice.invoice_type ?? 'monthly',
  status:              props.invoice.status,
  issue_date:          props.invoice.issue_date,
  due_date:            props.invoice.due_date,
});

watch(() => form.issue_date, (val, old) => {
  if (!val || !old) return;
  const d = new Date(val);
  d.setDate(d.getDate() + 14);
  form.due_date = d.toISOString().slice(0, 10);
});

function submit() { form.put(route('invoices.update', props.invoice.id)); }
</script>

<style scoped>
@reference "tailwindcss";
.field      { @apply w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition; }
.field-error{ @apply border-red-300 bg-red-50; }
.err        { @apply mt-1.5 text-xs text-red-500; }
</style>
