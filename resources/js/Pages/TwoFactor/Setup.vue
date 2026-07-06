<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

        <!-- Header -->
        <div class="text-center mb-7">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 mb-4">
            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </div>
          <h1 class="text-xl font-extrabold text-slate-900">Aktifkan Autentikasi 2 Langkah</h1>
          <p class="text-slate-500 text-sm mt-1">Scan QR code dengan Google Authenticator</p>
        </div>

        <!-- Steps -->
        <div class="space-y-5">

          <!-- Step 1 -->
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center mt-0.5">1</div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-slate-700 mb-1">Install Google Authenticator</p>
              <p class="text-xs text-slate-500">Unduh aplikasi di <span class="font-medium text-slate-700">App Store</span> atau <span class="font-medium text-slate-700">Google Play</span>.</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center mt-0.5">2</div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-slate-700 mb-3">Scan QR Code berikut</p>
              <!-- QR Code -->
              <div class="flex justify-center">
                <div class="p-3 bg-white border-2 border-slate-100 rounded-xl inline-block shadow-sm">
                  <img :src="qrCode" alt="QR Code 2FA" class="w-44 h-44 block"/>
                </div>
              </div>
              <!-- Manual key -->
              <div class="mt-3">
                <p class="text-xs text-slate-500 text-center mb-1.5">Atau masukkan kode manual:</p>
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                  <code class="flex-1 text-xs font-mono text-slate-700 tracking-widest select-all break-all">{{ secret }}</code>
                  <button type="button" @click="copySecret"
                    class="flex-shrink-0 text-slate-400 hover:text-emerald-600 transition-colors">
                    <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex gap-4">
            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center mt-0.5">3</div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-slate-700 mb-3">Konfirmasi kode dari aplikasi</p>

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
                    class="block w-full px-4 py-3 rounded-xl border-2 text-center text-2xl font-mono font-bold tracking-[0.4em] bg-slate-50 focus:bg-white transition-all duration-200 outline-none placeholder:text-slate-300 placeholder:tracking-[0.4em]"
                    :class="form.errors.code ? 'border-red-400' : 'border-slate-100 focus:border-emerald-500'"
                  />
                  <p v-if="form.errors.code" class="mt-1.5 text-xs text-red-500 text-center">{{ form.errors.code }}</p>
                </div>

                <button
                  type="submit"
                  :disabled="form.processing || form.code.length < 6"
                  class="mt-4 w-full flex justify-center items-center gap-2 py-3.5 text-xs font-black uppercase tracking-widest text-white bg-emerald-600 hover:bg-slate-900 rounded-xl shadow-lg shadow-emerald-100 transition-all duration-300 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                  </svg>
                  <span v-else>Aktifkan 2FA</span>
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>

      <!-- Footer note -->
      <p class="text-center text-xs text-slate-400 mt-5">
        2FA wajib diaktifkan untuk keamanan akun Anda.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  qrCode: String,
  secret: String,
})

const copied = ref(false)

const form = useForm({ code: '' })

function copySecret() {
  navigator.clipboard.writeText(props.secret)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

function submit() {
  form.post(route('2fa.enable'), {
    onError: () => { form.reset('code') },
  })
}
</script>
