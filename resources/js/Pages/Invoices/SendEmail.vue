<template>
  <AppLayout :title="`Kirim Invoice ${invoice.invoice_number}`">
    <div class="max-w-2xl mx-auto space-y-4">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('invoices.show', invoice.id)"
          class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Kirim via Email</h2>
          <p class="text-sm text-gray-400 font-mono mt-0.5">{{ invoice.invoice_number }}</p>
        </div>
      </div>

      <!-- Invoice summary card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-800">{{ invoice.client?.company_name ?? '-' }}</p>
          <p class="text-xs text-gray-400 mt-0.5">{{ invoice.project_category?.name ?? '-' }} &bull; Due {{ fmtDate(invoice.due_date) }}</p>
        </div>
        <div class="text-right shrink-0">
          <p class="text-sm font-bold text-gray-900">{{ fmtCurrency(invoice.total) }}</p>
          <span class="text-xs px-2 py-0.5 rounded-lg font-medium" :class="statusClass">
            {{ statusLabel }}
          </span>
        </div>
      </div>

      <!-- Attachment info -->
      <div class="bg-gray-50 border border-gray-100 rounded-2xl px-5 py-3 flex items-center gap-3">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <p class="text-xs text-gray-500">
          PDF invoice di-generate otomatis dan dilampirkan saat dikirim:
          <span class="font-mono font-semibold text-gray-700 ml-1">{{ pdfFilename }}</span>
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-50">

          <!-- To -->
          <div class="px-5 py-4 flex items-start gap-4">
            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-2 w-16 shrink-0">Kepada</label>
            <div class="flex-1">
              <input v-model="form.to" type="email" required placeholder="email@client.com"
                class="w-full text-sm text-gray-800 border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 placeholder:text-gray-300"/>
              <p v-if="errors.to" class="text-xs text-red-500 mt-1">{{ errors.to }}</p>
            </div>
          </div>

          <!-- Subject -->
          <div class="px-5 py-4 flex items-start gap-4">
            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-2 w-16 shrink-0">Subject</label>
            <div class="flex-1">
              <input v-model="form.subject" type="text" required placeholder="Subject email..."
                class="w-full text-sm text-gray-800 border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 placeholder:text-gray-300"/>
              <p v-if="errors.subject" class="text-xs text-red-500 mt-1">{{ errors.subject }}</p>
            </div>
          </div>

          <!-- Body -->
          <div class="px-5 py-4 flex items-start gap-4">
            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-2 w-16 shrink-0">Isi</label>
            <div class="flex-1">
              <textarea v-model="form.body" required rows="10" placeholder="Isi email..."
                class="w-full text-sm text-gray-800 border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 placeholder:text-gray-300 resize-none leading-relaxed"/>
              <p v-if="errors.body" class="text-xs text-red-500 mt-1">{{ errors.body }}</p>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="px-5 py-4 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between gap-3">
          <p class="text-xs text-gray-400">Status otomatis jadi <span class="font-semibold text-blue-500">Sent</span> setelah berhasil dikirim.</p>
          <div class="flex items-center gap-2 shrink-0">
            <Link :href="route('invoices.show', invoice.id)"
              class="px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
              Batal
            </Link>
            <button type="submit" :disabled="sending"
              class="flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
              <svg v-if="sending" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
              {{ sending ? 'Mengirim...' : 'Kirim Email' }}
            </button>
          </div>
        </div>
      </form>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  invoice:        Object,
  defaultSubject: String,
  defaultBody:    String,
});

const form = ref({
  to:      '',
  subject: props.defaultSubject,
  body:    props.defaultBody,
});

const errors  = ref({});
const sending = ref(false);

const pdfFilename = computed(() =>
  (props.invoice.invoice_number ?? 'invoice').replace(/\//g, '-') + '.pdf'
);

const statusClass = computed(() => ({
  draft:  'bg-gray-100 text-gray-500',
  sent:   'bg-blue-50 text-blue-600',
  paid:   'bg-emerald-50 text-emerald-700',
  unpaid: 'bg-red-50 text-red-600',
}[props.invoice.status] ?? 'bg-gray-100 text-gray-500'));

const statusLabel = computed(() =>
  ({ draft: 'Draft', sent: 'Sent', paid: 'Paid', unpaid: 'Unpaid' }[props.invoice.status] ?? props.invoice.status)
);

const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtCurrency = (v) => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

function submit() {
  errors.value  = {};
  sending.value = true;

  router.post(route('invoices.send-email', props.invoice.id), form.value, {
    onError:  (e) => { errors.value = e; sending.value = false; },
    onFinish: ()  => { sending.value = false; },
  });
}
</script>
