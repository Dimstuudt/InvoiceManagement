<template>
  <AppLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-800">
          Selamat datang, {{ $page.props.auth.user.name }}!
        </h2>
        <p class="mt-1 text-sm text-gray-500">Kelola invoice dan broadcast email dari sini.</p>
      </div>

      <!-- Stats placeholder -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-2xl border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">{{ stat.label }}</p>
              <p class="mt-1 text-2xl font-bold text-gray-900">{{ stat.value }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="stat.bg">
              <svg class="w-5 h-5" :class="stat.color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const stats = [
  {
    label: 'Total Invoice',
    value: '0',
    bg: 'bg-indigo-50',
    color: 'text-indigo-600',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  },
  {
    label: 'Total Klien',
    value: '0',
    bg: 'bg-green-50',
    color: 'text-green-600',
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
  },
  {
    label: 'Email Terkirim',
    value: '0',
    bg: 'bg-purple-50',
    color: 'text-purple-600',
    icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  },
];
</script>
