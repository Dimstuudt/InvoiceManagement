<template>
  <AppLayout title="Project Categories">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Project Categories</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ categories.length }} kategori terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <TrashPanel type="project-categories" :items="trashed">
            <template #info-header>Kode</template>
            <template #info="{ item }">{{ item.code ?? '—' }}</template>
          </TrashPanel>
          <Link :href="route('master.project-categories.create')"
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
      <div v-if="categories.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-violet-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada project category</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan kategori untuk proyek kamu</p>
        <Link :href="route('master.project-categories.create')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Kategori
        </Link>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="px-4 py-3 w-10">
                <input type="checkbox" :checked="allSelected" @change="toggleAll"
                  class="rounded border-gray-300 dark:border-gray-600 text-indigo-500 focus:ring-indigo-400"/>
              </th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Nama</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Kode</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Deskripsi</th>
              <th class="px-5 py-3 w-20"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="cat in categories" :key="cat.id"
              :class="['transition-colors group', selected.includes(cat.id) ? 'bg-indigo-50/50' : 'hover:bg-gray-50/50']">
              <td class="px-4 py-3.5">
                <input type="checkbox" :value="cat.id" v-model="selected"
                  class="rounded border-gray-300 dark:border-gray-600 text-indigo-500 focus:ring-indigo-400"/>
              </td>
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                    <span class="text-xs font-bold text-violet-400">{{ cat.name.charAt(0).toUpperCase() }}</span>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ cat.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3.5">
                <span v-if="cat.code"
                  class="inline-flex items-center px-2 py-0.5 rounded-md bg-violet-50 text-violet-600 text-xs font-mono font-semibold ring-1 ring-violet-100">
                  {{ cat.code }}
                </span>
                <span v-else class="text-xs text-gray-300">—</span>
              </td>
              <td class="px-4 py-3.5 text-sm text-gray-400">{{ cat.description || '—' }}</td>
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="goEdit(cat.id)" title="Edit"
                    class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
                    </svg>
                  </button>
                  <button @click="destroy(cat)" title="Hapus"
                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
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

const props = defineProps({ categories: Array, trashed: { type: Array, default: () => [] } });

const { requireGate } = useSecurityGate();
const selected = ref([]);

const allSelected = computed(() =>
  props.categories.length > 0 && props.categories.every(c => selected.value.includes(c.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.categories.map(c => c.id);
}

async function goEdit(id) {
  if (!await requireGate()) return;
  router.visit(route('master.project-categories.edit', id));
}

async function destroy(cat) {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: 'Hapus kategori ini?',
    text: `"${cat.name}" akan dipindahkan ke trash.`,
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
  router.delete(route('master.project-categories.destroy', cat.id));
}

async function bulkDestroy() {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: `Hapus ${selected.value.length} kategori?`,
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
  router.delete(route('master.project-categories.bulk-destroy'), {
    data: { ids: selected.value },
    onSuccess: () => { selected.value = []; },
  });
}
</script>

<style scoped>
.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.15s ease; }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
