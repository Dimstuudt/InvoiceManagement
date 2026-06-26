<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"/>

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
              <p class="text-sm font-semibold text-gray-800">Kirim Invoice via Email</p>
              <p class="text-xs text-gray-400 font-mono mt-0.5">{{ invoice?.invoice_number }}</p>
            </div>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto p-5 space-y-4">

            <!-- Penerima -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Penerima</label>
              <div v-if="clientEmails.length > 0" class="space-y-1.5">
                <label v-for="email in clientEmails" :key="email"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-colors"
                  :class="selectedEmails.includes(email) ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                  <input type="checkbox" :value="email" v-model="selectedEmails"
                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300"/>
                  <span class="text-sm text-gray-700">{{ email }}</span>
                </label>
              </div>
              <p v-else class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
                Client belum punya email terdaftar. Tambahkan email di data client terlebih dahulu.
              </p>
              <p v-if="errors.emails" class="mt-1.5 text-xs text-red-500">{{ errors.emails }}</p>
            </div>

            <!-- Pilih Template (kalau belum assign) -->
            <div v-if="!invoice?.email_template_id && emailTemplates.length > 0">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Template Email</label>
              <div class="space-y-1.5">
                <label v-for="tpl in emailTemplates" :key="tpl.id"
                  class="flex items-center gap-3 px-3 py-2.5 rounded-xl border cursor-pointer transition-colors"
                  :class="selectedTemplateId === tpl.id ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                  <input type="radio" :value="tpl.id" v-model="selectedTemplateId"
                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"/>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700">{{ tpl.name }}</p>
                  </div>
                  <span v-if="tpl.is_default"
                    class="text-xs px-2 py-0.5 bg-indigo-100 text-indigo-600 rounded-md font-medium shrink-0">
                    Default
                  </span>
                </label>
              </div>
              <p v-if="!selectedTemplateId" class="text-xs text-gray-400 mt-1.5">Pilih template untuk mengisi subject & isi email.</p>
            </div>

            <!-- Subject -->
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Subject</label>
              <input v-model="form.subject" type="text" placeholder="Subject email..."
                class="w-full px-3 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                :class="errors.subject ? 'border-red-300 bg-red-50' : 'border-gray-200'"/>
              <p v-if="errors.subject" class="mt-1 text-xs text-red-500">{{ errors.subject }}</p>
            </div>

            <!-- Body -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Isi Email</label>
                <span class="text-xs text-gray-400">Dapat diedit sebelum dikirim</span>
              </div>
              <textarea v-model="form.body" rows="9" placeholder="Isi email..."
                class="w-full px-3 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none leading-relaxed"
                :class="errors.body ? 'border-red-300 bg-red-50' : 'border-gray-200'"/>
              <p v-if="errors.body" class="mt-1 text-xs text-red-500">{{ errors.body }}</p>
            </div>

            <!-- PDF attachment info -->
            <div class="flex items-center gap-2.5 px-3 py-2.5 bg-gray-50 rounded-xl border border-gray-100">
              <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
              </svg>
              <p class="text-xs text-gray-500">
                Lampiran: <span class="font-mono font-semibold text-gray-700">{{ pdfFilename }}</span>
              </p>
            </div>

          </div>

          <!-- Footer -->
          <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0 bg-gray-50/60 rounded-b-2xl">
            <p class="text-xs text-gray-400">Status → <span class="font-semibold text-blue-500">Sent</span> setelah terkirim</p>
            <div class="flex items-center gap-2">
              <button @click="$emit('close')"
                class="px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                Batal
              </button>
              <button @click="submit" :disabled="sending || selectedEmails.length === 0 || clientEmails.length === 0"
                class="flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
                <svg v-if="sending" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                {{ sending ? 'Mengirim...' : `Kirim ke ${selectedEmails.length} email` }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show:           { type: Boolean, default: false },
  invoice:        { type: Object,  default: null },
  clientEmails:   { type: Array,   default: () => [] },
  emailTemplates: { type: Array,   default: () => [] },
});

const emit = defineEmits(['close']);

const sending           = ref(false);
const errors            = ref({});
const selectedEmails    = ref([]);
const selectedTemplateId = ref(null);

const form = ref({ subject: '', body: '' });

const pdfFilename = computed(() =>
  ((props.invoice?.invoice_number ?? 'invoice').replace(/\//g, '-')) + '.pdf'
);

// Variabel yang tersedia untuk substitusi
function renderTemplate(text) {
  if (!text || !props.invoice) return text;
  const inv = props.invoice;
  const fmtDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
  const fmtRp   = (v) => v != null ? 'Rp ' + new Intl.NumberFormat('id-ID').format(v) : '';

  const vars = {
    '{{invoice_number}}':      inv.invoice_number ?? '',
    '{{issue_date}}':          fmtDate(inv.issue_date),
    '{{due_date}}':            fmtDate(inv.due_date),
    '{{amount}}':              fmtRp(inv.total ?? inv.items_sum_amount),
    '{{project_name}}':        inv.project_category?.name ?? '',
    '{{client_name}}':         inv.client?.company_name ?? '',
    '{{director}}':            inv.client?.director ?? '',
    '{{pic}}':                 inv.client?.pic ?? '',
    '{{client_city}}':         inv.client?.city ?? '',
    '{{issuer_name}}':         inv.document_issuer?.name ?? '',
    '{{bank_name}}':           inv.bank_account?.bank_name ?? '',
    '{{bank_account_number}}': inv.bank_account?.account_number ?? '',
    '{{bank_holder}}':         inv.bank_account?.name ?? '',
  };

  return Object.entries(vars).reduce((t, [k, v]) => t.replaceAll(k, v), text);
}

function applyTemplate(tpl) {
  if (!tpl) return;
  form.value.subject = renderTemplate(tpl.subject);
  form.value.body    = renderTemplate(tpl.body);
}

// Saat modal dibuka, init state
watch(() => props.show, (val) => {
  if (!val) return;
  errors.value       = {};
  selectedEmails.value = [...props.clientEmails];

  // Cari template yang di-assign ke invoice atau template default
  const assigned = props.invoice?.email_template_id
    ? props.emailTemplates.find(t => t.id === props.invoice.email_template_id)
    : null;
  const defaultTpl = props.emailTemplates.find(t => t.is_default);
  const tpl = assigned ?? defaultTpl ?? null;

  if (tpl) {
    selectedTemplateId.value = tpl.id;
    applyTemplate(tpl);
  } else {
    selectedTemplateId.value = null;
    form.value = { subject: '', body: '' };
  }
});

// Saat template dipilih (dari radio), isi subject & body
watch(selectedTemplateId, (id) => {
  const tpl = props.emailTemplates.find(t => t.id === id);
  if (tpl) applyTemplate(tpl);
});

function submit() {
  if (selectedEmails.value.length === 0) return;
  errors.value  = {};
  sending.value = true;

  router.post(route('invoices.send-email', props.invoice.id), {
    emails:  selectedEmails.value,
    subject: form.value.subject,
    body:    form.value.body,
  }, {
    onSuccess: () => { emit('close'); },
    onError:   (e) => { errors.value = e; },
    onFinish:  () => { sending.value = false; },
  });
}
</script>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to       { opacity: 0; }
</style>
