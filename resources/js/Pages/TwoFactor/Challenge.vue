<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-sm card-enter">

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Accent strip -->
        <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-400"/>

        <div class="p-8">
          <!-- Shield -->
          <div class="flex justify-center mb-5">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
              <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
          </div>

          <!-- Heading -->
          <div class="text-center mb-7">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verifikasi 2FA</h1>
            <p class="text-slate-400 text-sm mt-1">Masukkan kode 6 digit dari Google Authenticator</p>
          </div>

          <form @submit.prevent="submit">
            <!-- Segmented digit boxes -->
            <div
              class="relative flex gap-2 justify-center mb-5 cursor-text"
              :class="{ 'shake': hasError }"
              @click="focusInput"
            >
              <div
                v-for="i in 6"
                :key="i"
                class="w-11 rounded-xl border-2 flex items-center justify-center text-xl font-mono font-bold transition-all duration-150 select-none"
                style="height: 3.25rem;"
                :class="boxStyle(i)"
              >
                <span v-if="form.code[i - 1]">{{ form.code[i - 1] }}</span>
                <span
                  v-else-if="form.code.length === i - 1 && isFocused"
                  class="w-px h-5 bg-emerald-500 cursor-blink"
                />
              </div>

              <!-- Invisible real input -->
              <input
                ref="codeInput"
                v-model="form.code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                autofocus
                class="absolute inset-0 w-full h-full opacity-0 cursor-text"
                @focus="isFocused = true"
                @blur="isFocused = false"
                @input="onInput"
              />
            </div>

            <!-- Error -->
            <p v-if="form.errors.code" class="text-center text-xs text-red-500 mb-4 -mt-1">
              {{ form.errors.code }}
            </p>

            <!-- Button -->
            <button
              type="submit"
              :disabled="form.processing || form.code.length < 6"
              class="w-full flex justify-center items-center gap-2 py-3.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 active:scale-[0.98]"
              :class="form.code.length === 6 && !form.processing
                ? 'bg-emerald-600 hover:bg-slate-900 text-white shadow-lg shadow-emerald-100'
                : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
            >
              <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
              <span v-else>Verifikasi &amp; Masuk</span>
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-6 pt-5 border-t border-slate-50 text-center">
            <p class="text-xs text-slate-400 leading-relaxed">
              Kode diperbarui setiap 30 detik di aplikasi
              <span class="font-medium text-slate-500">Google Authenticator</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Logout -->
      <div class="text-center mt-5">
        <button
          type="button"
          @click="logout"
          class="text-xs text-slate-400 hover:text-slate-700 transition-colors font-medium"
        >
          Keluar dari akun ini
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const form      = useForm({ code: '' })
const codeInput = ref(null)
const isFocused = ref(false)
const hasError  = ref(false)

function focusInput() {
  codeInput.value?.focus()
}

function onInput() {
  form.code = form.code.replace(/\D/g, '').slice(0, 6)
  if (form.code.length === 6) {
    setTimeout(submit, 120)
  }
}

function boxStyle(i) {
  const idx    = i - 1
  const filled = !!form.code[idx]
  const active = form.code.length === idx && isFocused.value

  if (form.errors.code) return 'border-red-300 bg-red-50 text-red-600'
  if (active)  return 'border-emerald-500 bg-white text-slate-900 shadow-[0_0_0_3px_rgba(16,185,129,0.12)]'
  if (filled)  return 'border-emerald-300 bg-emerald-50 text-slate-900'
  return 'border-slate-200 bg-slate-50 text-transparent'
}

function submit() {
  if (form.code.length < 6 || form.processing) return
  form.post(route('2fa.verify'), {
    onError: () => {
      hasError.value = true
      setTimeout(() => { hasError.value = false }, 600)
      form.reset('code')
      setTimeout(focusInput, 50)
    },
  })
}

watch(() => form.errors.code, (val) => {
  if (val) {
    hasError.value = true
    setTimeout(() => { hasError.value = false }, 600)
  }
})

function logout() {
  router.post(route('logout'))
}
</script>

<style scoped>
.card-enter {
  animation: cardIn 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes cardIn {
  from { opacity: 0; transform: translateY(16px) scale(0.98); }
  to   { opacity: 1; transform: translateY(0)    scale(1);    }
}

.cursor-blink {
  animation: blink 1s step-end infinite;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0; }
}

.shake {
  animation: shake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
}
@keyframes shake {
  10%, 90%      { transform: translateX(-2px); }
  20%, 80%      { transform: translateX(4px);  }
  30%, 50%, 70% { transform: translateX(-5px); }
  40%, 60%      { transform: translateX(5px);  }
}
</style>
