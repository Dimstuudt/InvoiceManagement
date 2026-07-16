<template>
  <div class="relative" ref="wrapper">
    <!-- Trigger button -->
    <button @click="open = !open"
      class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-xl border transition-colors"
      :class="open
        ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-800'
        : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700'"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
      Trash
      <span v-if="items.length > 0"
        class="px-1.5 py-0.5 text-[11px] rounded-full font-semibold"
        :class="open ? 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
      >{{ items.length }}</span>
    </button>

    <!-- Dropdown panel -->
    <Transition name="drop">
      <div v-if="open"
        class="absolute right-0 top-full mt-2 z-30 w-[700px] max-w-[90vw] bg-white dark:bg-gray-800 rounded-2xl border border-red-100 dark:border-red-900 shadow-xl overflow-hidden">

        <!-- Header bar -->
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-red-50 dark:border-red-900/60 bg-red-50/60 dark:bg-red-950/40">
          <span class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase tracking-wide">Trash</span>
          <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Bulk bar -->
        <Transition name="slide-down">
          <div v-if="selected.length > 0"
            class="flex items-center gap-3 px-4 py-2 border-b border-red-100 dark:border-red-900 bg-red-50 dark:bg-red-950">
            <span class="text-xs font-medium text-red-700 dark:text-red-300">{{ selected.length }} dipilih</span>
            <div class="ml-auto flex items-center gap-2">
              <button @click="bulkRestore"
                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 hover:bg-emerald-200 dark:text-emerald-300 dark:bg-emerald-900 dark:hover:bg-emerald-800 rounded-lg transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Pulihkan ({{ selected.length }})
              </button>
              <button @click="bulkForceDelete"
                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-300 dark:bg-red-900 dark:hover:bg-red-800 rounded-lg transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Permanen ({{ selected.length }})
              </button>
            </div>
          </div>
        </Transition>

        <!-- Empty -->
        <div v-if="items.length === 0" class="flex flex-col items-center justify-center py-10">
          <svg class="w-7 h-7 text-gray-200 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          <p class="text-sm text-gray-400 dark:text-gray-500">Trash kosong</p>
        </div>

        <!-- Table -->
        <div v-else class="max-h-80 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
              <tr class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                <th class="px-4 py-2 text-left w-10">
                  <input type="checkbox" :checked="allSelected" @change="toggleAll"
                    class="rounded border-gray-300 dark:border-gray-600 text-red-500 focus:ring-red-400"/>
                </th>
                <th class="px-4 py-2 text-left">Nama</th>
                <th class="px-4 py-2 text-left hidden sm:table-cell">
                  <slot name="info-header">Info</slot>
                </th>
                <th class="px-4 py-2 text-left hidden md:table-cell">Dihapus</th>
                <th class="px-4 py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
              <tr v-for="item in items" :key="item.id"
                :class="['transition-colors', selected.includes(item.id) ? 'bg-red-50/60 dark:bg-red-950/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30']">
                <td class="px-4 py-2.5">
                  <input type="checkbox" :value="item.id" v-model="selected"
                    class="rounded border-gray-300 dark:border-gray-600 text-red-500 focus:ring-red-400"/>
                </td>
                <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                  {{ item[nameField] ?? item.name ?? `#${item.id}` }}
                </td>
                <td class="px-4 py-2.5 hidden sm:table-cell text-gray-400 dark:text-gray-500 text-xs">
                  <slot name="info" :item="item">—</slot>
                </td>
                <td class="px-4 py-2.5 hidden md:table-cell text-gray-400 dark:text-gray-500 text-xs font-mono">
                  {{ fmtDate(item.deleted_at) }}
                </td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-1.5 justify-end">
                    <button @click="restoreOne(item)"
                      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/40 dark:hover:bg-emerald-900 rounded-lg transition-colors">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                      Pulihkan
                    </button>
                    <button @click="forceDeleteOne(item)"
                      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 dark:text-red-300 dark:bg-red-900/40 dark:hover:bg-red-900 rounded-lg transition-colors">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Hapus Permanen
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({
  type:      { type: String, required: true },
  items:     { type: Array,  default: () => [] },
  nameField: { type: String, default: 'name' },
});

const open     = ref(false);
const selected = ref([]);
const wrapper  = ref(null);

const { requireGate } = useSecurityGate();

// Tutup saat klik di luar
function onClickOutside(e) {
  if (open.value && wrapper.value && !wrapper.value.contains(e.target)) {
    open.value = false;
  }
}
onMounted(()      => document.addEventListener('mousedown', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside));

const allSelected = computed(() =>
  props.items.length > 0 && props.items.every(i => selected.value.includes(i.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.items.map(i => i.id);
}

function fmtDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

async function restoreOne(item) {
  if (!await requireGate()) return;
  router.post(route('master.trash.restore', { type: props.type, id: item.id }));
}

async function forceDeleteOne(item) {
  if (!await requireGate()) return;
  const name = item[props.nameField] ?? item.name ?? `#${item.id}`;
  Swal.fire({
    title: 'Hapus Permanen?',
    text: `"${name}" akan dihapus selamanya.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonText: 'Batal',
    confirmButtonText: 'Ya, Hapus',
  }).then(r => {
    if (r.isConfirmed)
      router.delete(route('master.trash.force-delete', { type: props.type, id: item.id }));
  });
}

async function bulkRestore() {
  if (!await requireGate()) return;
  router.post(
    route('master.trash.bulk-restore', { type: props.type }),
    { ids: selected.value },
    { onSuccess: () => { selected.value = []; } }
  );
}

async function bulkForceDelete() {
  if (!await requireGate()) return;
  Swal.fire({
    title: `Hapus ${selected.value.length} item permanen?`,
    text: 'Tidak bisa dipulihkan setelah ini.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonText: 'Batal',
    confirmButtonText: 'Ya, Hapus',
  }).then(r => {
    if (r.isConfirmed)
      router.delete(
        route('master.trash.bulk-force-delete', { type: props.type }),
        { data: { ids: selected.value }, onSuccess: () => { selected.value = []; } }
      );
  });
}
</script>

<style scoped>
.drop-enter-active,
.drop-leave-active   { transition: all 0.15s ease; }
.drop-enter-from,
.drop-leave-to       { opacity: 0; transform: translateY(-4px); }

.slide-down-enter-active,
.slide-down-leave-active { transition: all 0.15s ease; }
.slide-down-enter-from,
.slide-down-leave-to    { opacity: 0; transform: translateY(-4px); }
</style>
