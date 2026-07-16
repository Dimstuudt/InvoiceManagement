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
          <a :href="route('master.email-template-groups.preview-wrapper', { type: 'invoice' })" target="_blank"
            class="px-3.5 py-2 text-xs font-medium text-violet-600 border border-violet-200 hover:bg-violet-50 rounded-lg transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Preview Invoice
          </a>
          <a :href="route('master.email-template-groups.preview-wrapper', { type: 'receipt' })" target="_blank"
            class="px-3.5 py-2 text-xs font-medium text-emerald-600 border border-emerald-200 hover:bg-emerald-50 rounded-lg transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Preview Receipt
          </a>
          <Link :href="route('master.email-template-groups.create') + '?prefill=default'"
            class="px-3.5 py-2 text-xs font-medium text-indigo-600 border border-indigo-200 hover:bg-indigo-50 rounded-lg transition-colors">
            ✦ Mulai dari contoh
          </Link>
          <TrashPanel type="email-template-groups" :items="trashed">
            <template #info-header>Status</template>
            <template #info="{ item }">{{ item.is_default ? 'Default' : '—' }}</template>
          </TrashPanel>
          <Link :href="route('master.email-template-groups.create')"
            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
            + Grup Baru
          </Link>
        </div>
      </div>

      <!-- Bulk action bar -->
      <Transition name="bulk-bar">
        <div v-if="selected.length > 0"
          class="flex items-center gap-3 px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900">
          <input type="checkbox" :checked="allSelected" @change="toggleAll"
            class="rounded border-gray-300 dark:border-gray-600 text-indigo-500 focus:ring-indigo-400"/>
          <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ selected.length }} dipilih</span>
          <button @click="selected = []" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ml-1">Batal</button>
          <div class="ml-auto">
            <button @click="bulkDestroy"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-300 dark:bg-red-900/50 dark:hover:bg-red-900 rounded-lg transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Hapus ke Trash ({{ selected.length }})
            </button>
          </div>
        </div>
      </Transition>

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
        :class="['bg-white border rounded-2xl overflow-hidden transition-all',
          selected.includes(group.id) ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-200']">

        <!-- Group header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <input type="checkbox" :value="group.id" v-model="selected"
              class="rounded border-gray-300 text-indigo-500 focus:ring-indigo-400 shrink-0"/>
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
            <button @click="goEdit(group.id)"
              class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-indigo-600 border border-gray-200 hover:border-indigo-300 rounded-lg transition-colors">
              Edit
            </button>
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
import TrashPanel from '@/Components/TrashPanel.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({
  groups:  Array,
  trashed: { type: Array, default: () => [] },
});

const { requireGate } = useSecurityGate();
const selected = ref([]);

const allSelected = computed(() =>
  props.groups.length > 0 && props.groups.every(g => selected.value.includes(g.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.groups.map(g => g.id);
}

async function goEdit(id) {
  if (!await requireGate()) return;
  router.visit(route('master.email-template-groups.edit', id));
}

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

async function deleteGroup(group) {
  if (!await requireGate()) return;
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

async function bulkDestroy() {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: `Hapus ${selected.value.length} grup template?`,
    text: 'Data akan dipindahkan ke trash.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
  });
  if (!isConfirmed) return;
  router.delete(route('master.email-template-groups.bulk-destroy'), {
    data: { ids: selected.value },
    onSuccess: () => { selected.value = []; },
  });
}
</script>

<style scoped>
.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.15s ease; }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
