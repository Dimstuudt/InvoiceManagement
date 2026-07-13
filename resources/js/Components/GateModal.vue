<template>
  <Teleport to="body">
    <Transition name="gate-overlay">
      <div v-if="gate.isOpen" class="fixed inset-0 z-[300] overflow-y-auto">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/65 backdrop-blur-sm"
             @click="!gate.isLoading && !gate.isSuccess && cancelGate()"></div>

        <div class="flex min-h-full items-center justify-center p-4">
          <Transition name="gate-card" appear>
            <div v-if="gate.isOpen"
              class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
              style="min-height: 320px">

              <!-- ── SUCCESS STATE ── -->
              <Transition name="gate-success">
                <div v-if="gate.isSuccess"
                  class="absolute inset-0 flex flex-col items-center justify-center px-6 py-10 text-center bg-white">
                  <div class="success-circle w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2.5">
                      <path class="check-path" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <h3 class="text-base font-bold text-gray-900 mb-1">Terverifikasi!</h3>
                  <p class="text-sm text-gray-400 mb-5">Membuka halaman...</p>
                  <div class="flex justify-center gap-1.5">
                    <span v-for="i in 3" :key="i"
                      class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce"
                      :style="{ animationDelay: (i - 1) * 160 + 'ms', animationDuration: '0.8s' }"></span>
                  </div>
                </div>
              </Transition>

              <!-- ── GATE FORM ── -->
              <template v-if="!gate.isSuccess">

                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                      </div>
                      <div>
                        <h3 class="text-base font-bold text-gray-900">Verifikasi Identitas</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                          {{ has2fa ? 'Pilih metode verifikasi' : 'Masukkan password untuk melanjutkan' }}
                        </p>
                      </div>
                    </div>
                    <button v-if="!gate.isLoading" @click="cancelGate"
                      class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors">
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="px-6 py-5 space-y-4">

                  <!-- Tab switcher (only when 2FA is active) -->
                  <div v-if="has2fa" class="flex gap-1.5 bg-gray-100 p-1 rounded-xl">
                    <button @click="setMethod('2fa')"
                      class="flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all"
                      :class="gate.method === '2fa'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-500 hover:text-gray-700'">
                      Kode 2FA
                    </button>
                    <button @click="setMethod('password')"
                      class="flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all"
                      :class="gate.method === 'password'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-500 hover:text-gray-700'">
                      Password
                    </button>
                  </div>

                  <!-- Input -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                      {{ gate.method === 'password' ? 'Password Akun' : 'Kode Google Authenticator' }}
                    </label>
                    <input
                      ref="inputRef"
                      v-model="gate.credential"
                      :type="gate.method === 'password' ? 'password' : 'text'"
                      :maxlength="gate.method === 'password' ? 255 : 6"
                      :inputmode="gate.method === 'password' ? 'text' : 'numeric'"
                      :placeholder="gate.method === 'password' ? '••••••••' : '000000'"
                      :autocomplete="gate.method === 'password' ? 'current-password' : 'one-time-code'"
                      :disabled="gate.isLoading"
                      @keyup.enter="handleSubmit"
                      class="w-full border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                      :class="gate.method === '2fa'
                        ? 'text-3xl tracking-[0.5em] text-center font-mono py-3.5 px-4'
                        : 'text-sm py-2.5 px-4'"
                    />

                    <!-- Error message -->
                    <Transition name="gate-error">
                      <div v-if="gate.errorMessage"
                        class="flex items-center gap-1.5 mt-2 text-red-500 text-xs font-medium">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ gate.errorMessage }}
                      </div>
                    </Transition>
                  </div>

                  <!-- 2FA hint -->
                  <div v-if="gate.method === '2fa'"
                    class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-2.5">
                    <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-500">Kode berubah setiap 30 detik — buka Google Authenticator di HP kamu.</p>
                  </div>

                </div>

                <!-- Footer -->
                <div class="px-6 pb-6 flex gap-3">
                  <button @click="cancelGate" :disabled="gate.isLoading"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all disabled:opacity-50">
                    Batal
                  </button>
                  <button @click="handleSubmit"
                    :disabled="!gate.credential || gate.isLoading"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="gate.isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ gate.isLoading ? 'Memverifikasi...' : 'Verifikasi' }}
                  </button>
                </div>

              </template>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { gateState, cancelGate, submitGate } from '@/Composables/useSecurityGate'

const page    = usePage()
const has2fa  = computed(() => !!page.props.auth?.user?.has_2fa)
const gate    = gateState
const inputRef = ref(null)

function setMethod(method) {
    gate.method       = method
    gate.credential   = ''
    gate.errorMessage = ''
    nextTick(() => inputRef.value?.focus())
}

function handleSubmit() {
    if (!gate.credential || gate.isLoading) return
    if (gate.method === '2fa' && gate.credential.length !== 6) {
        gate.errorMessage = 'Kode 2FA harus 6 digit.'
        return
    }
    submitGate()
}

watch(() => gate.isOpen, open => {
    if (open) nextTick(() => inputRef.value?.focus())
})
</script>

<style scoped>
/* Backdrop fade */
.gate-overlay-enter-active,
.gate-overlay-leave-active { transition: opacity 0.2s ease; }
.gate-overlay-enter-from,
.gate-overlay-leave-to     { opacity: 0; }

/* Card spring */
.gate-card-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.gate-card-leave-active { transition: all 0.15s ease-in; }
.gate-card-enter-from   { transform: scale(0.9) translateY(12px); opacity: 0; }
.gate-card-leave-to     { transform: scale(0.95); opacity: 0; }

/* Success overlay fade-scale */
.gate-success-enter-active { transition: all 0.3s ease-out; }
.gate-success-leave-active { transition: all 0.15s ease-in; }
.gate-success-enter-from   { opacity: 0; transform: scale(0.96); }
.gate-success-leave-to     { opacity: 0; }

/* Error message */
.gate-error-enter-active { transition: all 0.2s ease-out; }
.gate-error-enter-from   { opacity: 0; transform: translateY(-4px); }

/* Checkmark draw */
.check-path {
    stroke-dasharray: 28;
    stroke-dashoffset: 28;
    animation: checkDraw 0.4s ease-out 0.2s forwards;
}

/* Circle scale-in */
.success-circle {
    animation: circleIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes checkDraw {
    to { stroke-dashoffset: 0; }
}

@keyframes circleIn {
    from { transform: scale(0.4); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}
</style>
