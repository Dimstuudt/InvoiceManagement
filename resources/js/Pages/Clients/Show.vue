<template>
  <AppLayout title="Detail Client">
    <div class="max-w-3xl mx-auto space-y-4">

      <!-- Back + actions header -->
      <div class="flex items-center justify-between">
        <Link :href="route('clients.index')"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Kembali
        </Link>
        <button @click="editClient"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Edit
        </button>
      </div>

      <!-- Company header card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center text-xl font-bold text-indigo-600 flex-shrink-0">
            {{ initials(client.company_name) }}
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ client.company_name }}</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ client.category ?? '-' }} · {{ client.city }}</p>

            <div class="flex flex-wrap gap-2 mt-3">
              <!-- Client Status toggle -->
              <button @click="toggleClientStatus"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                :class="client.client_status === 'active_client'
                  ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                  : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="client.client_status === 'active_client' ? 'bg-emerald-500' : 'bg-amber-400'"></span>
                {{ client.client_status === 'active_client' ? 'Active Client' : 'Prospect' }}
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                </svg>
              </button>

              <!-- Is Active toggle -->
              <button @click="toggleIsActive"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                :class="client.is_active
                  ? 'bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100'
                  : 'bg-gray-100 text-gray-400 border-gray-200 hover:bg-gray-200'">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="client.is_active ? 'bg-indigo-500' : 'bg-gray-300'"></span>
                {{ client.is_active ? 'Aktif' : 'Nonaktif' }}
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Info grid -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
        <div v-for="row in infoRows" :key="row.label"
          class="flex items-start gap-4 px-6 py-4">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide w-28 flex-shrink-0 pt-0.5">{{ row.label }}</p>
          <p class="text-sm text-gray-700 flex-1">{{ row.value || '-' }}</p>
        </div>
      </div>

      <!-- Emails -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</p>
        </div>
        <div v-if="client.emails.length === 0" class="px-6 py-5 text-sm text-gray-300">Belum ada email.</div>
        <div v-for="em in client.emails" :key="em.id"
          class="flex items-center gap-3 px-6 py-3.5 border-b border-gray-50 last:border-0">
          <span class="w-2 h-2 rounded-full flex-shrink-0"
            :class="em.is_active ? 'bg-emerald-400' : 'bg-gray-200'"></span>
          <span class="text-sm font-medium flex-1"
            :class="em.is_active ? 'text-gray-700' : 'text-gray-300 line-through'">
            {{ em.email }}
          </span>
          <span v-if="!em.is_active" class="text-[11px] text-gray-300">nonaktif</span>
        </div>
      </div>

      <!-- Phones -->
      <div v-if="client.phones.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Telepon</p>
        </div>
        <div v-for="phone in client.phones" :key="phone"
          class="flex items-center gap-3 px-6 py-3.5 border-b border-gray-50 last:border-0">
          <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
          <span class="text-sm text-gray-700 font-mono">{{ phone }}</span>
        </div>
      </div>

      <!-- Social Media -->
      <div v-if="client.social_media.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Social Media</p>
        </div>
        <div v-for="url in client.social_media" :key="url"
          class="flex items-center gap-3 px-6 py-3.5 border-b border-gray-50 last:border-0">
          <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          <span class="text-sm text-indigo-600 truncate">{{ url }}</span>
        </div>
      </div>

      <!-- NPWP Image -->
      <div v-if="client.npwp_image_url" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NPWP</p>
        </div>
        <div class="p-6">
          <img :src="client.npwp_image_url" alt="NPWP" class="max-w-sm rounded-xl border border-gray-100"/>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';
import { useSecurityGate } from '@/Composables/useSecurityGate';

const props = defineProps({
    client: Object,
});

const { requireGate } = useSecurityGate();

async function editClient() {
    const passed = await requireGate();
    if (!passed) return;
    router.visit(route('clients.edit', props.client.id));
}

function initials(name) {
    return name?.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase() ?? '?';
}

const infoRows = computed(() => [
    { label: 'Direktur',  value: props.client.director },
    { label: 'PIC',       value: props.client.pic },
    { label: 'Alamat',    value: props.client.address },
    { label: 'Kota',      value: props.client.city },
]);

async function toggleClientStatus() {
    const nextStatus = props.client.client_status === 'active_client' ? 'prospect' : 'active_client';
    const label      = nextStatus === 'active_client' ? 'Active Client' : 'Prospect';

    const result = await Swal.fire({
        title: 'Ubah Status Client?',
        html: `<span class="text-sm text-gray-600">Ubah ke <strong>${label}</strong>?</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;
    router.patch(route('clients.update-status', props.client.id), { client_status: nextStatus }, { preserveScroll: true });
}

async function toggleIsActive() {
    const next  = !props.client.is_active;
    const label = next ? 'Aktif' : 'Nonaktif';

    const result = await Swal.fire({
        title: 'Ubah Status Aktif?',
        html: `<span class="text-sm text-gray-600">Ubah ke <strong>${label}</strong>?</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;
    router.patch(route('clients.update-status', props.client.id), { is_active: next }, { preserveScroll: true });
}
</script>
