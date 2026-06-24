<template>
  <AppLayout title="Bank Accounts">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Bank Accounts</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ accounts.length }} akun terdaftar</p>
        </div>
        <Link :href="route('master.bank-accounts.create')"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Bank
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="accounts.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada bank account</p>
        <p class="text-xs text-gray-400 mt-1">Tambahkan rekening bank pertama kamu</p>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="acc in accounts" :key="acc.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">

          <!-- Logo Area -->
          <div class="relative h-32 bg-gradient-to-br from-slate-50 to-gray-100 flex items-center justify-center overflow-hidden">
            <img v-if="acc.bank_logo_url"
              :src="acc.bank_logo_url"
              class="max-h-16 max-w-[60%] object-contain"
              :alt="acc.bank_name" />
            <div v-else
              class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center">
              <span class="text-xl font-bold text-gray-400">{{ acc.bank_name.charAt(0) }}</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col">
            <!-- Bank Name -->
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-widest mb-1">{{ acc.bank_name }}</p>
            <h3 class="text-sm font-semibold text-gray-900 mb-4 leading-snug">{{ acc.name }}</h3>

            <!-- Account Number -->
            <div class="bg-gray-50 rounded-xl px-4 py-3 flex-1 flex items-center justify-between">
              <span class="text-xs text-gray-400">No. Rekening</span>
              <span class="text-sm font-mono font-semibold text-gray-800 tracking-wide">{{ acc.account_number }}</span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              <Link :href="route('master.bank-accounts.edit', acc.id)"
                class="flex-1 text-center py-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 transition-colors">
                Edit
              </Link>
              <button @click="destroy(acc)"
                class="flex-1 py-2 text-xs font-medium text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg border border-red-100 hover:border-red-200 transition-colors">
                Hapus
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({ accounts: Array });

function destroy(acc) {
  if (confirm(`Hapus akun "${acc.name}"?`)) {
    router.delete(route('master.bank-accounts.destroy', acc.id));
  }
}
</script>
