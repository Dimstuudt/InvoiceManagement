<template>
  <div class="flex h-screen overflow-hidden bg-slate-200">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 flex flex-col flex-shrink-0">
      <!-- Logo -->
      <div class="h-16 flex items-center px-6 border-b border-gray-700">
        <span class="text-lg font-bold text-white tracking-tight">
          Invoice<span class="text-indigo-400">Pro</span>
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto scroll-dark">
        <!-- Dashboard -->
        <Link
          :href="route('dashboard')"
          :class="navClass(route('dashboard'))"
        >
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </Link>

        <!-- Invoices -->
        <Link :href="route('invoices.index')" :class="navClass(route('invoices.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Invoices
        </Link>

        <!-- Clients -->
        <Link :href="route('clients.index')" :class="navClass(route('clients.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Clients
        </Link>

        <!-- Master Data -->
        <div class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Master Data</p>
        </div>

        <Link :href="route('master.client-categories.index')" :class="navClass(route('master.client-categories.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
          Client Categories
        </Link>

        <Link :href="route('master.project-categories.index')" :class="navClass(route('master.project-categories.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Project Categories
        </Link>

        <Link :href="route('master.document-issuers.index')" :class="navClass(route('master.document-issuers.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Document Issuers
        </Link>

        <Link :href="route('master.bank-accounts.index')" :class="navClass(route('master.bank-accounts.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          Bank Accounts
        </Link>

        <Link :href="route('master.signatures.index')" :class="navClass(route('master.signatures.index'))">
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
          Signatures
        </Link>

        <!-- Admin Section -->
        <div v-if="$page.props.auth.user.role === 'admin'" class="pt-4 pb-2">
          <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</p>
        </div>

        <Link
          v-if="$page.props.auth.user.role === 'admin'"
          :href="route('users.index')"
          :class="navClass(route('users.index'))"
        >
          <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          Users
        </Link>
      </nav>

      <!-- User info -->
      <div class="border-t border-gray-700 p-4">
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
            {{ userInitial }}
          </div>
          <div class="ml-3 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
            <p class="text-xs text-gray-400 capitalize">{{ $page.props.auth.user.role }}</p>
          </div>
        </div>
        <button
          @click="logout"
          class="mt-3 w-full flex items-center text-sm text-gray-400 hover:text-white transition-colors duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center px-6 flex-shrink-0">
        <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>

        <!-- Flash messages -->
        <div v-if="$page.props.flash.success" class="ml-auto">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
            {{ $page.props.flash.success }}
          </span>
        </div>
        <div v-if="$page.props.flash.error" class="ml-auto">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">
            {{ $page.props.flash.error }}
          </span>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6 bg-slate-100 scroll-light">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
});

const page = usePage();

const userInitial = computed(() =>
  page.props.auth.user?.name?.charAt(0).toUpperCase() ?? '?'
);

function logout() {
  router.post(route('logout'));
}

const navBase = 'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150';
const navActive = `${navBase} bg-indigo-600 text-white`;
const navInactive = `${navBase} text-gray-300 hover:bg-gray-800 hover:text-white`;
const navDisabled = `${navBase} text-gray-500 cursor-not-allowed`;

function navClass(routeUrl) {
  return page.url.startsWith(new URL(routeUrl).pathname) ? navActive : navInactive;
}
</script>
