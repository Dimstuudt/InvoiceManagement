<template>
  <AppLayout title="Sender Domains">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Sender Domains</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ senderDomains.length }} sender terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <TrashPanel type="sender-domains" :items="trashed" name-field="display_name">
            <template #info-header>Domain</template>
            <template #info="{ item }">{{ item.domain ?? '—' }}</template>
          </TrashPanel>
          <button @click="showForm = !showForm"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
          </button>
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

      <!-- Add Form -->
      <Transition name="form-slide">
        <div v-if="showForm" class="bg-white rounded-2xl border border-indigo-100 shadow-sm overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Tambah Sender Domain</h3>
            <button @click="showForm = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="px-6 py-5 space-y-4">

            <!-- Domain -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
              <input v-model="form.domain" type="text" placeholder="contoh: smartcoop.com"
                class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                :class="errors.domain ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
              <p v-if="errors.domain" class="mt-1.5 text-xs text-red-500">{{ errors.domain }}</p>
            </div>

            <!-- Prefix -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Prefix Email</label>
              <div class="flex items-center gap-2">
                <input v-model="form.prefix" type="text" placeholder="invoice"
                  class="flex-1 px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                  :class="errors.prefix ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
                <span class="text-sm text-gray-400 shrink-0">@{{ form.domain || 'domain.com' }}</span>
              </div>
              <p v-if="errors.prefix" class="mt-1.5 text-xs text-red-500">{{ errors.prefix }}</p>
              <p v-if="form.prefix && form.domain" class="mt-1.5 text-xs text-gray-400">
                Email pengirim: <span class="font-mono text-indigo-600">{{ form.prefix }}@{{ form.domain }}</span>
              </p>
            </div>

            <!-- Display Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pengirim</label>
              <input v-model="form.display_name" type="text" placeholder="contoh: SmartCoop"
                class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                :class="errors.display_name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
              <p v-if="errors.display_name" class="mt-1.5 text-xs text-red-500">{{ errors.display_name }}</p>
              <p v-if="form.display_name && form.prefix && form.domain" class="mt-1.5 text-xs text-gray-400">
                Tampil di inbox: <span class="text-gray-700">{{ form.display_name }} &lt;{{ form.prefix }}@{{ form.domain }}&gt;</span>
              </p>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button type="button" @click="resetForm"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:border-gray-300 rounded-xl transition-colors bg-white">
              Reset
            </button>
            <button type="button" @click="submit" :disabled="submitting"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </Transition>

      <!-- Empty state -->
      <div v-if="senderDomains.length === 0 && !showForm"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada sender domain</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan domain pengirim email pertama</p>
        <button @click="showForm = true"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Sender
        </button>
      </div>

      <!-- List -->
      <div v-else class="space-y-3">
        <div v-for="sd in senderDomains" :key="sd.id"
          :class="['bg-white rounded-2xl border shadow-sm px-5 py-4 flex items-center justify-between gap-4 transition-all',
            selected.includes(sd.id) ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100']">

          <div class="flex items-center gap-4 min-w-0">
            <input type="checkbox" :value="sd.id" v-model="selected"
              class="rounded border-gray-300 text-indigo-500 focus:ring-indigo-400 shrink-0"/>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ sd.display_name }}</p>
              <p class="text-xs font-mono text-indigo-600 mt-0.5">{{ sd.sender_email }}</p>
              <p class="text-xs text-gray-400 mt-0.5">Tampil di inbox: {{ sd.display_name }} &lt;{{ sd.sender_email }}&gt;</p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button @click="startEdit(sd)"
              class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
              </svg>
            </button>
            <button @click="destroy(sd)"
              class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- Edit Modal -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="editTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/40" @click="editTarget = null"/>
          <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <h3 class="text-sm font-semibold text-gray-900">Edit Sender Domain</h3>
              <button @click="editTarget = null" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div class="px-6 py-5 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                <input v-model="editForm.domain" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prefix Email</label>
                <div class="flex items-center gap-2">
                  <input v-model="editForm.prefix" type="text"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                  <span class="text-sm text-gray-400 shrink-0">@{{ editForm.domain }}</span>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pengirim</label>
                <input v-model="editForm.display_name" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <p v-if="editForm.prefix && editForm.domain" class="text-xs text-gray-400">
                Preview: <span class="text-gray-700">{{ editForm.display_name }} &lt;{{ editForm.prefix }}@{{ editForm.domain }}&gt;</span>
              </p>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
              <button @click="editTarget = null"
                class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl bg-white hover:border-gray-300 transition-colors">
                Batal
              </button>
              <button @click="submitEdit" :disabled="submitting"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                {{ submitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TrashPanel from '@/Components/TrashPanel.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({ senderDomains: Array, trashed: { type: Array, default: () => [] } });

const showForm   = ref(false);
const submitting = ref(false);
const errors     = ref({});
const editTarget = ref(null);

const form     = ref({ display_name: '', prefix: '', domain: '' });
const editForm = ref({ display_name: '', prefix: '', domain: '' });

function resetForm() {
  form.value = { display_name: '', prefix: '', domain: '' };
  errors.value = {};
}

function submit() {
  submitting.value = true;
  router.post(route('master.sender-domains.store'), form.value, {
    onSuccess: () => { showForm.value = false; resetForm(); },
    onError: (e) => { errors.value = e; },
    onFinish: () => { submitting.value = false; },
  });
}

const { requireGate } = useSecurityGate();
const selected = ref([]);

const allSelected = computed(() =>
  props.senderDomains.length > 0 && props.senderDomains.every(s => selected.value.includes(s.id))
);

function toggleAll() {
  selected.value = allSelected.value ? [] : props.senderDomains.map(s => s.id);
}

async function startEdit(sd) {
  if (!await requireGate()) return;
  editTarget.value = sd;
  editForm.value   = { display_name: sd.display_name, prefix: sd.prefix, domain: sd.domain };
}

function submitEdit() {
  submitting.value = true;
  router.put(route('master.sender-domains.update', editTarget.value.id), editForm.value, {
    onSuccess: () => { editTarget.value = null; },
    onFinish:  () => { submitting.value = false; },
  });
}

async function destroy(sd) {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: 'Hapus sender domain ini?',
    text: `"${sd.display_name}" akan dipindahkan ke trash.`,
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
  router.delete(route('master.sender-domains.destroy', sd.id));
}

async function bulkDestroy() {
  if (!await requireGate()) return;
  const { isConfirmed } = await Swal.fire({
    title: `Hapus ${selected.value.length} sender domain?`,
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
  router.delete(route('master.sender-domains.bulk-destroy'), {
    data: { ids: selected.value },
    onSuccess: () => { selected.value = []; },
  });
}
</script>

<style scoped>
.bulk-bar-enter-active, .bulk-bar-leave-active { transition: all 0.15s ease; }
.bulk-bar-enter-from, .bulk-bar-leave-to { opacity: 0; transform: translateY(-4px); }
.form-slide-enter-active, .form-slide-leave-active { transition: all 0.2s ease; }
.form-slide-enter-from, .form-slide-leave-to { opacity: 0; transform: translateY(-8px); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
