<template>
  <div class="flex h-screen overflow-hidden bg-slate-100">

    <!-- Mobile overlay backdrop -->
    <transition name="fade-overlay">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="sidebarOpen = false"
      />
    </transition>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-6 border-b border-gray-700 flex-shrink-0">
        <span class="text-lg font-bold text-white tracking-tight">
          Invoice<span class="text-indigo-400">Pro</span>
        </span>
        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-gray-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto scroll-dark">

        <Link :href="route('dashboard')" :class="navClass(route('dashboard'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Dashboard
        </Link>

        <Link :href="route('invoices.index')" :class="navClass(route('invoices.index'), ['/invoices/schedule', '/invoices/broadcast', '/invoices/clients', '/invoices/numbering'])" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Client Invoice
        </Link>

        <Link :href="route('invoices.schedule')" :class="navClass(route('invoices.schedule'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
          </svg>
          Email Schedule Invoice
        </Link>

        <Link :href="route('invoices.numbering')" :class="navClass(route('invoices.numbering'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
          </svg>
          Urutan Nomor
        </Link>

        <Link :href="route('spk.index')" :class="navClass(route('spk.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          SPK Parser
        </Link>

        <Link :href="route('clients.index')" :class="navClass(route('clients.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Clients
        </Link>

        <!-- Master Data -->
        <div class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</p>
        </div>

        <Link :href="route('master.client-categories.index')" :class="navClass(route('master.client-categories.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
          Client Categories
        </Link>

        <Link :href="route('master.project-categories.index')" :class="navClass(route('master.project-categories.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Project Categories
        </Link>

        <Link :href="route('master.document-issuers.index')" :class="navClass(route('master.document-issuers.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Document Issuers
        </Link>

        <Link :href="route('master.bank-accounts.index')" :class="navClass(route('master.bank-accounts.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          Bank Accounts
        </Link>

        <Link :href="route('master.signatures.index')" :class="navClass(route('master.signatures.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
          Signatures
        </Link>

        <Link :href="route('master.email-templates.index')" :class="navClass(route('master.email-templates.index'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Email Templates
        </Link>

        <!-- Logs -->
        <div class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem</p>
        </div>

        <Link :href="route('logs.index')" :class="navClass(route('logs.index'), ['/logs/cron'])" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
          Activity Log
        </Link>
        <Link :href="route('logs.cron')" :class="navClass(route('logs.cron'))" @click="closeSidebarOnMobile">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Log Cron
        </Link>

        <!-- Admin -->
        <template v-if="$page.props.auth.user.role === 'admin'">
          <div class="pt-4 pb-2">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</p>
          </div>
          <Link :href="route('users.index')" :class="navClass(route('users.index'))" @click="closeSidebarOnMobile">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Users
          </Link>
          <Link :href="route('admin.artisan')" :class="navClass(route('admin.artisan'))" @click="closeSidebarOnMobile">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Artisan Panel
          </Link>
          <Link :href="route('admin.cron')" :class="navClass(route('admin.cron'))" @click="closeSidebarOnMobile">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Cron Panel
          </Link>
        </template>

      </nav>

      <!-- User info -->
      <div class="border-t border-gray-700 p-4 flex-shrink-0">
        <Link :href="route('profile.show')" @click="closeSidebarOnMobile"
          class="flex items-center gap-3 rounded-xl hover:bg-gray-800 p-1.5 -mx-1.5 transition-colors group">
          <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium flex-shrink-0 overflow-hidden">
            <img v-if="$page.props.auth.user.avatar_url"
              :src="$page.props.auth.user.avatar_url"
              class="w-full h-full object-cover" alt="avatar"/>
            <span v-else>{{ userInitial }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
            <p class="text-xs text-gray-400 capitalize">{{ $page.props.auth.user.role }}</p>
          </div>
          <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-gray-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </Link>
        <button @click="logout"
          class="mt-2 w-full flex items-center text-sm text-gray-400 hover:text-white transition-colors px-1.5 py-1">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center px-4 lg:px-6 flex-shrink-0 gap-3">
        <!-- Hamburger (mobile only) -->
        <button @click="sidebarOpen = true"
          class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <h1 class="text-base lg:text-lg font-semibold text-gray-800 truncate">{{ title }}</h1>

        <!-- Flash messages -->
        <div v-if="$page.props.flash.success" class="ml-auto flex-shrink-0">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs lg:text-sm bg-green-100 text-green-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="hidden sm:inline">{{ $page.props.flash.success }}</span>
          </span>
        </div>
        <div v-if="$page.props.flash.error" class="ml-auto flex-shrink-0">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs lg:text-sm bg-red-100 text-red-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="hidden sm:inline">{{ $page.props.flash.error }}</span>
          </span>
        </div>
      </header>

      <!-- Content -->
      <main ref="mainEl" class="flex-1 overflow-y-auto p-4 lg:p-6 bg-slate-100 scroll-light">
        <slot />
      </main>

    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const mainEl = ref(null);

defineProps({ title: { type: String, default: '' } });

const page        = usePage();
const sidebarOpen = ref(false);

const userInitial = computed(() =>
  page.props.auth.user?.name?.charAt(0).toUpperCase() ?? '?'
);

// Reset scroll ke atas + tutup sidebar saat navigasi
watch(() => page.url, () => {
  sidebarOpen.value = false;
  if (mainEl.value) mainEl.value.scrollTop = 0;
});

function closeSidebarOnMobile() {
  if (window.innerWidth < 1024) sidebarOpen.value = false;
}

function logout() {
  router.post(route('logout'));
}

const navBase     = 'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150';
const navActive   = `${navBase} bg-indigo-600 text-white`;
const navInactive = `${navBase} text-gray-300 hover:bg-gray-800 hover:text-white`;

function navClass(routeUrl, exclude = []) {
  const path = new URL(routeUrl).pathname;
  if (!page.url.startsWith(path)) return navInactive;
  if (exclude.some(p => page.url.startsWith(p))) return navInactive;
  return navActive;
}
</script>

<style scoped>
.fade-overlay-enter-active,
.fade-overlay-leave-active { transition: opacity 0.25s ease; }
.fade-overlay-enter-from,
.fade-overlay-leave-to     { opacity: 0; }
</style>
