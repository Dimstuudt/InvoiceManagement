<template>
  <AppLayout title="SPK">
    <div class="max-w-6xl mx-auto space-y-5">

      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Surat Perjanjian Kerja</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ props.clients.length }} klien terdaftar</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('spk.create-local')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Import Lokal
          </Link>
          <Link :href="route('spk.create-ollama')"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-violet-700 bg-violet-50 hover:bg-violet-100 border border-violet-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            Import + Ollama
          </Link>
          <Link :href="route('spk.create')"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
            </svg>
            Import + Groq
          </Link>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="!props.clients.length" class="p-10 text-center">
          <p class="text-sm text-gray-400">Belum ada klien.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs min-w-[820px]">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100">
                <th class="px-4 py-3 text-center font-semibold text-gray-400 w-10">No</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-400">Klien</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-400 w-28">Kategori</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 w-28">Status</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 w-24">Klien</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 w-20">Layanan</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-400 w-40">Tagihan /Invoice</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-400 w-40">Tgl Aktivasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="(client, idx) in props.clients" :key="client.id"
                class="hover:bg-indigo-50/30 cursor-pointer transition-colors group"
                @click="visit(client.id)">
                <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ idx + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                      :class="client.is_active ? 'bg-emerald-400' : 'bg-gray-300'"/>
                    <span class="font-semibold text-gray-800 group-hover:text-indigo-700 transition-colors">
                      {{ client.company_name }}
                    </span>
                    <span v-if="client.city" class="text-gray-400">· {{ client.city }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-gray-500">{{ client.category?.name ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    :class="client.is_active
                      ? 'bg-emerald-50 text-emerald-600'
                      : 'bg-gray-100 text-gray-400'">
                    {{ client.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    :class="client.client_status === 'active_client'
                      ? 'bg-indigo-50 text-indigo-600'
                      : 'bg-amber-50 text-amber-600'">
                    {{ client.client_status === 'active_client' ? 'Klien' : 'Prospek' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-gray-600 font-mono">
                  {{ client.spks_count || '—' }}
                </td>
                <td class="px-4 py-3 text-right font-mono">
                  <span v-if="client.spks_sum_contract_value" class="font-semibold text-gray-700">
                    Rp {{ formatNumber(client.spks_sum_contract_value) }}
                  </span>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-gray-400 font-mono text-[11px]">
                  {{ formatDatetime(client.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  clients: Array,
})

function visit(clientId) {
  router.visit(route('spk.client', clientId))
}

function formatNumber(n) {
  return Number(n).toLocaleString('id-ID')
}

function formatDatetime(str) {
  if (!str) return '—'
  const d = new Date(str)
  const yy  = String(d.getFullYear()).slice(2)
  const mm  = String(d.getMonth() + 1).padStart(2, '0')
  const dd  = String(d.getDate()).padStart(2, '0')
  const hh  = String(d.getHours()).padStart(2, '0')
  const min = String(d.getMinutes()).padStart(2, '0')
  const sec = String(d.getSeconds()).padStart(2, '0')
  return `${yy}-${mm}-${dd} ${hh}.${min}.${sec}`
}
</script>
