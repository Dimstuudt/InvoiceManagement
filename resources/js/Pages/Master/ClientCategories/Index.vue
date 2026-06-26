<template>
  <AppLayout title="Client Categories">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Client Categories</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ categories.length }} kategori terdaftar</p>
        </div>
        <Link :href="route('master.client-categories.create')"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="categories.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada client category</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan kategori pertama untuk client kamu</p>
        <Link :href="route('master.client-categories.create')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Kategori
        </Link>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/60">
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Nama</th>
              <th class="text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Deskripsi</th>
              <th class="px-5 py-3 w-20"/>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="cat in categories" :key="cat.id"
              class="hover:bg-gray-50/50 transition-colors group">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <span class="text-xs font-bold text-indigo-400">{{ cat.name.charAt(0).toUpperCase() }}</span>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ cat.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3.5 text-sm text-gray-400">{{ cat.description || '—' }}</td>
              <td class="px-4 py-3.5">
                <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <Link :href="route('master.client-categories.edit', cat.id)" title="Edit"
                    class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
                    </svg>
                  </Link>
                  <button @click="destroy(cat)" title="Hapus"
                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
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

defineProps({ categories: Array });

function destroy(cat) {
  if (confirm(`Hapus "${cat.name}"?`)) {
    router.delete(route('master.client-categories.destroy', cat.id));
  }
}
</script>
