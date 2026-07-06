<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

        <!-- Header -->
        <div class="text-center mb-7">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 mb-4">
            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <h1 class="text-xl font-extrabold text-slate-900">Verifikasi 2FA</h1>
          <p class="text-slate-500 text-sm mt-1">Masukkan kode dari Google Authenticator</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit">
          <div class="group">
            <input
              v-model="form.code"
              type="text"
              inputmode="numeric"
              autocomplete="one-time-code"
              placeholder="000000"
              maxlength="8"
              autofocus
              class="block w-full px-4 py-4 rounded-xl border-2 text-center text-3xl font-mono font-bold tracking-[0.5em] bg-slate-50 focus:bg-white transition-all duration-200 outline-none placeholder:text-slate-300 placeholder:tracking-[0.4em]"
              :class="form.errors.code ? 'border-red-400' : 'border-slate-100 focus:border-emerald-500'"
            />
            <p v-if="form.errors.code" class="mt-2 text-xs text-red-500 text-center">{{ form.errors.code }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || form.code.length < 6"
            class="mt-5 w-full flex justify-center items-center gap-2 py-4 text-xs font-black uppercase tracking-widest text-white bg-emerald-600 hover:bg-slate-900 rounded-xl shadow-lg shadow-emerald-100 transition-all duration-300 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span v-else>Verifikasi &amp; Masuk</span>
          </button>
        </form>

        <!-- Help text -->
        <div class="mt-5 pt-5 border-t border-slate-50">
          <p class="text-center text-xs text-slate-400">
            Buka aplikasi <span class="font-medium text-slate-600">Google Authenticator</span> di ponsel Anda untuk mendapatkan kode 6 digit.
          </p>
        </div>

      </div>

      <!-- Logout link -->
      <div class="text-center mt-5">
        <form method="POST" :action="route('logout')" class="inline">
          <input type="hidden" name="_token" :value="$page.props.csrf_token ?? ''">
          <button type="button" @click="logout"
            class="text-xs text-slate-400 hover:text-slate-700 transition-colors font-medium">
            Keluar dari akun ini
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({ code: '' })

function submit() {
  form.post(route('2fa.verify'), {
    onError: () => { form.reset('code') },
  })
}

function logout() {
  router.post(route('logout'))
}
</script>
