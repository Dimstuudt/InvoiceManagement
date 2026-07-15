<template>
  <div class="flex h-screen overflow-hidden bg-gray-100">

    <!-- Mobile overlay -->
    <transition name="fade-overlay">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[2px] lg:hidden"
        @click="sidebarOpen = false"
      />
    </transition>

    <!-- ── Sidebar ── -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-60 bg-gray-50 border-r border-gray-200 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="h-14 flex items-center justify-between px-4 flex-shrink-0 border-b border-gray-200">
        <div class="flex items-center gap-2.5">
          <img src="/iconAutoInvoice.png" class="w-8 h-8 object-contain flex-shrink-0" alt="Auto Invoice">
          <span class="text-[17px] leading-none tracking-tight bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-600 bg-clip-text text-transparent" style="font-family: 'Open Sans', sans-serif; font-weight: 800;">Auto Invoice</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-2.5 py-3 space-y-0.5 overflow-y-auto scroll-light">

        <Link :href="route('dashboard')" :class="navClass(route('dashboard'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Dashboard
        </Link>

        <Link :href="route('invoices.index')" :class="navClass(route('invoices.index'), ['/invoices/schedule', '/invoices/broadcast', '/invoices/clients', '/invoices/numbering'])" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Client Invoice
        </Link>

        <Link :href="route('invoices.schedule')" :class="navClass(route('invoices.schedule'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
          </svg>
          Email Schedule
        </Link>

        <Link :href="route('invoices.numbering')" :class="navClass(route('invoices.numbering'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
          </svg>
          Urutan Nomor
        </Link>

        <Link :href="route('spk.index')" :class="navClass(route('spk.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          SPK Parser
        </Link>

        <Link :href="route('clients.index')" :class="navClass(route('clients.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Clients
        </Link>

        <!-- Master Data -->
        <div class="pt-5 pb-1.5">
          <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Master Data</p>
        </div>

        <Link :href="route('master.companies.index')" :class="navClass(route('master.companies.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Perusahaan
        </Link>

        <Link :href="route('master.client-categories.index')" :class="navClass(route('master.client-categories.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
          Client Categories
        </Link>

        <Link :href="route('master.project-categories.index')" :class="navClass(route('master.project-categories.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Project Categories
        </Link>

        <Link :href="route('master.document-issuers.index')" :class="navClass(route('master.document-issuers.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Document Issuers
        </Link>

        <Link :href="route('master.bank-accounts.index')" :class="navClass(route('master.bank-accounts.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          Bank Accounts
        </Link>

        <Link :href="route('master.sender-domains.index')" :class="navClass(route('master.sender-domains.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Sender Domains
        </Link>

        <Link :href="route('master.signatures.index')" :class="navClass(route('master.signatures.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
          Signatures
        </Link>

        <Link :href="route('master.email-template-groups.index')" :class="navClass(route('master.email-template-groups.index'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Email Templates
        </Link>

        <!-- Sistem -->
        <div class="pt-5 pb-1.5">
          <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Sistem</p>
        </div>

        <Link :href="route('logs.index')" :class="navClass(route('logs.index'), ['/logs/cron'])" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
          Activity Log
        </Link>

        <Link :href="route('logs.cron')" :class="navClass(route('logs.cron'))" @click="closeSidebarOnMobile">
          <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Log Cron
        </Link>

        <!-- Admin -->
        <template v-if="$page.props.auth.user.role === 'admin'">
          <div class="pt-5 pb-1.5">
            <p class="px-3 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Admin</p>
          </div>

          <Link :href="route('users.index')" :class="navClass(route('users.index'))" @click="closeSidebarOnMobile">
            <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Users
          </Link>

          <Link :href="route('admin.artisan')" :class="navClass(route('admin.artisan'))" @click="closeSidebarOnMobile">
            <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Artisan Panel
          </Link>

          <Link :href="route('admin.cron')" :class="navClass(route('admin.cron'))" @click="closeSidebarOnMobile">
            <svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Cron Panel
          </Link>
        </template>

      </nav>

      <!-- User section -->
      <div class="border-t border-gray-200 p-3 flex-shrink-0 space-y-0.5">
        <Link :href="route('profile.show')" @click="closeSidebarOnMobile"
          class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-gray-50 transition-colors group">
          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-sm font-semibold flex-shrink-0 overflow-hidden">
            <img v-if="$page.props.auth.user.avatar_url"
              :src="$page.props.auth.user.avatar_url"
              class="w-full h-full object-cover" alt="avatar"/>
            <span v-else>{{ userInitial }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">{{ $page.props.auth.user.name }}</p>
            <p class="text-[11px] text-gray-400 capitalize leading-tight mt-0.5">{{ $page.props.auth.user.role }}</p>
          </div>
          <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </Link>

        <Link :href="route('settings.notification-emails.index')" @click="closeSidebarOnMobile"
          class="flex items-center gap-2.5 w-full px-3 py-2 rounded-lg text-[12px] text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors">
          <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          Notifikasi Email
        </Link>

        <button @click="logout"
          class="flex items-center gap-2.5 w-full px-3 py-2 rounded-lg text-[12px] text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Keluar
        </button>
      </div>
    </aside>

    <!-- ── Main ── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

      <!-- Topbar -->
      <header class="h-14 bg-white border-b border-gray-200 flex items-center px-4 lg:px-6 flex-shrink-0 gap-3">
        <button @click="sidebarOpen = true"
          class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <h1 class="text-sm font-semibold text-gray-700 truncate">{{ title }}</h1>

        <div class="ml-auto flex items-center gap-2 flex-shrink-0">

          <!-- ── Bell Notification ── -->
          <div class="relative" ref="notifRef">
            <button @click="toggleNotif"
              class="relative p-1.5 rounded-lg transition-colors"
              :class="notifOpen ? 'text-indigo-600 bg-indigo-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              <span v-if="$page.props.notifCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-0.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black leading-none">
                {{ $page.props.notifCount > 99 ? '99+' : $page.props.notifCount }}
              </span>
            </button>

            <!-- Dropdown panel -->
            <Transition name="notif-drop">
              <div v-if="notifOpen"
                class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-[100]">

                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                  <span v-if="notifData"
                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    :class="notifData.total > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400'">
                    {{ notifData.total > 0 ? notifData.total + ' perlu perhatian' : 'Semua aman' }}
                  </span>
                </div>

                <!-- Loading -->
                <div v-if="notifLoading" class="py-8 flex flex-col items-center gap-2">
                  <svg class="w-5 h-5 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <p class="text-xs text-gray-400">Memuat...</p>
                </div>

                <!-- Empty -->
                <div v-else-if="notifData && notifData.total === 0"
                  class="py-8 flex flex-col items-center gap-2 text-center px-4">
                  <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-500">Semua invoice aman</p>
                </div>

                <!-- Summary rows -->
                <div v-else-if="notifData" class="divide-y divide-gray-50">
                  <div v-if="notifData.overdue.length > 0"
                    class="flex items-center gap-3 px-4 py-3">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shrink-0"/>
                    <span class="text-sm text-gray-700 flex-1">Overdue</span>
                    <span class="text-sm font-bold text-red-500">{{ notifData.overdue.length }}</span>
                  </div>
                  <div v-if="notifData.due_today.length > 0"
                    class="flex items-center gap-3 px-4 py-3">
                    <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"/>
                    <span class="text-sm text-gray-700 flex-1">Jatuh tempo hari ini</span>
                    <span class="text-sm font-bold text-orange-500">{{ notifData.due_today.length }}</span>
                  </div>
                  <div v-if="notifData.due_soon.length > 0"
                    class="flex items-center gap-3 px-4 py-3">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"/>
                    <span class="text-sm text-gray-700 flex-1">Segera jatuh tempo</span>
                    <span class="text-sm font-bold text-amber-500">{{ notifData.due_soon.length }}</span>
                  </div>
                  <div v-if="notifData.draft_unverified?.length > 0"
                    class="flex items-center gap-3 px-4 py-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"/>
                    <span class="text-sm text-gray-700 flex-1">Draft belum diverifikasi</span>
                    <span class="text-sm font-bold text-blue-500">{{ notifData.draft_unverified.length }}</span>
                  </div>
                </div>

                <!-- Footer: Lihat Semua -->
                <div v-if="notifData && notifData.total > 0"
                  class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50">
                  <button @click="goToNotifPage"
                    class="w-full text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center justify-center gap-1.5 py-1 rounded-lg hover:bg-indigo-50 transition-colors">
                    Lihat Semua
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Bypass active indicator -->
          <div v-if="bypassActive"
            class="flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 pl-2.5 pr-1 py-1 rounded-full text-xs font-semibold">
            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse shrink-0"></span>
            <span class="hidden sm:inline">Bypass</span>
            <span class="font-mono">{{ formatBypassTime }}</span>
            <button @click="confirmDeactivateBypass"
              title="Matikan bypass"
              class="ml-0.5 w-5 h-5 rounded-full hover:bg-red-100 text-amber-600 hover:text-red-600 flex items-center justify-center transition-colors">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <button v-else @click="openBypassModal"
            title="Aktifkan Bypass Verifikasi"
            class="p-1.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
          </button>

        </div>
      </header>

      <!-- Content -->
      <main ref="mainEl" class="flex-1 overflow-y-auto p-4 lg:p-6 bg-gray-100 scroll-light">
        <slot />
      </main>

    </div>
  </div>

  <!-- Gate Modal -->
  <GateModal />

  <!-- ── Bypass Modal ── -->
  <Transition name="bypass-overlay">
    <div v-if="showBypassModal" class="fixed inset-0 z-[200] overflow-y-auto">
      <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
           @click="!isActivatingBypass && !bypassSuccess && closeBypassModal()"></div>
      <div class="flex min-h-full items-center justify-center p-4">
        <Transition name="bypass-card" appear>
          <div v-if="showBypassModal"
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
            style="min-height: 340px">

            <!-- SUCCESS -->
            <Transition name="bypass-success">
              <div v-if="bypassSuccess"
                class="absolute inset-0 flex flex-col items-center justify-center px-6 py-10 text-center bg-white">
                <div class="bypass-success-circle w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4">
                  <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path class="bypass-check-path" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Bypass Aktif!</h3>
                <p class="text-sm text-gray-400 mb-5">Gate dilewati selama {{ bypassMinutes }} menit.</p>
                <div class="flex justify-center gap-1.5">
                  <span v-for="i in 3" :key="i"
                    class="w-2 h-2 bg-amber-400 rounded-full animate-bounce"
                    :style="{ animationDelay: (i - 1) * 160 + 'ms', animationDuration: '0.8s' }"></span>
                </div>
              </div>
            </Transition>

            <!-- FORM -->
            <template v-if="!bypassSuccess">
              <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                      <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-base font-bold text-gray-900">Aktifkan Bypass</h3>
                      <p class="text-xs text-gray-400 mt-0.5">Lewati gate selama durasi yang dipilih</p>
                    </div>
                  </div>
                  <button v-if="!isActivatingBypass" @click="closeBypassModal()"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="px-6 py-5 space-y-5">
                <div>
                  <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Durasi Bypass</p>
                  <div class="flex gap-2">
                    <button v-for="opt in [{v:15,l:'15 mnt'},{v:30,l:'30 mnt'},{v:60,l:'1 jam'},{v:240,l:'4 jam'}]"
                      :key="opt.v"
                      @click="bypassMinutes = opt.v"
                      type="button"
                      class="flex-1 py-2 rounded-xl text-xs font-bold border transition-all"
                      :class="bypassMinutes === opt.v
                        ? 'bg-amber-500 text-white border-amber-500 shadow-sm'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300 hover:text-amber-600'">
                      {{ opt.l }}
                    </button>
                  </div>
                </div>

                <div v-if="!$page.props.auth.user.has_2fa"
                  class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                  <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                  </svg>
                  <div>
                    <p class="text-sm font-semibold text-amber-800">Google Authenticator belum disetup</p>
                    <p class="text-xs text-amber-600 mt-0.5">Bypass membutuhkan verifikasi 2FA. Setup Google Authenticator terlebih dahulu di halaman profil.</p>
                  </div>
                </div>

                <template v-else>
                  <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password Akun</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                      </div>
                      <input
                        v-model="bypassPassword"
                        type="password"
                        :disabled="isActivatingBypass"
                        placeholder="Masukkan password akun..."
                        autocomplete="current-password"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-colors placeholder-gray-300 disabled:opacity-50"/>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Kode Google Authenticator</label>
                    <input
                      v-model="bypassOtp"
                      type="text"
                      maxlength="6"
                      inputmode="numeric"
                      :disabled="isActivatingBypass"
                      placeholder="000000"
                      @keyup.enter="bypassOtp.length === 6 && bypassPassword.length > 0 ? submitActivateBypass() : null"
                      class="w-full text-3xl tracking-[0.5em] text-center font-mono py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-colors placeholder-gray-200 disabled:opacity-50"/>
                    <p class="text-xs text-gray-400 mt-1.5 text-center">Kode berubah setiap 30 detik</p>
                  </div>

                  <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                    <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-600">Bypass membutuhkan <strong>password + kode 2FA</strong> sekaligus untuk keamanan ekstra.</p>
                  </div>
                </template>
              </div>

              <div class="px-6 pb-6 flex gap-3">
                <button @click="closeBypassModal()"
                  :disabled="isActivatingBypass"
                  class="flex-1 py-2.5 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all disabled:opacity-50">
                  Batal
                </button>
                <button v-if="$page.props.auth.user.has_2fa"
                  @click="submitActivateBypass"
                  :disabled="bypassPassword.length === 0 || bypassOtp.length < 6 || isActivatingBypass"
                  class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                  <svg v-if="isActivatingBypass" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ isActivatingBypass ? 'Memverifikasi...' : 'Aktifkan Bypass' }}
                </button>
              </div>
            </template>
          </div>
        </Transition>
      </div>
    </div>
  </Transition>

  <!-- ── Toast Notifications ── -->
  <Teleport to="body">
    <div class="fixed bottom-5 right-5 z-[400] flex flex-col-reverse gap-2 items-end pointer-events-none">
      <TransitionGroup name="toast">
        <div v-for="toast in toasts" :key="toast.id"
          class="pointer-events-auto flex items-center gap-3 bg-white border border-gray-100 shadow-xl shadow-gray-300/30 rounded-2xl px-4 py-3 text-sm text-gray-700 min-w-64 max-w-sm">
          <div class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center"
            :class="toast.type === 'success' ? 'bg-emerald-100' : 'bg-red-100'">
            <svg class="w-3.5 h-3.5" :class="toast.type === 'success' ? 'text-emerald-600' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <span class="flex-1 text-[13px] leading-snug">{{ toast.message }}</span>
          <button @click="removeToast(toast.id)"
            class="shrink-0 text-gray-300 hover:text-gray-500 transition-colors p-0.5 rounded-md hover:bg-gray-100">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>

</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import GateModal from '@/Components/GateModal.vue';

const mainEl = ref(null);

defineProps({ title: { type: String, default: '' } });

const page        = usePage();
const sidebarOpen = ref(false);

// ── Notification bell ─────────────────────────────────────────────────────────
const notifRef      = ref(null)
const notifOpen     = ref(false)
const notifLoading  = ref(false)
const notifData     = ref(null)
const notifPendingNav = ref(null)

async function fetchNotif() {
  notifLoading.value = true
  try {
    const { data } = await axios.get(route('notifications.index'))
    notifData.value = data
  } finally {
    notifLoading.value = false
  }
}

function toggleNotif() {
  notifOpen.value = !notifOpen.value
  if (notifOpen.value && !notifData.value) fetchNotif()
}

function goToInvoice(n) {
  notifOpen.value = false
  const dest = route('invoices.client', n.client_id) + '?highlight=' + n.id
  if (bypassActive.value) {
    router.visit(dest)
  } else {
    notifPendingNav.value = dest
    openBypassModal()
  }
}

function goToNotifPage() {
  notifOpen.value = false
  const dest = route('notifications.page')
  if (bypassActive.value) {
    router.visit(dest)
  } else {
    notifPendingNav.value = dest
    openBypassModal()
  }
}

function handleNotifOutside(e) {
  if (notifRef.value && !notifRef.value.contains(e.target)) {
    notifOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleNotifOutside)
  // refresh count setiap 60 detik
  const interval = setInterval(() => {
    if (notifOpen.value) fetchNotif()
  }, 60000)
  onBeforeUnmount(() => clearInterval(interval))
})
onBeforeUnmount(() => document.removeEventListener('mousedown', handleNotifOutside))

// ── Toast system ─────────────────────────────────────────────────────────────
const toasts   = ref([])
let   _toastId = 0

function addToast(message, type = 'success') {
  const id = ++_toastId
  toasts.value.push({ id, message, type })
  setTimeout(() => removeToast(id), 4500)
}

function removeToast(id) {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

watch(
  () => [page.props.flash?.success, page.props.flash?.error],
  ([success, error]) => {
    if (success) addToast(success, 'success')
    if (error)   addToast(error,   'error')
  }
)
// ─────────────────────────────────────────────────────────────────────────────

// ── Bypass ───────────────────────────────────────────────────────────────────
const bypassExpiresAt    = computed(() => page.props.bypassExpiresAt)
const bypassRemaining    = ref(0)
const showBypassModal    = ref(false)
const bypassMinutes      = ref(30)
const bypassPassword     = ref('')
const bypassOtp          = ref('')
const isActivatingBypass = ref(false)
const bypassSuccess      = ref(false)

const updateBypassTimer = () => {
  const exp = bypassExpiresAt.value
  bypassRemaining.value = exp ? Math.max(0, exp - Math.floor(Date.now() / 1000)) : 0
}

const formatBypassTime = computed(() => {
  const s = bypassRemaining.value
  if (!s) return ''
  const m   = Math.floor(s / 60)
  const sec = String(s % 60).padStart(2, '0')
  return `${m}:${sec}`
})

const bypassActive = computed(() => bypassRemaining.value > 0)

let _bypassInterval = null
onMounted(() => { updateBypassTimer(); _bypassInterval = setInterval(updateBypassTimer, 1000) })
onBeforeUnmount(() => clearInterval(_bypassInterval))
watch(bypassExpiresAt, updateBypassTimer)

function openBypassModal() {
  bypassPassword.value  = ''
  bypassOtp.value       = ''
  bypassMinutes.value   = 30
  showBypassModal.value = true
}

function closeBypassModal() {
  showBypassModal.value = false
  notifPendingNav.value = null
}

async function submitActivateBypass() {
  if (isActivatingBypass.value) return
  isActivatingBypass.value = true
  try {
    await axios.post(route('security.activate-bypass'), {
      password: bypassPassword.value,
      otp:      bypassOtp.value,
      minutes:  bypassMinutes.value,
    })
    isActivatingBypass.value = false
    bypassSuccess.value = true
    setTimeout(() => {
      showBypassModal.value = false
      bypassSuccess.value   = false
      if (notifPendingNav.value) {
        const dest = notifPendingNav.value
        notifPendingNav.value = null
        router.visit(dest)
      } else {
        router.reload()
      }
    }, 1500)
  } catch (e) {
    isActivatingBypass.value = false
    Swal.fire({
      icon: 'error',
      title: 'Verifikasi Gagal',
      text: e.response?.data?.message || 'Periksa kembali password dan kode 2FA.',
      confirmButtonColor: '#EF4444',
    })
  }
}

async function confirmDeactivateBypass() {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Matikan Bypass?',
    text: 'Gate verifikasi akan aktif kembali untuk semua aksi berikutnya.',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonColor:  '#6B7280',
    confirmButtonText: 'Ya, Matikan',
    cancelButtonText:  'Batal',
    reverseButtons: true,
    focusCancel: true,
  })
  if (!result.isConfirmed) return

  await axios.post(route('security.deactivate-bypass'))
  await Swal.fire({
    icon: 'success',
    title: 'Bypass Dimatikan',
    text: 'Gate verifikasi kembali aktif.',
    timer: 1800,
    timerProgressBar: true,
    showConfirmButton: false,
  })
  router.reload()
}
// ─────────────────────────────────────────────────────────────────────────────

const userInitial = computed(() =>
  page.props.auth.user?.name?.charAt(0).toUpperCase() ?? '?'
)

watch(() => page.url, () => {
  sidebarOpen.value = false
  if (mainEl.value) mainEl.value.scrollTop = 0
})

function closeSidebarOnMobile() {
  if (window.innerWidth < 1024) sidebarOpen.value = false
}

function logout() {
  router.post(route('logout'))
}

const navBase     = 'flex items-center px-3 py-2 rounded-lg text-[13px] transition-colors duration-150'
const navActive   = `${navBase} bg-white text-indigo-700 font-semibold shadow-sm`
const navInactive = `${navBase} text-gray-500 hover:bg-white/70 hover:text-gray-900`

function navClass(routeUrl, exclude = []) {
  const path = new URL(routeUrl).pathname
  if (!page.url.startsWith(path)) return navInactive
  if (exclude.some(p => page.url.startsWith(p))) return navInactive
  return navActive
}
</script>

<style scoped>
/* Sidebar overlay */
.fade-overlay-enter-active,
.fade-overlay-leave-active { transition: opacity 0.25s ease; }
.fade-overlay-enter-from,
.fade-overlay-leave-to     { opacity: 0; }

/* Notification dropdown */
.notif-drop-enter-active { transition: opacity 0.15s ease, transform 0.18s cubic-bezier(0.34,1.56,0.64,1); }
.notif-drop-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.notif-drop-enter-from   { opacity: 0; transform: translateY(-6px) scale(0.97); }
.notif-drop-leave-to     { opacity: 0; transform: translateY(-4px) scale(0.98); }

/* Bypass modal backdrop */
.bypass-overlay-enter-active,
.bypass-overlay-leave-active { transition: opacity 0.2s ease; }
.bypass-overlay-enter-from,
.bypass-overlay-leave-to     { opacity: 0; }

/* Bypass modal card */
.bypass-card-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.bypass-card-leave-active { transition: all 0.15s ease-in; }
.bypass-card-enter-from   { transform: scale(0.9) translateY(12px); opacity: 0; }
.bypass-card-leave-to     { transform: scale(0.95); opacity: 0; }

/* Bypass success overlay */
.bypass-success-enter-active { transition: all 0.3s ease-out; }
.bypass-success-leave-active { transition: all 0.15s ease-in; }
.bypass-success-enter-from   { opacity: 0; transform: scale(0.96); }
.bypass-success-leave-to     { opacity: 0; }

/* Bypass checkmark draw */
.bypass-check-path {
  stroke-dasharray: 28;
  stroke-dashoffset: 28;
  animation: bypassCheckDraw 0.4s ease-out 0.2s forwards;
}
.bypass-success-circle {
  animation: bypassCircleIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes bypassCheckDraw { to { stroke-dashoffset: 0; } }
@keyframes bypassCircleIn {
  from { transform: scale(0.4); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}

/* Toast */
.toast-enter-active { transition: all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.18s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(16px) scale(0.96); }
.toast-leave-to     { opacity: 0; transform: translateX(8px) scale(0.98); }
.toast-move         { transition: transform 0.2s ease; }
</style>
