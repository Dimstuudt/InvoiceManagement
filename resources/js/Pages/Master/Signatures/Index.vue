<template>
  <AppLayout title="Signatures">
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Signatures</h2>
          <p class="text-sm text-gray-500">{{ signatures.length }} tanda tangan</p>
        </div>
        <Link :href="route('master.signatures.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah
        </Link>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanda Tangan</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jabatan</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="signatures.length === 0">
              <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data.</td>
            </tr>
            <tr v-for="sig in signatures" :key="sig.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <img v-if="sig.signature_image_url" :src="sig.signature_image_url" class="h-12 object-contain rounded border border-gray-100 bg-gray-50 p-1" />
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ sig.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ sig.position }}</td>
              <td class="px-6 py-4 text-right space-x-2">
                <Link :href="route('master.signatures.edit', sig.id)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 rounded-lg transition-colors">Edit</Link>
                <button @click="destroy(sig)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 rounded-lg transition-colors">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
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
