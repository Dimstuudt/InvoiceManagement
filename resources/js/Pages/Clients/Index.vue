<template>
  <AppLayout title="Clients">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-gray-800">Clients</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ filtered.length }} dari {{ clients.length }} client</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Search -->
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input v-model="search" type="text" placeholder="Cari client..."
              class="pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 w-48 transition-all placeholder-gray-300"/>
          </div>
          <Link :href="route('clients.create')"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
          </Link>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-100">
              <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Perusahaan</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Kategori</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Kota</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">PIC</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="filtered.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-300">
                {{ search ? 'Tidak ada hasil untuk "' + search + '"' : 'Belum ada client.' }}
              </td>
            </tr>
            <tr v-for="client in filtered" :key="client.id"
              class="hover:bg-slate-50/60 transition-colors group">

              <!-- Perusahaan -->
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"
                    :class="client.is_active ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400'">
                    {{ initials(client.company_name) }}
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight">{{ client.company_name }}</p>
                    <p class="text-xs text-gray-400 md:hidden mt-0.5">{{ client.category ?? '-' }}</p>
                  </div>
                </div>
              </td>

              <!-- Kategori -->
              <td class="px-4 py-3.5 hidden md:table-cell">
                <span class="text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded-md">
                  {{ client.category ?? '-' }}
                </span>
              </td>

              <!-- Kota -->
              <td class="px-4 py-3.5 text-sm text-gray-500 hidden lg:table-cell">{{ client.city }}</td>

              <!-- PIC -->
              <td class="px-4 py-3.5 text-sm text-gray-600 hidden lg:table-cell">{{ client.pic }}</td>

              <!-- Status -->
              <td class="px-4 py-3.5">
                <div class="flex flex-col gap-1">
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full w-fit"
                    :class="client.client_status === 'active_client'
                      ? 'bg-emerald-50 text-emerald-700'
                      : 'bg-amber-50 text-amber-700'">
                    <span class="w-1.5 h-1.5 rounded-full"
                      :class="client.client_status === 'active_client' ? 'bg-emerald-500' : 'bg-amber-400'"></span>
                    {{ client.client_status === 'active_client' ? 'Active Client' : 'Prospect' }}
                  </span>
                  <span v-if="!client.is_active"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full w-fit bg-gray-100 text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                    Nonaktif
                  </span>
                </div>
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5">
                <div class="flex items-center justify-end gap-1.5 opacity-70 group-hover:opacity-100 transition-opacity">
                  <!-- View (with gate) -->
                  <button @click="viewClient(client)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 border border-indigo-100 hover:border-indigo-300 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Lihat
                  </button>

                  <!-- Edit -->
                  <button @click="editClient(client)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </button>

                  <!-- Hapus -->
                  <button @click="destroy(client)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-100 hover:border-red-300 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
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
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({ clients: Array });

const { requireGate } = useSecurityGate();
const search = ref('');


const filtered = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) return props.clients;
    return props.clients.filter(c =>
        c.company_name.toLowerCase().includes(q) ||
        (c.category ?? '').toLowerCase().includes(q) ||
        c.city.toLowerCase().includes(q) ||
        c.pic.toLowerCase().includes(q)
    );
});

function initials(name) {
    return name?.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() ?? '?';
}

async function viewClient(client) {
    const passed = await requireGate();
    if (!passed) return;
    router.visit(route('clients.show', client.id));
}

async function editClient(client) {
    const passed = await requireGate();
    if (!passed) return;
    router.visit(route('clients.edit', client.id));
}

async function destroy(client) {
    const passed = await requireGate();
    if (!passed) return;

    Swal.fire({
        title: 'Hapus client ini?',
        html: `<span class="text-sm text-gray-600"><strong>${client.company_name}</strong> akan dihapus permanen.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
    }).then(r => {
        if (r.isConfirmed) router.delete(route('clients.destroy', client.id));
    });
}
</script>
