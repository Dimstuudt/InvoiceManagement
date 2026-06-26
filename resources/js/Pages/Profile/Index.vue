<template>
  <AppLayout title="Profil Saya">
    <div class="max-w-2xl mx-auto space-y-6">

      <!-- Avatar + Info -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-5">
          <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center shrink-0">
            <span class="text-2xl font-bold text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
          </div>
          <div>
            <h2 class="text-base font-semibold text-gray-900">{{ user.name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ user.email }}</p>
            <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-md text-xs font-semibold"
              :class="user.role === 'admin'
                ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-200'
                : 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200'">
              {{ user.role === 'admin' ? 'Administrator' : 'User' }}
            </span>
          </div>
          <div class="ml-auto text-right">
            <p class="text-xs text-gray-400">Bergabung sejak</p>
            <p class="text-sm font-medium text-gray-600 mt-0.5">{{ fmtDate(user.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Edit Info -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-900">Informasi Akun</h3>
          <p class="text-xs text-gray-400 mt-0.5">Perbarui nama dan email kamu</p>
        </div>
        <form @submit.prevent="submitInfo" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nama Lengkap</label>
            <input v-model="infoForm.name" type="text"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="infoForm.errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="infoForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ infoForm.errors.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
            <input v-model="infoForm.email" type="email"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="infoForm.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="infoForm.errors.email" class="mt-1.5 text-xs text-red-500">{{ infoForm.errors.email }}</p>
          </div>
          <div class="flex items-center justify-between pt-1">
            <p v-if="infoSuccess" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              Berhasil disimpan
            </p>
            <span v-else/>
            <button type="submit" :disabled="infoForm.processing"
              class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors">
              {{ infoForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Ganti Password -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-900">Ganti Password</h3>
          <p class="text-xs text-gray-400 mt-0.5">Gunakan password yang kuat dan unik</p>
        </div>
        <form @submit.prevent="submitPassword" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Password Saat Ini</label>
            <input v-model="pwForm.current_password" type="password" autocomplete="current-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="pwForm.errors.current_password ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="pwForm.errors.current_password" class="mt-1.5 text-xs text-red-500">{{ pwForm.errors.current_password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Password Baru</label>
            <input v-model="pwForm.password" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="pwForm.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="pwForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ pwForm.errors.password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Konfirmasi Password Baru</label>
            <input v-model="pwForm.password_confirmation" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition border-gray-200 bg-gray-50/50 focus:bg-white"/>
          </div>
          <div class="flex items-center justify-between pt-1">
            <p v-if="pwSuccess" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
              Password berhasil diubah
            </p>
            <span v-else/>
            <button type="submit" :disabled="pwForm.processing"
              class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors">
              {{ pwForm.processing ? 'Menyimpan...' : 'Ganti Password' }}
            </button>
          </div>
        </form>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ user: Object });

const infoSuccess = ref(false);
const pwSuccess = ref(false);

const infoForm = useForm({
  name:  props.user.name,
  email: props.user.email,
});

const pwForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
});

function submitInfo() {
  infoForm.patch(route('profile.update'), {
    onSuccess: () => {
      infoSuccess.value = true;
      setTimeout(() => infoSuccess.value = false, 3000);
    },
  });
}

function submitPassword() {
  pwForm.patch(route('profile.password'), {
    onSuccess: () => {
      pwForm.reset();
      pwSuccess.value = true;
      setTimeout(() => pwSuccess.value = false, 3000);
    },
  });
}

const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
  : '—';
</script>
