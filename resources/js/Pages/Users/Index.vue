<template>
  <AppLayout title="User Management">
    <div class="space-y-4">

      <!-- Header -->
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-gray-800">User Management</h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ users.length }} user terdaftar</p>
        </div>
        <button @click="goCreate"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah User
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-gray-100">
              <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Email</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Role</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">2FA</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Bergabung</th>
              <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="users.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-300">Belum ada user.</td>
            </tr>
            <tr v-for="user in users" :key="user.id"
              class="hover:bg-slate-50/60 transition-colors group">

              <!-- User -->
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"
                    :class="avatarColor(user.name)">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight flex items-center gap-1.5">
                      {{ user.name }}
                      <span v-if="user.id === $page.props.auth.user.id"
                        class="text-[10px] font-medium text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-full">
                        Kamu
                      </span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 md:hidden">{{ user.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Email -->
              <td class="px-4 py-3.5 text-sm text-gray-500 hidden md:table-cell">{{ user.email }}</td>

              <!-- Role -->
              <td class="px-4 py-3.5">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  :class="user.role === 'admin'
                    ? 'bg-indigo-50 text-indigo-700'
                    : 'bg-gray-100 text-gray-500'">
                  <span class="w-1.5 h-1.5 rounded-full"
                    :class="user.role === 'admin' ? 'bg-indigo-500' : 'bg-gray-400'"></span>
                  {{ user.role === 'admin' ? 'Admin' : 'User' }}
                </span>
              </td>

              <!-- 2FA -->
              <td class="px-4 py-3.5 hidden lg:table-cell">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  :class="user.has_2fa
                    ? 'bg-emerald-50 text-emerald-700'
                    : 'bg-gray-100 text-gray-400'">
                  <span class="w-1.5 h-1.5 rounded-full"
                    :class="user.has_2fa ? 'bg-emerald-500' : 'bg-gray-300'"></span>
                  {{ user.has_2fa ? 'Aktif' : 'Belum' }}
                </span>
              </td>

              <!-- Bergabung -->
              <td class="px-4 py-3.5 text-sm text-gray-500 hidden lg:table-cell">
                {{ formatDate(user.created_at) }}
              </td>

              <!-- Aksi -->
              <td class="px-4 py-3.5">
                <div class="flex items-center justify-end gap-1.5 opacity-70 group-hover:opacity-100 transition-opacity">
                  <button @click="goEdit(user)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </button>
                  <button
                    v-if="user.id !== $page.props.auth.user.id"
                    @click="deleteUser(user)"
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
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

defineProps({ users: Array });

const { requireGate } = useSecurityGate();

async function goCreate() {
  const passed = await requireGate();
  if (!passed) return;
  router.visit(route('users.create'));
}

async function goEdit(user) {
  const passed = await requireGate();
  if (!passed) return;
  router.visit(route('users.edit', user.id));
}

async function deleteUser(user) {
  const passed = await requireGate();
  if (!passed) return;

  const result = await Swal.fire({
    title: 'Hapus user ini?',
    html: `<span class="text-sm text-gray-600"><strong>${user.name}</strong> akan dihapus permanen.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
  });

  if (result.isConfirmed) router.delete(route('users.destroy', user.id));
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(String(dateStr).replace(' ', 'T'))
    .toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function avatarColor(name) {
  const palette = [
    'bg-indigo-100 text-indigo-600',
    'bg-blue-100 text-blue-600',
    'bg-violet-100 text-violet-600',
    'bg-emerald-100 text-emerald-600',
    'bg-rose-100 text-rose-600',
    'bg-amber-100 text-amber-600',
    'bg-teal-100 text-teal-600',
    'bg-cyan-100 text-cyan-600',
  ];
  return palette[(name?.charCodeAt(0) ?? 0) % palette.length];
}
</script>
