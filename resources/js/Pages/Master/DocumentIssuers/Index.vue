<template>
  <AppLayout title="Document Issuers">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Document Issuers</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ issuers.length }} issuer terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <TrashPanel type="document-issuers" :items="trashed">
            <template #info-header>NPWP</template>
            <template #info="{ item }">{{ item.tax_id_name ?? '—' }}</template>
          </TrashPanel>
          <Link :href="route('master.document-issuers.create')"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
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
      <div v-if="issuers.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada document issuer</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan issuer pertama kamu</p>
        <Link :href="route('master.document-issuers.create')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Issuer
        </Link>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="issuer in issuers" :key="issuer.id"
          :class="['group bg-white rounded-2xl border shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col',
            selected.includes(issuer.id) ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100']">

          <!-- Header Image Area -->
          <div class="relative h-36 bg-gradient-to-br from-slate-50 to-gray-100 flex items-center justify-center overflow-hidden">
            <div class="absolute top-2.5 left-2.5 z-10">
              <input type="checkbox" :value="issuer.id" v-model="selected"
                class="rounded border-gray-300 text-indigo-500 focus:ring-indigo-400 shadow-sm bg-white"/>
            </div>
            <img v-if="issuer.header_image_url"
              :src="issuer.header_image_url"
              class="max-h-28 max-w-full object-contain px-6"
              :alt="issuer.name"/>
            <div v-else class="flex flex-col items-center gap-2 text-gray-300">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span class="text-xs">No header image</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 leading-snug">{{ issuer.name }}</h3>
            <div class="space-y-2 flex-1">
              <div class="flex items-start gap-2">
                <span class="text-xs text-gray-400 w-20 shrink-0 pt-0.5">NPWP</span>
                <span class="text-xs text-gray-700 font-mono">{{ issuer.tax_id_number || '—' }}</span>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-xs text-gray-400 w-20 shrink-0 pt-0.5">Alamat</span>
                <span class="text-xs text-gray-500 line-clamp-2">{{ issuer.tax_id_address || '—' }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              <button @click="goEdit(issuer.id)"
                class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
                </svg>
                Edit
              </button>
              <button @click="destroy(issuer)"
                class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg border border-red-100 hover:border-red-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>
            </div>
          </div>

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

const props = defineProps({ issuers: Array, trashed: { type: Array, default: () => [] } });

const { requireGate } = useSecurityGate();
const selected = ref([]);

const allSelected = computed(() =>
  props.issuers.length > 0 && props.issuers.every(i => selected.value.includes(i.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.issuers.map(i => i.id);
}

async function goEdit(id) {
  if (!await requireGate()) return;
  router.visit(route('master.document-issuers.edit', id));
}

async function destroy(issuer) {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: 'Hapus issuer ini?',
    text: `"${issuer.name}" akan dipindahkan ke trash.`,
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
  router.delete(route('master.document-issuers.destroy', issuer.id));
}

async function bulkDestroy() {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: `Hapus ${selected.value.length} issuer?`,
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
  router.delete(route('master.document-issuers.bulk-destroy'), {
    data: { ids: selected.value },
    onSuccess: () => { selected.value = []; },
  });
}
</script>

<style scoped>
.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.15s ease; }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
