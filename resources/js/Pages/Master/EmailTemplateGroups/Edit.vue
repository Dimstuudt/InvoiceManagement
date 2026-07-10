<template>
  <AppLayout :title="`Edit Grup: ${group.name}`">
    <div class="-m-4 lg:-m-6 flex flex-col lg:flex-row lg:h-[calc(100vh-4rem)]">

      <!-- ===== KIRI: Form ===== -->
      <div class="w-full lg:w-[480px] lg:shrink-0 bg-white border-b lg:border-b-0 lg:border-r border-gray-200 overflow-y-auto">
        <div class="p-6">

          <div class="flex items-center gap-2 mb-6">
            <Link :href="route('master.email-template-groups.index')"
              class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </Link>
            <h2 class="text-base font-semibold text-gray-800">Edit Grup Template</h2>
          </div>

          <form @submit.prevent="submit" class="space-y-6">

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Grup</label>
              <input v-model="form.name" type="text"
                class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                :class="form.errors.name ? 'border-red-400' : 'border-gray-300'"/>
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <button type="button" @click="form.is_default = !form.is_default"
              class="flex items-center gap-2.5">
              <div class="relative w-9 h-5 rounded-full transition-colors duration-200"
                :class="form.is_default ? 'bg-indigo-600' : 'bg-gray-300'">
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                  :class="form.is_default ? 'translate-x-4' : 'translate-x-0'"/>
              </div>
              <span class="text-sm" :class="form.is_default ? 'text-indigo-600 font-medium' : 'text-gray-500'">
                Jadikan grup default
              </span>
            </button>

            <!-- Tab stage -->
            <div>
              <div class="flex border-b border-gray-200 mb-4 gap-1 overflow-x-auto">
                <button v-for="stage in STAGES" :key="stage.key"
                  type="button"
                  @click="activeStage = stage.key"
                  class="px-3 py-2 text-xs font-semibold rounded-t-lg whitespace-nowrap transition-colors border-b-2 -mb-px"
                  :class="activeStage === stage.key
                    ? stageColor(stage.key) + ' border-current'
                    : 'text-gray-400 border-transparent hover:text-gray-600'">
                  <span class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full"
                      :class="hasContent(stage.key) ? 'bg-current' : 'bg-gray-300'"/>
                    {{ stage.label }}
                  </span>
                </button>
              </div>

              <div v-for="stage in STAGES" :key="stage.key" v-show="activeStage === stage.key" class="space-y-3">
                <div class="px-3 py-2 rounded-lg text-xs" :class="stageBg(stage.key)">
                  {{ stage.description }}
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Subject</label>
                  <input v-model="form[`subject_${stage.key}`]" type="text"
                    :placeholder="`Subject email ${stage.label}...`"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Isi Email (Body)</label>
                  <textarea v-model="form[`body_${stage.key}`]" rows="12"
                    :placeholder="`Isi email untuk ${stage.label}...`"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none leading-relaxed"/>
                </div>
              </div>

              <div class="mt-4 bg-gray-50 rounded-xl border border-gray-200 p-3">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Variabel — klik untuk copy</p>
                <div class="flex flex-wrap gap-1.5">
                  <button v-for="v in ALL_VARIABLES" :key="v" type="button" @click="copyVar(v)"
                    class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-mono bg-white text-gray-600 border border-gray-200 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                    {{ v }}
                  </button>
                </div>
              </div>
            </div>

            <div class="flex gap-3 pt-2 pb-8">
              <button type="submit" :disabled="form.processing"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
              <Link :href="route('master.email-template-groups.index')"
                class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-300 hover:border-gray-400 rounded-lg transition-colors">
                Batal
              </Link>
            </div>

          </form>
        </div>
      </div>

      <!-- ===== KANAN: Preview ===== -->
      <div class="flex-1 bg-slate-50 overflow-y-auto">
        <div class="p-6">

          <div class="flex items-center justify-between mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Preview — {{ activeStageLabel }}</p>
            <span class="text-xs text-amber-500/80 font-medium italic">data contoh</span>
          </div>

          <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-4 py-2.5 border-b border-gray-100 flex items-center gap-1.5 bg-gray-50/80">
              <div class="w-2.5 h-2.5 rounded-full bg-red-400"/>
              <div class="w-2.5 h-2.5 rounded-full bg-amber-400"/>
              <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"/>
            </div>
            <div class="px-4 py-3 border-b border-gray-100 space-y-2 bg-gray-50/40">
              <div class="flex items-baseline gap-2.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider w-10 shrink-0">Dari</span>
                <span class="text-xs text-gray-500">{{ sampleFrom }}</span>
              </div>
              <div class="flex items-baseline gap-2.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider w-10 shrink-0">Ke</span>
                <span class="text-xs text-gray-500">{{ sampleTo }}</span>
              </div>
              <div class="flex items-baseline gap-2.5 pt-0.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider w-10 shrink-0">Subj</span>
                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ previewSubject || '—' }}</span>
              </div>
            </div>
            <div class="px-4 py-4">
              <p v-if="!previewBody" class="text-xs text-gray-300 italic text-center py-10">
                Isi email akan tampil di sini...
              </p>
              <pre v-else class="text-xs text-gray-700 leading-relaxed whitespace-pre-wrap font-sans">{{ previewBody }}</pre>
            </div>
            <div v-if="previewBody" class="px-4 pb-4">
              <div class="h-px bg-gray-100 mb-3"/>
              <div class="inline-flex items-center gap-2 px-2.5 py-1.5 bg-gray-50 rounded-lg border border-gray-200">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                </svg>
                <span class="text-xs font-mono text-gray-500">001-IT-INV-MVC-VI-2026.pdf</span>
              </div>
            </div>
          </div>

          <details class="mt-4 group">
            <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600 transition-colors list-none flex items-center gap-1.5">
              <svg class="w-3 h-3 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              Lihat nilai contoh yang digunakan
            </summary>
            <div class="mt-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div v-for="([k, v]) in Object.entries(SAMPLE)" :key="k"
                class="flex items-center px-3 py-1.5 gap-3 border-b border-gray-50 last:border-0">
                <span class="text-[10px] font-mono text-indigo-400 w-44 shrink-0">{{ k }}</span>
                <span class="text-xs text-gray-500 truncate">{{ v }}</span>
              </div>
            </div>
          </details>

        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  group: Object,
});

const STAGES = [
  { key: 'send1', label: 'Send 1', description: 'Dikirim pada hari H (issue_date). Nada: informatif, profesional.' },
  { key: 'send2', label: 'Send 2', description: '+14 hari. Pengingat pertama. Nada: sopan, ramah.' },
  { key: 'send3', label: 'Send 3', description: '+21 hari. Pengingat kedua. Nada: lebih tegas.' },
  { key: 'send4', label: 'Send 4', description: '+28 hari. Pengingat ketiga. Nada: mendesak.' },
  { key: 'send5', label: 'Send 5', description: '+35 hari. Peringatan akhir — layanan akan dinonaktifkan.' },
  { key: 'paid',  label: 'Paid',  description: 'Dikirim saat invoice ditandai Lunas. Sertakan receipt/konfirmasi.' },
];

const ALL_VARIABLES = [
  '{{invoice_number}}', '{{issue_date}}', '{{due_date}}', '{{amount}}',
  '{{project_name}}', '{{client_name}}', '{{director}}', '{{pic}}', '{{client_city}}',
  '{{issuer_name}}', '{{bank_name}}', '{{bank_account_number}}', '{{bank_holder}}', '{{send_stage}}',
];

const SAMPLE = {
  '{{invoice_number}}':      '001/IT/INV/MVC/VI/2026',
  '{{issue_date}}':          '1 Juni 2026',
  '{{due_date}}':            '15 Juni 2026',
  '{{amount}}':              'Rp 5.000.000',
  '{{project_name}}':        'Pengembangan Website',
  '{{client_name}}':         'PT. Maju Bersama',
  '{{director}}':            'Budi Santoso',
  '{{pic}}':                 'Andi Wijaya',
  '{{client_city}}':         'Jakarta',
  '{{issuer_name}}':         'SmartCoop',
  '{{bank_name}}':           'BCA',
  '{{bank_account_number}}': '1234567890',
  '{{bank_holder}}':         'PT. SmartCoop Indonesia',
  '{{send_stage}}':          'send1',
};

function render(text) {
  if (!text) return '';
  return Object.entries(SAMPLE).reduce((t, [k, v]) => t.replaceAll(k, v), text);
}

const stageFields = Object.fromEntries(
  STAGES.flatMap(s => [
    [`subject_${s.key}`, props.group[`subject_${s.key}`] ?? ''],
    [`body_${s.key}`,    props.group[`body_${s.key}`]    ?? ''],
  ])
);

const form = useForm({
  name:       props.group.name,
  is_default: props.group.is_default,
  ...stageFields,
});

const activeStage = ref('send1');
const activeStageLabel = computed(() => STAGES.find(s => s.key === activeStage.value)?.label ?? '');
const previewSubject   = computed(() => render(form[`subject_${activeStage.value}`]));
const previewBody      = computed(() => render(form[`body_${activeStage.value}`]));

const sampleFrom = `${SAMPLE['{{issuer_name}}']} <no-reply@perusahaan.com>`;
const sampleTo   = `${SAMPLE['{{director}}']} <budi.santoso@email.com>`;

function hasContent(key) {
  return !!(form[`subject_${key}`] || form[`body_${key}`]);
}

function stageColor(key) {
  const map = {
    send1: 'text-indigo-600', send2: 'text-sky-600',
    send3: 'text-amber-600',  send4: 'text-orange-600',
    send5: 'text-red-600',    paid:  'text-emerald-600',
  };
  return map[key] ?? 'text-gray-600';
}

function stageBg(key) {
  const map = {
    send1: 'bg-indigo-50 text-indigo-600', send2: 'bg-sky-50 text-sky-600',
    send3: 'bg-amber-50 text-amber-600',   send4: 'bg-orange-50 text-orange-600',
    send5: 'bg-red-50 text-red-600',       paid:  'bg-emerald-50 text-emerald-600',
  };
  return map[key] ?? 'bg-gray-50 text-gray-600';
}

function copyVar(v) {
  navigator.clipboard.writeText(v).catch(() => {});
}

function submit() {
  form.patch(route('master.email-template-groups.update', props.group.id));
}
</script>
