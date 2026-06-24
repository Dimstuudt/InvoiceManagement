<template>
  <AppLayout title="Clients">
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Clients</h2>
          <p class="text-sm text-gray-500">{{ clients.length }} client terdaftar</p>
        </div>
        <Link :href="route('clients.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Client
        </Link>
      </div>

      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Perusahaan</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kota</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">PIC</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Telepon</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aktif</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="clients.length === 0">
              <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada client.</td>
            </tr>
            <tr v-for="client in clients" :key="client.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-gray-900">{{ client.company_name }}</p>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ client.category ?? '-' }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ client.city }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ client.pic }}</td>
              <td class="px-6 py-4">
                <div class="space-y-0.5">
                  <p v-for="phone in client.phones" :key="phone" class="text-sm text-gray-600">{{ phone }}</p>
                  <p v-if="!client.phones.length" class="text-sm text-gray-400">-</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="client.client_status === 'active_client'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-yellow-100 text-yellow-700'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ client.client_status === 'active_client' ? 'Active Client' : 'Prospect' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="client.is_active ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ client.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <Link :href="route('clients.edit', client.id)"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 rounded-lg transition-colors">
                  Edit
                </Link>
                <button @click="destroy(client)"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 rounded-lg transition-colors">
                  Hapus
                </button>
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

defineProps({ clients: Array });

function destroy(client) {
  if (confirm(`Hapus client "${client.company_name}"?`)) {
    router.delete(route('clients.destroy', client.id));
  }
}
</script>
