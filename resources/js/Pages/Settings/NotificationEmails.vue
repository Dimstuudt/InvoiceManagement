<template>
  <AppLayout title="Notifikasi Email">
    <div class="max-w-xl mx-auto space-y-4">

      <!-- Header -->
      <div>
        <h2 class="text-base font-semibold text-gray-800">Notifikasi Email Internal</h2>
        <p class="text-xs text-gray-400 mt-0.5">Daftar email yang menerima notifikasi saat cron mengirim invoice. Entry pertama jadi penerima utama, sisanya di-CC.</p>
      </div>

      <!-- Email List -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-for="em in emails" :key="em.id"
          class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-50 last:border-0">

          <!-- Toggle button -->
          <button @click="toggle(em)"
            :title="em.is_active ? 'Nonaktifkan' : 'Aktifkan'"
            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors border"
            :class="em.is_active
              ? 'bg-emerald-50 border-emerald-200 text-emerald-600 hover:bg-emerald-100'
              : 'bg-gray-50 border-gray-200 text-gray-300 hover:bg-gray-100'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
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
              <span v-else-if="em.label"
                class="text-[10px] text-gray-400">
                {{ em.label }}
              </span>
              <span v-if="!em.is_active" class="text-[10px] text-gray-300">· off</span>
            </div>
          </div>

          <!-- Lock icon for default, delete for others -->
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

        <!-- Empty -->
        <div v-if="emails.length === 0" class="px-5 py-10 text-center text-sm text-gray-300">
          Belum ada email terdaftar.
        </div>
      </div>

      <!-- Add form -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-500 mb-3">Tambah Email</p>
        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <input v-model="form.email" type="email" placeholder="email@contoh.com"
              class="w-full text-sm px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition-colors placeholder-gray-300"
              required />
          </div>
          <div>
            <input v-model="form.label" type="text" placeholder="Label (opsional, misal: Pak Regy)"
              class="w-full text-sm px-3.5 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition-colors placeholder-gray-300" />
          </div>
          <button type="submit"
            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            + Tambah
          </button>
        </form>
      </div>

      <!-- Info -->
      <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4">
        <p class="text-xs font-semibold text-blue-600 mb-1">Cara kerja</p>
        <ul class="text-xs text-blue-500 space-y-1 leading-relaxed">
          <li>· <strong>This User</strong> selalu jadi penerima utama (To) selama aktif</li>
          <li>· Kalau This User off, email aktif tertua jadi penerima utama</li>
          <li>· Email lain yang aktif masuk sebagai CC</li>
          <li>· Kalau semua off, notifikasi internal tidak dikirim</li>
        </ul>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  emails: { type: Array, default: () => [] },
});

const form = reactive({ email: '', label: '' });

function toggle(em) {
  router.patch(route('settings.notification-emails.toggle', em.id), {}, {
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

function submit() {
  router.post(route('settings.notification-emails.store'), {
    email: form.email,
    label: form.label,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      form.email = '';
      form.label = '';
    },
  });
}
</script>
