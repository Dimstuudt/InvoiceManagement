<template>
  <AppLayout title="Document Issuers">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Document Issuers</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ issuers.length }} issuer terdaftar</p>
        </div>
        <Link :href="route('master.document-issuers.create')"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Issuer
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="issuers.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada document issuer</p>
        <p class="text-xs text-gray-400 mt-1">Tambahkan issuer pertama kamu</p>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="issuer in issuers" :key="issuer.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">

          <!-- Header Image Area -->
          <div class="relative h-36 bg-gradient-to-br from-slate-50 to-gray-100 flex items-center justify-center overflow-hidden">
            <img v-if="issuer.header_image_url"
              :src="issuer.header_image_url"
              class="max-h-28 max-w-full object-contain px-6"
              :alt="issuer.name" />
            <div v-else class="flex flex-col items-center gap-2 text-gray-300">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span class="text-xs">No header image</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 leading-snug">{{ issuer.name }}</h3>

            <div class="space-y-2 flex-1">
              <div class="flex items-start gap-2">
                <span class="text-xs text-gray-400 w-20 flex-shrink-0 pt-0.5">Nama Pajak</span>
                <span class="text-xs text-gray-700 font-medium">{{ issuer.tax_id_name }}</span>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-xs text-gray-400 w-20 flex-shrink-0 pt-0.5">NPWP</span>
                <span class="text-xs text-gray-700 font-mono">{{ issuer.tax_id_number }}</span>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-xs text-gray-400 w-20 flex-shrink-0 pt-0.5">Alamat</span>
                <span class="text-xs text-gray-500 line-clamp-2">{{ issuer.tax_id_address }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              <Link :href="route('master.document-issuers.edit', issuer.id)"
                class="flex-1 text-center py-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 transition-colors">
                Edit
              </Link>
              <button @click="destroy(issuer)"
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

defineProps({ issuers: Array });

function destroy(issuer) {
  if (confirm(`Hapus "${issuer.name}"?`)) {
    router.delete(route('master.document-issuers.destroy', issuer.id));
  }
}
</script>
