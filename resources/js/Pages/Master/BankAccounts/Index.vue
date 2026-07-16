<template>
  <AppLayout title="Bank Accounts">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Bank Accounts</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ accounts.length }} akun terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <TrashPanel type="bank-accounts" :items="trashed">
            <template #info-header>Bank</template>
            <template #info="{ item }">{{ item.bank_name ? `${item.bank_name} · ${item.account_number}` : '—' }}</template>
          </TrashPanel>
          <Link :href="route('master.bank-accounts.create')"
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
      <div v-if="accounts.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada bank account</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan rekening bank pertama kamu</p>
        <Link :href="route('master.bank-accounts.create')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Bank
        </Link>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="acc in accounts" :key="acc.id"
          :class="['group bg-white rounded-2xl border shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col',
            selected.includes(acc.id) ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100']">

          <!-- Logo Area -->
          <div class="relative h-32 bg-gradient-to-br from-slate-50 to-gray-100 flex items-center justify-center overflow-hidden">
            <div class="absolute top-2.5 left-2.5 z-10">
              <input type="checkbox" :value="acc.id" v-model="selected"
                class="rounded border-gray-300 text-indigo-500 focus:ring-indigo-400 shadow-sm bg-white"/>
            </div>
            <img v-if="acc.bank_logo_url"
              :src="acc.bank_logo_url"
              class="max-h-16 max-w-[60%] object-contain"
              :alt="acc.bank_name"/>
            <div v-else
              class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center">
              <span class="text-xl font-bold text-gray-300">{{ acc.bank_name.charAt(0) }}</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col">
            <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-widest mb-1">{{ acc.bank_name }}</p>
            <h3 class="text-sm font-semibold text-gray-900 mb-4 leading-snug">{{ acc.name }}</h3>

            <div class="bg-gray-50 rounded-xl px-4 py-3 flex-1 flex items-center justify-between">
              <span class="text-xs text-gray-400">No. Rekening</span>
              <span class="text-sm font-mono font-semibold text-gray-800 tracking-wide">{{ acc.account_number }}</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              <button @click="goEdit(acc.id)"
                class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
                </svg>
                Edit
              </button>
              <button @click="destroy(acc)"
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
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({ accounts: Array, trashed: { type: Array, default: () => [] } });

const { requireGate } = useSecurityGate();
const selected = ref([]);

const allSelected = computed(() =>
  props.accounts.length > 0 && props.accounts.every(a => selected.value.includes(a.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.accounts.map(a => a.id);
}

async function goEdit(id) {
  if (!await requireGate()) return;
  router.visit(route('master.bank-accounts.edit', id));
}

async function destroy(acc) {
  if (!await requireGate()) return;
  router.delete(route('master.bank-accounts.destroy', acc.id));
}

async function bulkDestroy() {
  if (!await requireGate()) return;
  router.delete(route('master.bank-accounts.bulk-destroy'), {
    data: { ids: selected.value },
    onSuccess: () => { selected.value = []; },
  });
}
</script>

<style scoped>
.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.15s ease; }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
