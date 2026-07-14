<template>
  <div
    @mousemove="handleMouseMove"
    class="flex flex-col lg:flex-row w-full min-h-screen lg:h-screen bg-white font-sans lg:overflow-hidden"
  >

    <!-- LEFT PANEL -->
    <div class="hidden lg:flex lg:w-[55%] relative bg-[#06111f] overflow-hidden">
      <!-- Background image parallax -->
      <div
        class="absolute -inset-[5%] w-[110%] h-[110%] bg-cover bg-center opacity-10 mix-blend-overlay transition-transform duration-300 ease-out"
        :style="{ transform: `translate(${mouseX * -1}px, ${mouseY * -1}px)`, backgroundImage: `url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1672&auto=format&fit=crop')` }"
      />
      <!-- Glow blobs -->
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-sky-500 rounded-full mix-blend-screen filter blur-[100px] opacity-25 animate-pulse-slow"/>
      <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-500 rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse-slow" style="animation-delay:2s"/>
      <div class="absolute top-[40%] right-[10%] w-[25%] h-[25%] bg-indigo-400 rounded-full mix-blend-screen filter blur-[80px] opacity-15 animate-pulse-slow" style="animation-delay:4s"/>
      <!-- Overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-[#06111f]/97 via-[#06111f]/80 to-transparent"/>

      <!-- Content -->
      <div class="relative z-10 flex flex-col justify-between h-full p-16 2xl:p-24 w-full">
        <!-- Logo -->
        <div
          class="flex items-center gap-3 transition-all duration-700 delay-100"
          :class="isLoaded ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0'"
        >
          <img src="/iconAutoInvoice.png" class="h-10 w-10 object-contain" alt="Auto Invoice">
          <span class="text-2xl font-extrabold tracking-tight bg-gradient-to-r from-sky-400 via-blue-300 to-indigo-400 bg-clip-text text-transparent" style="font-family:'Open Sans',sans-serif;font-weight:800;">Auto Invoice</span>
        </div>

        <!-- Hero text + features -->
        <div
          class="mb-10 transition-all duration-700 delay-300"
          :class="isLoaded ? 'translate-x-0 opacity-100' : '-translate-x-10 opacity-0'"
        >
          <h2 class="text-5xl 2xl:text-6xl font-extrabold text-white leading-[1.1] mb-6">
            Kelola Invoice<br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-300">Lebih Mudah</span>
          </h2>

          <!-- Feature carousel -->
          <div class="h-28 mt-8 relative w-full max-w-lg">
            <transition-group name="fade" tag="div">
              <div
                v-for="(feat, i) in features"
                :key="i"
                v-show="activeFeature === i"
                class="absolute top-0 left-0 w-full"
              >
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0 mt-1 w-8 h-8 rounded-full bg-sky-500/20 border border-sky-500/40 flex items-center justify-center">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-white mb-2">{{ feat.title }}</h3>
                    <p class="text-slate-400 text-base leading-relaxed">{{ feat.desc }}</p>
                  </div>
                </div>
              </div>
            </transition-group>
          </div>

          <!-- Dots -->
          <div class="flex gap-2 mt-6">
            <div
              v-for="(_, i) in features"
              :key="'dot'+i"
              class="h-1 rounded-full transition-all duration-500"
              :class="activeFeature === i ? 'w-8 bg-sky-500' : 'w-2 bg-slate-700'"
            />
          </div>
        </div>

        <!-- Footer -->
        <div
          class="flex items-center gap-4 text-slate-500 text-sm font-medium transition-all duration-700 delay-500"
          :class="isLoaded ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'"
        >
          <span>Auto Invoice</span>
          <span class="w-1 h-1 rounded-full bg-slate-600"/>
          <span>Secure Access</span>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="w-full lg:w-[45%] lg:h-full flex flex-col lg:items-center lg:justify-center relative bg-white overflow-y-auto lg:overflow-hidden">
      <!-- Dot grid (desktop only) -->
      <div class="hidden lg:block absolute inset-0 opacity-[0.025] pointer-events-none" style="background-image:radial-gradient(#0ea5e9 1px,transparent 1px);background-size:24px 24px;"/>

      <!-- Mobile hero -->
      <div class="lg:hidden relative bg-[#06111f] overflow-hidden px-6 pt-12 pb-10">
        <div class="absolute top-[-30%] left-[-20%] w-[50%] h-[150%] bg-sky-500 rounded-full filter blur-[80px] opacity-20"/>
        <div class="absolute bottom-[-50%] right-[-10%] w-[60%] h-[150%] bg-blue-500 rounded-full filter blur-[80px] opacity-15"/>
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-6">
            <img src="/iconAutoInvoice.png" class="h-8 w-8 object-contain" alt="Auto Invoice">
            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-sky-400 to-blue-300 bg-clip-text text-transparent" style="font-family:'Open Sans',sans-serif;font-weight:800;">Auto Invoice</span>
          </div>
          <h2 class="text-2xl font-extrabold text-white leading-snug mb-2">
            Kelola Invoice<br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-300">Lebih Mudah</span>
          </h2>
          <p class="text-slate-400 text-sm">Buat, kirim & pantau invoice profesional dari mana saja.</p>
        </div>
      </div>

      <!-- Form wrapper -->
      <div class="w-full max-w-[420px] mx-auto px-6 pt-8 pb-10 lg:py-0 relative z-10 lg:flex lg:flex-col lg:justify-center lg:h-full">

        <!-- Heading -->
        <div class="mb-8 stagger-1">
          <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk</h1>
          <p class="text-slate-500 mt-1 text-sm font-medium">Masukkan kredensial Anda untuk akses sistem.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <!-- Email -->
          <div class="group stagger-2">
            <label class="block text-slate-700 font-bold text-[11px] uppercase tracking-wide ml-1 mb-1.5 transition-colors group-focus-within:text-sky-600">
              Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-500 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <input
                type="email"
                v-model="form.email"
                autocomplete="email"
                placeholder="nama@perusahaan.com"
                required
                autofocus
                class="block w-full pl-11 pr-4 py-3.5 rounded-xl border-2 text-sm bg-slate-50 focus:bg-white focus:ring-0 transition-all duration-300 outline-none"
                :class="form.errors.email ? 'border-red-400' : 'border-slate-100 focus:border-sky-400'"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 ml-1">{{ form.errors.email }}</p>
          </div>

          <!-- Password -->
          <div class="group stagger-3">
            <label class="block text-slate-700 font-bold text-[11px] uppercase tracking-wide ml-1 mb-1.5 transition-colors group-focus-within:text-sky-600">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-500 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                autocomplete="current-password"
                placeholder="••••••••"
                required
                class="block w-full pl-11 pr-12 py-3.5 rounded-xl border-2 border-slate-100 text-sm bg-slate-50 focus:bg-white focus:border-sky-400 focus:ring-0 transition-all duration-300 outline-none"
              />
              <button type="button" @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-sky-500 transition-colors">
                <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Remember -->
          <div class="flex items-center stagger-4">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="checkbox" v-model="form.remember"
                class="w-4 h-4 rounded border-2 border-slate-200 text-sky-500 focus:ring-0 cursor-pointer"/>
              <span class="text-xs text-slate-500 font-bold group-hover:text-slate-900 transition-colors">Ingat Saya</span>
            </label>
          </div>

          <!-- reCAPTCHA -->
          <div class="stagger-5 py-1 overflow-hidden">
            <div class="g-recaptcha scale-[0.88] origin-left" :data-sitekey="captchaSiteKey"/>
            <p v-if="form.errors.g_recaptcha_response" class="mt-1.5 text-xs text-red-500 ml-1">
              {{ form.errors.g_recaptcha_response }}
            </p>
          </div>

          <!-- Submit -->
          <div class="stagger-6 pt-1">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center items-center gap-2 py-4 text-xs font-black uppercase tracking-widest text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 rounded-xl shadow-lg shadow-sky-200/80 transition-all duration-300 active:scale-95 disabled:opacity-60"
            >
              <span v-if="!form.processing">Masuk ke Sistem</span>
              <svg v-else class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
              </svg>
            </button>
          </div>

        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineProps({ captchaSiteKey: String });

const showPassword  = ref(false);
const mouseX        = ref(0);
const mouseY        = ref(0);
const activeFeature = ref(0);
const isLoaded      = ref(false);

const features = [
  { title: 'Invoice Profesional', desc: 'Buat dan kirim invoice berpenampilan profesional dengan mudah ke seluruh klien kamu.' },
  { title: 'Lacak Status Pembayaran', desc: 'Pantau invoice yang sudah dibayar, jatuh tempo, atau belum diproses secara real-time.' },
  { title: 'Laporan & Statistik', desc: 'Analisa performa bisnis dengan ringkasan outstanding, total invoice, dan data per klien.' },
];

const form = useForm({
  email:                '',
  password:             '',
  remember:             false,
  g_recaptcha_response: '',
});

const handleMouseMove = (e) => {
  mouseX.value = (e.clientX / window.innerWidth  - 0.5) * 20;
  mouseY.value = (e.clientY / window.innerHeight - 0.5) * 20;
};

let featureTimer;

onMounted(() => {
  setTimeout(() => { isLoaded.value = true; }, 100);
  featureTimer = setInterval(() => {
    activeFeature.value = (activeFeature.value + 1) % features.length;
  }, 4000);

  if (!document.getElementById('recaptcha-script')) {
    const s = document.createElement('script');
    s.id    = 'recaptcha-script';
    s.src   = 'https://www.google.com/recaptcha/api.js';
    s.async = true;
    s.defer = true;
    document.head.appendChild(s);
  }
});

onUnmounted(() => clearInterval(featureTimer));

function submit() {
  form.g_recaptcha_response = window.grecaptcha?.getResponse() ?? '';
  form.post(route('login'), {
    onFinish: () => {
      form.reset('password');
      window.grecaptcha?.reset();
    },
    onError: (errors) => {
      if (errors.g_recaptcha_response) {
        Swal.fire({
          icon: 'warning',
          title: 'Verifikasi Diperlukan',
          text: errors.g_recaptcha_response,
          confirmButtonColor: '#0ea5e9',
          confirmButtonText: 'OK',
        });
      } else if (errors.email) {
        Swal.fire({
          icon: 'error',
          title: 'Login Gagal',
          text: errors.email,
          confirmButtonColor: '#0ea5e9',
          confirmButtonText: 'Coba Lagi',
        });
      }
    },
  });
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.6s ease; }
.fade-enter-from { opacity: 0; transform: translateY(10px); }
.fade-leave-to   { opacity: 0; transform: translateY(-10px); }

@keyframes pulse-slow {
  0%, 100% { transform: scale(1);    opacity: 0.2; }
  50%       { transform: scale(1.15); opacity: 0.35; }
}
.animate-pulse-slow { animation: pulse-slow 10s infinite alternate ease-in-out; }

.stagger-1, .stagger-2, .stagger-3,
.stagger-4, .stagger-5, .stagger-6 {
  animation: slideIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  opacity: 0;
}
.stagger-1 { animation-delay: 0.2s; }
.stagger-2 { animation-delay: 0.3s; }
.stagger-3 { animation-delay: 0.4s; }
.stagger-4 { animation-delay: 0.5s; }
.stagger-5 { animation-delay: 0.6s; }
.stagger-6 { animation-delay: 0.7s; }

@keyframes slideIn {
  from { opacity: 0; transform: translateY(30px); }
  to   { opacity: 1; transform: translateY(0); }
}

input:focus {
  box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.1), 0 4px 6px -2px rgba(14, 165, 233, 0.05) !important;
}
</style>
