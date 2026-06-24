<template>
  <AppLayout title="Signatures">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Signatures</h2>
          <p class="text-sm text-gray-500 mt-0.5">{{ signatures.length }} tanda tangan terdaftar</p>
        </div>
        <Link :href="route('master.signatures.create')"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Signature
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="signatures.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
          <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada signature</p>
        <p class="text-xs text-gray-400 mt-1">Tambahkan tanda tangan pertama kamu</p>
      </div>

      <!-- Cards Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <div v-for="sig in signatures" :key="sig.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">

          <!-- Signature Image Area -->
          <div class="relative h-40 bg-gradient-to-b from-white to-gray-50 flex items-center justify-center px-6 border-b border-gray-100">
            <!-- Ruled line (khas area tanda tangan) -->
            <div class="absolute bottom-8 left-6 right-6 h-px bg-gray-200"></div>

            <img v-if="sig.signature_image_url"
              :src="sig.signature_image_url"
              class="max-h-24 max-w-full object-contain relative z-10"
              :alt="sig.name" />
            <div v-else class="flex flex-col items-center gap-2 text-gray-300 relative z-10">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
              </svg>
              <span class="text-xs">No signature</span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col">
            <div class="flex-1 text-center">
              <h3 class="text-sm font-semibold text-gray-900">{{ sig.name }}</h3>
              <p class="text-xs text-gray-400 mt-1">{{ sig.position }}</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
              <Link :href="route('master.signatures.edit', sig.id)"
                class="flex-1 text-center py-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-200 transition-colors">
                Edit
              </Link>
              <button @click="destroy(sig)"
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

defineProps({ signatures: Array });

function destroy(sig) {
  if (confirm(`Hapus "${sig.name}"?`)) {
    router.delete(route('master.signatures.destroy', sig.id));
  }
}
</script>
