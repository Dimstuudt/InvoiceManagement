<template>
  <AppLayout title="Profil Saya">
    <div class="max-w-2xl mx-auto space-y-5">

      <!-- Hero card: avatar + info -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Top accent strip -->
        <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-400"/>

        <div class="p-6 flex items-center gap-5">
          <!-- Avatar -->
          <div class="relative group shrink-0 cursor-pointer" @click="triggerAvatarInput">
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-emerald-600 flex items-center justify-center ring-4 ring-emerald-50">
              <img v-if="avatarPreview || user.avatar_url"
                :src="avatarPreview || user.avatar_url"
                class="w-full h-full object-cover" alt="avatar"/>
              <span v-else class="text-3xl font-bold text-white select-none">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="absolute inset-0 rounded-2xl bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <input ref="avatarInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onAvatarSelected"/>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h2 class="text-base font-bold text-gray-900 truncate">{{ user.name }}</h2>
                <p class="text-sm text-gray-400 truncate mt-0.5">{{ user.email }}</p>
              </div>
              <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                :class="user.role === 'admin'
                  ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-200'
                  : 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200'">
                {{ user.role === 'admin' ? 'Administrator' : 'User' }}
              </span>
            </div>

            <div class="mt-3 flex items-center gap-4 text-xs text-gray-400">
              <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Bergabung {{ fmtDate(user.created_at) }}
              </span>
            </div>

            <!-- Avatar actions -->
            <div v-if="avatarFile" class="flex items-center gap-2 mt-3">
              <button @click="uploadAvatar" :disabled="avatarForm.processing"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-xs font-semibold rounded-lg transition-colors">
                {{ avatarForm.processing ? 'Menyimpan...' : 'Simpan Foto' }}
              </button>
              <button @click="cancelAvatar"
                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition-colors">
                Batal
              </button>
            </div>
            <p v-else class="text-xs text-gray-400 mt-2">Klik foto untuk mengganti</p>
          </div>
        </div>
      </div>

      <!-- Informasi Akun -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900">Informasi Akun</h3>
            <p class="text-xs text-gray-400">Perbarui nama dan email</p>
          </div>
        </div>
        <form @submit.prevent="submitInfo" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nama Lengkap</label>
            <input v-model="infoForm.name" type="text"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition"
              :class="infoForm.errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="infoForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ infoForm.errors.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
            <input v-model="infoForm.email" type="email"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition"
              :class="infoForm.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="infoForm.errors.email" class="mt-1.5 text-xs text-red-500">{{ infoForm.errors.email }}</p>
          </div>
          <div class="flex items-center justify-between pt-1">
            <transition name="fade">
              <p v-if="infoSuccess" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Berhasil disimpan
              </p>
            </transition>
            <span/>
            <button type="submit" :disabled="infoForm.processing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors">
              {{ infoForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Ganti Password -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900">Ganti Password</h3>
            <p class="text-xs text-gray-400">Gunakan password yang kuat dan unik</p>
          </div>
        </div>
        <form @submit.prevent="submitPassword" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Password Saat Ini</label>
            <input v-model="pwForm.current_password" type="password" autocomplete="current-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition"
              :class="pwForm.errors.current_password ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="pwForm.errors.current_password" class="mt-1.5 text-xs text-red-500">{{ pwForm.errors.current_password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Password Baru</label>
            <input v-model="pwForm.password" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition"
              :class="pwForm.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50/50 focus:bg-white'"/>
            <p v-if="pwForm.errors.password" class="mt-1.5 text-xs text-red-500">{{ pwForm.errors.password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Konfirmasi Password Baru</label>
            <input v-model="pwForm.password_confirmation" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition border-gray-200 bg-gray-50/50 focus:bg-white"/>
          </div>
          <div class="flex items-center justify-between pt-1">
            <transition name="fade">
              <p v-if="pwSuccess" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Password berhasil diubah
              </p>
            </transition>
            <span/>
            <button type="submit" :disabled="pwForm.processing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors">
              {{ pwForm.processing ? 'Menyimpan...' : 'Ganti Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Keamanan 2FA -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-gray-900">Autentikasi 2 Langkah</h3>
            <p class="text-xs text-gray-400">Keamanan tambahan via Google Authenticator</p>
          </div>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold"
            :class="user.has_2fa
              ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200'
              : 'bg-red-50 text-red-500 ring-1 ring-red-200'">
            <span class="w-1.5 h-1.5 rounded-full"
              :class="user.has_2fa ? 'bg-emerald-500' : 'bg-red-400'"/>
            {{ user.has_2fa ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>

        <div class="p-6">
          <!-- Status aktif -->
          <div v-if="user.has_2fa">
            <p class="text-sm text-gray-500 mb-5">
              2FA aktif pada akun ini. Setiap login akan memerlukan kode dari aplikasi Google Authenticator.
              Untuk menonaktifkan atau mengganti perangkat, konfirmasi dengan kode dari aplikasi.
            </p>

            <!-- Form disable -->
            <div v-if="!showDisableForm">
              <button @click="showDisableForm = true"
                class="px-4 py-2 border border-red-200 text-red-500 hover:bg-red-50 text-sm font-semibold rounded-xl transition-colors">
                Nonaktifkan 2FA
              </button>
            </div>

            <div v-else class="space-y-4">
              <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-xs text-amber-700 leading-relaxed">
                  Setelah dinonaktifkan, kamu akan langsung diarahkan ke halaman setup 2FA dan wajib mengaktifkannya kembali sebelum bisa mengakses aplikasi.
                </p>
              </div>

              <form @submit.prevent="submitDisable" class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Kode Google Authenticator
                  </label>
                  <input
                    v-model="disableForm.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    maxlength="8"
                    autofocus
                    class="w-full px-4 py-2.5 rounded-xl border text-sm text-center font-mono font-bold tracking-widest focus:outline-none focus:ring-2 focus:ring-red-400/30 focus:border-red-400 transition bg-gray-50/50 focus:bg-white"
                    :class="disableForm.errors['2fa_code'] ? 'border-red-400' : 'border-gray-200'"
                  />
                  <p v-if="disableForm.errors['2fa_code']" class="mt-1.5 text-xs text-red-500">
                    {{ disableForm.errors['2fa_code'] }}
                  </p>
                </div>
                <div class="flex items-center gap-2">
                  <button type="submit" :disabled="disableForm.processing || disableForm.code.length < 6"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ disableForm.processing ? 'Memproses...' : 'Konfirmasi Nonaktifkan' }}
                  </button>
                  <button type="button" @click="cancelDisable"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-xl transition-colors">
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Status nonaktif (shouldn't normally reach here) -->
          <div v-else class="flex items-center justify-between">
            <p class="text-sm text-gray-500">2FA belum diaktifkan pada akun ini.</p>
            <a :href="route('2fa.setup')"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors">
              Aktifkan Sekarang
            </a>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ user: Object })

// ── Avatar ──────────────────────────────────────────────
const avatarInput   = ref(null)
const avatarFile    = ref(null)
const avatarPreview = ref(null)
const avatarForm    = useForm({ avatar: null })

function triggerAvatarInput() { avatarInput.value?.click() }

function onAvatarSelected(e) {
  const file = e.target.files[0]
  if (!file) return
  avatarFile.value    = file
  avatarPreview.value = URL.createObjectURL(file)
  avatarForm.avatar   = file
}

function uploadAvatar() {
  avatarForm.post(route('profile.avatar'), {
    forceFormData: true,
    onSuccess: () => {
      avatarFile.value    = null
      avatarPreview.value = null
      avatarForm.reset()
    },
  })
}

function cancelAvatar() {
  avatarFile.value    = null
  avatarPreview.value = null
  avatarForm.reset()
  if (avatarInput.value) avatarInput.value.value = ''
}

// ── Info ─────────────────────────────────────────────────
const infoSuccess = ref(false)
const infoForm    = useForm({ name: props.user.name, email: props.user.email })

function submitInfo() {
  infoForm.patch(route('profile.update'), {
    onSuccess: () => {
      infoSuccess.value = true
      setTimeout(() => infoSuccess.value = false, 3000)
    },
  })
}

// ── Password ──────────────────────────────────────────────
const pwSuccess = ref(false)
const pwForm    = useForm({ current_password: '', password: '', password_confirmation: '' })

function submitPassword() {
  pwForm.patch(route('profile.password'), {
    onSuccess: () => {
      pwForm.reset()
      pwSuccess.value = true
      setTimeout(() => pwSuccess.value = false, 3000)
    },
  })
}

// ── 2FA Disable ───────────────────────────────────────────
const showDisableForm = ref(false)
const disableForm     = useForm({ code: '' })

function submitDisable() {
  disableForm.delete(route('2fa.disable'), {
    data: { code: disableForm.code },
    onError: () => { disableForm.reset('code') },
  })
}

function cancelDisable() {
  showDisableForm.value = false
  disableForm.reset()
}

// ── Helpers ───────────────────────────────────────────────
const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
  : '—'
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
