<template>
  <AppLayout title="SPK">
    <div class="max-w-5xl mx-auto space-y-5">

      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Nomor SPK</h2>
          <p class="text-sm text-gray-400 mt-0.5">Pilih klien untuk kelola nomor SPK per invoice</p>
        </div>
      </div>

      <!-- Search -->
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <input v-model="search" type="text" placeholder="Cari klien..."
          class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"/>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="!filtered.length" class="p-10 text-center">
          <p class="text-sm text-gray-400">{{ search ? 'Tidak ada klien yang cocok.' : 'Belum ada klien.' }}</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs min-w-[600px]">
            <thead>
              <tr class="bg-gray-50/70 border-b border-gray-100">
                <th class="px-4 py-3 text-left font-semibold text-gray-400">Klien</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-400 w-32">Kategori</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 w-40">SPK Coverage</th>
                <th class="px-4 py-3 w-8"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="client in filtered" :key="client.id"
                class="hover:bg-indigo-50/30 cursor-pointer transition-colors group"
                @click="router.visit(route('spk.client', client.id))">

                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                      :class="client.is_active ? 'bg-emerald-400' : 'bg-gray-300'"/>
                    <span class="font-semibold text-gray-800 group-hover:text-indigo-700 transition-colors">
                      {{ client.company_name }}
                    </span>
                    <span v-if="client.city" class="text-gray-400">· {{ client.city }}</span>
                  </div>
                </td>

                <td class="px-4 py-3 text-gray-500">{{ client.category?.name ?? '—' }}</td>

                <td class="px-4 py-3">
                  <div v-if="client.total_invoices > 0" class="flex items-center gap-2">
                    <!-- Mini progress bar -->
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                      <div class="h-full rounded-full transition-all"
                        :class="coverageColor(client)"
                        :style="{ width: coveragePct(client) + '%' }"/>
                    </div>
                    <!-- Fraction -->
                    <span class="text-[10px] font-semibold shrink-0"
                      :class="client.spk_count === client.total_invoices
                        ? 'text-emerald-600'
                        : client.spk_count > 0 ? 'text-amber-600' : 'text-red-400'">
                      {{ client.spk_count }}/{{ client.total_invoices }}
                    </span>
                    <!-- Status dot -->
                    <span class="inline-flex items-center text-[9px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                      :class="client.spk_count === client.total_invoices
                        ? 'bg-emerald-50 text-emerald-600'
                        : client.spk_count > 0 ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-500'">
                      {{ client.spk_count === client.total_invoices ? 'Lengkap' : client.spk_count > 0 ? 'Sebagian' : 'Belum' }}
                    </span>
                  </div>
                  <span v-else class="text-gray-300 text-[10px]">Tidak ada invoice</span>
                </td>

                <td class="px-4 py-3 text-right">
                  <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-400 transition-colors ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
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
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ clients: Array })

const search = ref('')

const filtered = computed(() => {
  if (!search.value.trim()) return props.clients
  const q = search.value.toLowerCase()
  return props.clients.filter(c =>
    c.company_name.toLowerCase().includes(q) ||
    c.city?.toLowerCase().includes(q) ||
    c.category?.name?.toLowerCase().includes(q)
  )
})

function coveragePct(client) {
  if (!client.total_invoices) return 0
  return Math.round((client.spk_count / client.total_invoices) * 100)
}

function coverageColor(client) {
  const pct = coveragePct(client)
  if (pct === 100) return 'bg-emerald-400'
  if (pct > 0)    return 'bg-amber-400'
  return 'bg-red-300'
}
</script>
