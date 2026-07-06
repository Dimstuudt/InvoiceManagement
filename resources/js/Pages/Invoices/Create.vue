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
            <p class="text-sm text-gray-400 mt-0.5">Nomor invoice otomatis · urutan dapat diubah jika perlu</p>
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

            <!-- Nomor Invoice Preview -->
            <div class="border-t border-gray-100 pt-5">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Nomor Invoice</p>

              <div v-if="!readyPreview" class="text-sm text-gray-400 italic">
                Pilih Project Category dan Issue Date untuk pratinjau nomor.
              </div>

              <div v-else class="space-y-2">
                <!-- Segmented number display -->
                <div class="flex items-center flex-wrap gap-1.5">
                  <div class="flex flex-col items-center gap-1">
                    <input
                      v-model.number="seqInput"
                      type="number" min="1" max="9999"
                      class="w-20 px-2 py-2 rounded-xl border-2 text-center font-mono text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                      :class="numState.available === false
                        ? 'border-red-400 bg-red-50 text-red-700'
                        : 'border-indigo-300 bg-indigo-50 text-indigo-800'"
                    />
                    <span class="text-xs text-gray-400">urutan</span>
                  </div>
                  <span class="text-gray-300 text-xl pb-4">/</span>
                  <div class="flex flex-col items-center gap-1">
                    <span class="px-3 py-2 bg-gray-100 rounded-xl text-gray-700 text-sm font-semibold font-mono">{{ catCode }}</span>
                    <span class="text-xs text-gray-400">kategori</span>
                  </div>
                  <span class="text-gray-300 text-xl pb-4">/</span>
                  <span class="px-3 py-2 bg-gray-100 rounded-xl text-gray-500 text-sm font-mono mb-5">INV</span>
                  <span class="text-gray-300 text-xl pb-4">/</span>
                  <span class="px-3 py-2 bg-gray-100 rounded-xl text-gray-500 text-sm font-mono mb-5">MVC</span>
                  <span class="text-gray-300 text-xl pb-4">/</span>
                  <div class="flex flex-col items-center gap-1">
                    <span class="px-3 py-2 bg-gray-100 rounded-xl text-gray-500 text-sm font-mono">{{ issueMonth }}</span>
                    <span class="text-xs text-gray-400">bulan</span>
                  </div>
                  <span class="text-gray-300 text-xl pb-4">/</span>
                  <div class="flex flex-col items-center gap-1">
                    <span class="px-3 py-2 bg-gray-100 rounded-xl text-gray-500 text-sm font-mono">{{ issueYear }}</span>
                    <span class="text-xs text-gray-400">tahun</span>
                  </div>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-1.5 text-xs min-h-4">
                  <template v-if="numState.loading">
                    <svg class="w-3 h-3 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="text-gray-400">Mengecek ketersediaan...</span>
                  </template>
                  <template v-else-if="numState.available === true">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-emerald-600 font-medium">{{ numState.number }} — tersedia</span>
                  </template>
                  <template v-else-if="numState.available === false">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="text-red-600 font-medium">{{ numState.number }} — sudah digunakan tahun {{ issueYear }}</span>
                  </template>
                </div>

                <p v-if="form.errors.invoice_number" class="err">{{ form.errors.invoice_number }}</p>
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

            <!-- Email Template -->
            <div v-if="emailTemplates?.length" class="border-t border-gray-100 pt-5">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Template Email</p>
              <select v-model="form.email_template_id" class="field">
                <option value="">— Tanpa Template —</option>
                <option v-for="t in emailTemplates" :key="t.id" :value="t.id">
                  {{ t.name }}{{ t.is_default ? ' (Default)' : '' }}
                </option>
              </select>
              <p class="text-xs text-gray-400 mt-1.5">Template akan otomatis digunakan saat kirim email invoice ini.</p>
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

            <!-- Interval Layanan -->
            <div class="border-t border-gray-100 pt-5">
              <div class="flex items-start gap-3">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Interval Layanan
                    <span class="text-gray-400 font-normal text-xs ml-1">· opsional</span>
                  </label>
                  <select v-model="form.interval_months" class="field">
                    <option :value="null">— One-time (tidak berulang) —</option>
                    <template v-if="form.invoice_type === 'yearly'">
                      <option v-for="n in [1,2,3,4]" :key="n" :value="n * 12">{{ n }} tahun</option>
                    </template>
                    <template v-else>
                      <option v-for="n in [1,2,3,4,6]" :key="n" :value="n">{{ n }} bulan</option>
                    </template>
                  </select>
                </div>
                <div v-if="form.interval_months" class="mt-7 px-3 py-2 bg-indigo-50 border border-indigo-100 rounded-xl shrink-0">
                  <p class="text-xs text-indigo-600 font-medium whitespace-nowrap">
                    Perpanjang otomatis tiap
                    {{ form.invoice_type === 'yearly' ? (form.interval_months / 12) + ' tahun' : form.interval_months + ' bulan' }}
                  </p>
                </div>
              </div>
              <p v-if="form.interval_months" class="text-xs text-gray-400 mt-1.5">
                Saat invoice lunas, sistem akan otomatis membuat invoice perpanjangan sebagai draft.
              </p>
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
            <Link :href="route('invoices.index')"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:border-gray-300 rounded-xl transition-colors bg-white">
              Batal
            </Link>
            <button type="submit"
              :disabled="form.processing || numState.loading || (readyPreview && numState.available === false)"
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
import { watch, computed, ref } from 'vue';

const props = defineProps({
  clients: Array, projectCategories: Array,
  documentIssuers: Array, bankAccounts: Array,
  signatures: Array, emailTemplates: Array, fromInvoice: Object,
});

const prefill       = props.fromInvoice ?? {};
const queryClientId = new URLSearchParams(window.location.search).get('client_id') ?? '';

const form = useForm({
  client_id:           prefill.client_id           ?? queryClientId,
  project_category_id: prefill.project_category_id ?? '',
  document_issuer_id:  prefill.document_issuer_id  ?? '',
  bank_account_id:     prefill.bank_account_id     ?? '',
  signature_id:        prefill.signature_id         ?? '',
  email_template_id:   prefill.email_template_id    ?? (props.emailTemplates?.find(t => t.is_default)?.id ?? ''),
  interval_months:     prefill.interval_months      ?? null,
  invoice_type:        prefill.invoice_type         ?? 'monthly',
  with_signature:      prefill.with_signature       ?? false,
  spk_number:          '',
  status:              'draft',
  issue_date:          '',
  due_date:            '',
  invoice_number:      '',
});

// ── Invoice number preview ──────────────────────────────────────
const seqInput   = ref(null);
const numState   = ref({ number: '', seq: null, available: null, loading: false });
let   debounceTimer = null;

const romanMonths  = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
const selectedCat  = computed(() => props.projectCategories.find(c => c.id == form.project_category_id));
const catCode      = computed(() => selectedCat.value?.code ?? '???');
const issueMonth   = computed(() => form.issue_date ? romanMonths[new Date(form.issue_date).getMonth()] : '?');
const issueYear    = computed(() => form.issue_date ? new Date(form.issue_date).getFullYear() : '????');
const readyPreview = computed(() => !!form.project_category_id && !!form.issue_date);

async function fetchPreview(customSeq = null) {
  if (!readyPreview.value) {
    numState.value = { number: '', seq: null, available: null, loading: false };
    form.invoice_number = '';
    return;
  }
  numState.value = { ...numState.value, loading: true };
  try {
    const params = new URLSearchParams({
      project_category_id: form.project_category_id,
      issue_date:          form.issue_date,
      invoice_type:        form.invoice_type,
    });
    if (customSeq !== null) params.set('seq', customSeq);
    const res  = await fetch(`/invoices/number-preview?${params}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();
    numState.value = { ...data, loading: false };
    if (customSeq === null) seqInput.value = data.seq;
    form.invoice_number = data.number;
  } catch {
    numState.value = { ...numState.value, loading: false };
  }
}

watch([() => form.project_category_id, () => form.issue_date], () => {
  clearTimeout(debounceTimer);
  fetchPreview();
});

watch(seqInput, (val) => {
  if (val === numState.value?.seq) return;
  clearTimeout(debounceTimer);
  if (val && val >= 1) {
    debounceTimer = setTimeout(() => fetchPreview(val), 400);
  }
});
// ───────────────────────────────────────────────────────────────

watch(() => form.issue_date, (val) => {
  if (!val) return;
  const d = new Date(val);
  d.setDate(d.getDate() + 14);
  form.due_date = d.toISOString().slice(0, 10);
});

watch(() => form.invoice_type, () => { form.interval_months = null; });

function submit() {
  if (readyPreview.value && numState.value.available === false) return;
  form.post(route('invoices.store'));
}
</script>

<style scoped>
@reference "tailwindcss";
.field      { @apply w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition; }
.field-error{ @apply border-red-300 bg-red-50; }
.err        { @apply mt-1.5 text-xs text-red-500; }
</style>
