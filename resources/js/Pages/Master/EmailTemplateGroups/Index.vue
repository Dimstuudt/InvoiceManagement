<template>
  <AppLayout title="Grup Template Email">
    <div class="max-w-4xl mx-auto space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-800">Grup Template Email</h2>
          <p class="text-xs text-gray-400 mt-0.5">Setiap grup punya template berbeda untuk send1–send5 dan paid.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('master.email-template-groups.create') + '?prefill=default'"
            class="px-3.5 py-2 text-xs font-medium text-indigo-600 border border-indigo-200 hover:bg-indigo-50 rounded-lg transition-colors">
            ✦ Mulai dari contoh
          </Link>
          <Link :href="route('master.email-template-groups.create')"
            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
            + Grup Baru
          </Link>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="groups.length === 0"
        class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-600 mb-1">Belum ada grup template</p>
        <p class="text-xs text-gray-400 mb-4">Tidak perlu mulai dari nol — kami sudah siapkan contoh lengkap untuk semua tahap pengiriman.</p>
        <Link :href="route('master.email-template-groups.create') + '?prefill=default'"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          ✦ Mulai dari contoh
        </Link>
      </div>

      <!-- List -->
      <div v-for="group in groups" :key="group.id"
        class="bg-white border border-gray-200 rounded-2xl overflow-hidden">

        <!-- Group header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-gray-800">{{ group.name }}</p>
                <span v-if="group.is_default"
                  class="text-[10px] font-bold px-1.5 py-0.5 bg-indigo-100 text-indigo-600 rounded-md">
                  DEFAULT
                </span>
              </div>
              <p class="text-xs text-gray-400 mt-0.5">{{ filledStages(group) }} / 6 tahap terisi</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Link :href="route('master.email-template-groups.edit', group.id)"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-indigo-600 border border-gray-200 hover:border-indigo-300 rounded-lg transition-colors">
              Edit
            </Link>
            <button @click="deleteGroup(group)"
              class="px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-600 border border-transparent hover:border-red-200 rounded-lg transition-colors">
              Hapus
            </button>
          </div>
        </div>

        <!-- Stage pills -->
        <div class="px-5 py-3 flex flex-wrap gap-2">
          <div v-for="stage in STAGES" :key="stage.key"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors"
            :class="hasStage(group, stage.key)
              ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
              : 'bg-gray-50 text-gray-400 border border-dashed border-gray-200'">
            <span class="w-1.5 h-1.5 rounded-full"
              :class="hasStage(group, stage.key) ? 'bg-emerald-500' : 'bg-gray-300'"/>
            {{ stage.label }}
          </div>
        </div>

        <!-- Preview subject per stage -->
        <div class="border-t border-gray-50">
          <template v-for="stage in STAGES" :key="stage.key">
          <div v-if="hasStage(group, stage.key)"
            class="flex items-baseline gap-3 px-5 py-2 border-b border-gray-50 last:border-0">
            <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider w-10 shrink-0">
              {{ stage.label }}
            </span>
            <span class="text-xs text-gray-500 truncate font-mono">
              {{ group[`subject_${stage.key}`] }}
            </span>
          </div>
          </template>
        </div>

      </div>

      <!-- Variabel referensi -->
      <div class="bg-white border border-gray-200 rounded-2xl p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Variabel yang Tersedia</p>
        <div class="flex flex-wrap gap-2">
          <span v-for="v in ALL_VARIABLES" :key="v"
            class="text-xs font-mono px-2 py-1 bg-gray-50 text-indigo-500 border border-gray-200 rounded-lg">
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
import Swal from 'sweetalert2';

const props = defineProps({
  groups: Array,
});

const STAGES = [
  { key: 'send1', label: 'Send 1' },
  { key: 'send2', label: 'Send 2' },
  { key: 'send3', label: 'Send 3' },
  { key: 'send4', label: 'Send 4' },
  { key: 'send5', label: 'Send 5' },
  { key: 'paid',  label: 'Paid'   },
];

const ALL_VARIABLES = [
  '{{invoice_number}}', '{{issue_date}}', '{{due_date}}', '{{amount}}',
  '{{project_name}}', '{{client_name}}', '{{director}}', '{{pic}}', '{{client_city}}',
  '{{issuer_name}}', '{{bank_name}}', '{{bank_account_number}}', '{{bank_holder}}',
  '{{send_stage}}',
];

function hasStage(group, key) {
  return !!(group[`subject_${key}`] || group[`body_${key}`]);
}

function filledStages(group) {
  return STAGES.filter(s => hasStage(group, s.key)).length;
}

function deleteGroup(group) {
  Swal.fire({
    title: 'Hapus grup ini?',
    html: `<span class="text-sm text-gray-600">Grup <strong>"${group.name}"</strong> akan dihapus permanen. Invoice yang menggunakan grup ini tidak akan terpengaruh, tapi tidak punya template lagi.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
  }).then(r => {
    if (r.isConfirmed) {
      router.delete(route('master.email-template-groups.destroy', group.id), {
        onSuccess: () => Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Grup berhasil dihapus',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        }),
      });
    }
  });
}
</script>
