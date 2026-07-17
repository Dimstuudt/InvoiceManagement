<template>
  <AppLayout title="Notifikasi Email">
    <div class="max-w-xl mx-auto space-y-4">

      <!-- Header -->
      <div>
        <h2 class="text-base font-semibold text-gray-800">Notifikasi Email Internal</h2>
        <p class="text-xs text-gray-400 mt-0.5">Konfigurasi siapa yang menerima notifikasi saat cron mengirim invoice. Tiap grup dikirim sebagai email terpisah.</p>
      </div>

      <!-- Groups -->
      <div v-for="group in groups" :key="group.group_id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Group header -->
        <div class="px-5 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Grup {{ group.group_id }}</span>
          <span class="text-[10px] text-gray-300">{{ group.emails.filter(e => e.is_active).length }} aktif</span>
        </div>

        <!-- Email rows -->
        <div v-for="em in group.emails" :key="em.id"
          class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0">

          <!-- Active toggle -->
          <button @click="toggle(em)"
            :title="em.is_active ? 'Nonaktifkan' : 'Aktifkan'"
            class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors border"
            :class="em.is_active
              ? 'bg-emerald-50 border-emerald-200 text-emerald-600 hover:bg-emerald-100'
              : 'bg-gray-50 border-gray-200 text-gray-300 hover:bg-gray-100'">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </button>

          <!-- TO / CC badge -->
          <button @click="toggleType(em)"
            :title="em.send_type === 'to' ? 'Jadikan CC' : 'Jadikan TO'"
            class="w-8 h-6 rounded text-[10px] font-bold tracking-wide shrink-0 transition-colors border"
            :class="em.send_type === 'to'
              ? 'bg-indigo-50 border-indigo-200 text-indigo-600 hover:bg-indigo-100'
              : 'bg-amber-50 border-amber-200 text-amber-600 hover:bg-amber-100'">
            {{ em.send_type.toUpperCase() }}
          </button>

          <!-- Email info -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate"
              :class="em.is_active ? 'text-gray-800' : 'text-gray-300 line-through'">
              {{ em.email }}
            </p>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span v-if="em.is_default"
                class="text-[10px] font-bold px-1.5 py-0.5 bg-indigo-50 text-indigo-500 rounded-md">
                This User
              </span>
              <span v-else-if="em.label" class="text-[10px] text-gray-400">{{ em.label }}</span>
              <span v-if="!em.is_active" class="text-[10px] text-gray-300">· off</span>
            </div>
          </div>

          <!-- Lock (default) or delete -->
          <div class="shrink-0">
            <svg v-if="em.is_default" class="w-3.5 h-3.5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <button v-else @click="remove(em)"
              class="p-1 text-gray-300 hover:text-red-400 transition-colors rounded">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Inline add CC form -->
        <div v-if="addCcGroup === group.group_id" class="px-5 py-3 bg-amber-50/60 border-t border-amber-100 space-y-2">
          <p class="text-[10px] font-semibold text-amber-600 uppercase tracking-wider">Tambah CC ke Grup {{ group.group_id }}</p>
          <input v-model="ccForm.email" type="email" placeholder="email@contoh.com" required
            class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 placeholder-gray-300"/>
          <input v-model="ccForm.label" type="text" placeholder="Label (opsional)"
            class="w-full text-sm px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 placeholder-gray-300"/>
          <div class="flex gap-2">
            <button @click="submitCc(group.group_id)"
              class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl transition-colors">
              Tambah CC
            </button>
            <button @click="addCcGroup = null"
              class="px-4 py-2 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
              Batal
            </button>
          </div>
        </div>
        <button v-else @click="addCcGroup = group.group_id; addGroupOpen = false"
          class="w-full px-5 py-2.5 text-left text-xs text-amber-500 hover:text-amber-700 hover:bg-amber-50 border-t border-gray-50 transition-colors">
          + Tambah CC ke grup ini
        </button>

      </div>

      <!-- Add new group -->
      <div v-if="addGroupOpen" class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-5 space-y-3">
        <p class="text-xs font-semibold text-indigo-600">Grup Baru — Email TO</p>
        <input v-model="newGroupForm.email" type="email" placeholder="email@contoh.com" required
          class="w-full text-sm px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-gray-300"/>
        <input v-model="newGroupForm.label" type="text" placeholder="Label (opsional)"
          class="w-full text-sm px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 placeholder-gray-300"/>
        <div class="flex gap-2">
          <button @click="submitNewGroup"
            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            Buat Grup
          </button>
          <button @click="addGroupOpen = false"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
            Batal
          </button>
        </div>
      </div>
      <button v-else @click="addGroupOpen = true; addCcGroup = null"
        class="w-full py-3 border-2 border-dashed border-gray-200 rounded-2xl text-sm text-gray-400 hover:text-indigo-600 hover:border-indigo-300 transition-colors">
        + Buat Grup Baru
      </button>

      <!-- Info -->
      <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4">
        <p class="text-xs font-semibold text-blue-600 mb-1">Cara kerja</p>
        <ul class="text-xs text-blue-500 space-y-1 leading-relaxed">
          <li>· Tiap grup dikirim sebagai <strong>1 email terpisah</strong> saat cron berjalan</li>
          <li>· <strong>TO</strong> = penerima utama, <strong>CC</strong> = tembusan (ikut email yang sama di grup itu)</li>
          <li>· Klik badge <strong>TO/CC</strong> untuk mengganti peran email (TO minimal 1 per grup)</li>
          <li>· Kalau semua email off, notifikasi internal tidak dikirim</li>
        </ul>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  groups: { type: Array, default: () => [] },
});

const addCcGroup   = ref(null);
const addGroupOpen = ref(false);
const ccForm       = reactive({ email: '', label: '' });
const newGroupForm = reactive({ email: '', label: '' });

function toggle(em) {
  router.patch(route('settings.notification-emails.toggle', em.id), {}, {
    preserveScroll: true,
    preserveState: false,
  });
}

function toggleType(em) {
  const newType = em.send_type === 'to' ? 'cc' : 'to';
  router.patch(route('settings.notification-emails.set-type', em.id), { send_type: newType }, {
    preserveScroll: true,
    preserveState: false,
  });
}

function remove(em) {
  Swal.fire({
    title: 'Hapus email ini?',
    html: `<span class="text-sm text-gray-600"><strong>${em.email}</strong> akan dihapus dari daftar notifikasi.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
  }).then(r => {
    if (r.isConfirmed) {
      router.delete(route('settings.notification-emails.destroy', em.id), {
        preserveScroll: true,
      });
    }
  });
}

function submitCc(groupId) {
  if (!ccForm.email) return;
  router.post(route('settings.notification-emails.store'), {
    email:     ccForm.email,
    label:     ccForm.label,
    group_id:  groupId,
    send_type: 'cc',
  }, {
    preserveScroll: true,
    onSuccess: () => {
      ccForm.email = '';
      ccForm.label = '';
      addCcGroup.value = null;
    },
    onError: (errors) => {
      const msg = Object.values(errors)[0] ?? 'Gagal menambahkan email.';
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 3000 });
    },
  });
}

function submitNewGroup() {
  if (!newGroupForm.email) return;
  router.post(route('settings.notification-emails.store'), {
    email:     newGroupForm.email,
    label:     newGroupForm.label,
    group_id:  'new',
    send_type: 'to',
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newGroupForm.email = '';
      newGroupForm.label = '';
      addGroupOpen.value = false;
    },
    onError: (errors) => {
      const msg = Object.values(errors)[0] ?? 'Gagal membuat grup.';
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 3000 });
    },
  });
}
</script>
