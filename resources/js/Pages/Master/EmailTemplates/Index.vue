<template>
  <AppLayout title="Email Templates">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-gray-800">Email Templates</h2>
          <p class="text-sm text-gray-400 mt-0.5">Template email untuk pengiriman invoice</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <Link :href="route('master.email-templates.create') + '?prefill=default'"
            class="inline-flex items-center px-3.5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 text-gray-600 text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Buat dari Referensi
          </Link>
          <Link :href="route('master.email-templates.create')"
            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Template
          </Link>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="templates.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-indigo-50 flex items-center justify-center">
          <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-700">Belum ada template email</p>
        <p class="text-xs text-gray-400 mt-1 mb-4">Buat template agar pengiriman invoice lebih cepat dan konsisten.</p>
        <Link :href="route('master.email-templates.create')"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
          Buat Template Pertama
        </Link>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div v-for="tpl in templates" :key="tpl.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 group hover:border-indigo-200 transition-colors">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 min-w-0 flex-1">
              <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4.5 h-4.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="text-sm font-semibold text-gray-800">{{ tpl.name }}</p>
                  <span v-if="tpl.is_default"
                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-600 ring-1 ring-indigo-200">
                    Default
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Subject: <span class="font-medium text-gray-700">{{ tpl.subject }}</span></p>
                <p class="text-xs text-gray-400 mt-1.5 line-clamp-2 leading-relaxed">{{ tpl.body }}</p>
              </div>
            </div>
            <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
              <Link :href="route('master.email-templates.edit', tpl.id)"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
              </Link>
              <button @click="deleteTemplate(tpl)"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>
            </div>
          </div>

          <!-- Variable chips -->
          <div class="mt-3 pt-3 border-t border-gray-50 flex flex-wrap gap-1.5">
            <span v-for="v in extractVars(tpl.body + ' ' + tpl.subject)" :key="v"
              class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono bg-gray-100 text-gray-500">
              {{ v }}
            </span>
          </div>
        </div>
      </div>

      <!-- Variable reference -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Variabel yang tersedia</p>
        <div class="flex flex-wrap gap-2">
          <span v-for="v in allVariables" :key="v"
            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono bg-gray-50 text-gray-600 border border-gray-200">
            {{ v }}
          </span>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({ templates: Array });

const allVariables = [
  '{{invoice_number}}', '{{issue_date}}', '{{due_date}}', '{{amount}}',
  '{{project_name}}', '{{client_name}}', '{{director}}', '{{pic}}', '{{client_city}}',
  '{{issuer_name}}', '{{bank_name}}', '{{bank_account_number}}', '{{bank_holder}}',
];

function extractVars(text) {
  return [...new Set((text.match(/\{\{[^}]+\}\}/g) ?? []))];
}

function deleteTemplate(tpl) {
  if (!confirm(`Hapus template "${tpl.name}"?`)) return;
  router.delete(route('master.email-templates.destroy', tpl.id));
}
</script>
