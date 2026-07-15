<template>
  <AppLayout title="Notifikasi">
    <div class="max-w-5xl mx-auto space-y-5">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Notifikasi</h2>
          <p class="text-sm text-gray-400 mt-0.5">Invoice yang memerlukan tindakan</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <span v-if="total > 0"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-600">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"/>
            {{ total }} perlu perhatian
          </span>
          <span v-else
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"/>
            Semua aman
          </span>
        </div>
      </div>

      <!-- Body: two column on lg -->
      <div class="flex flex-col lg:flex-row gap-5 items-start">

        <!-- LEFT: Notification sections -->
        <div class="flex-1 min-w-0 space-y-4">

          <!-- Empty state -->
          <div v-if="total === 0"
            class="bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-14 flex flex-col items-center gap-4 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center">
              <svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-base font-bold text-gray-700">Semua invoice aman</p>
              <p class="text-sm text-gray-400 mt-1 max-w-xs">
                Tidak ada yang overdue, mendekati jatuh tempo, atau draft yang tertunda.
              </p>
            </div>
          </div>

          <!-- Overdue -->
          <section v-if="overdue.length > 0" class="bg-white rounded-2xl border border-red-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-3.5 bg-red-50 border-b border-red-100">
              <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse shrink-0"/>
              <p class="text-xs font-bold uppercase tracking-widest text-red-600 flex-1">Overdue</p>
              <span class="text-xs font-bold text-red-500 bg-red-100 px-2 py-0.5 rounded-full">{{ overdue.length }}</span>
            </div>
            <div class="divide-y divide-gray-50">
              <NotifRow v-for="n in overdue" :key="n.id" :item="n" color="red" @click="go(n)">
                <template #sub>{{ n.days_overdue }} hari melewati jatuh tempo</template>
              </NotifRow>
            </div>
          </section>

          <!-- Jatuh tempo hari ini -->
          <section v-if="due_today.length > 0" class="bg-white rounded-2xl border border-orange-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-3.5 bg-orange-50 border-b border-orange-100">
              <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"/>
              <p class="text-xs font-bold uppercase tracking-widest text-orange-600 flex-1">Jatuh Tempo Hari Ini</p>
              <span class="text-xs font-bold text-orange-500 bg-orange-100 px-2 py-0.5 rounded-full">{{ due_today.length }}</span>
            </div>
            <div class="divide-y divide-gray-50">
              <NotifRow v-for="n in due_today" :key="n.id" :item="n" color="orange" @click="go(n)">
                <template #sub>Jatuh tempo hari ini</template>
              </NotifRow>
            </div>
          </section>

          <!-- Segera jatuh tempo -->
          <section v-if="due_soon.length > 0" class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-3.5 bg-amber-50 border-b border-amber-100">
              <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"/>
              <p class="text-xs font-bold uppercase tracking-widest text-amber-600 flex-1">Segera Jatuh Tempo</p>
              <span class="text-xs font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">{{ due_soon.length }}</span>
            </div>
            <div class="divide-y divide-gray-50">
              <NotifRow v-for="n in due_soon" :key="n.id" :item="n" color="amber" @click="go(n)">
                <template #sub>{{ n.days_until }} hari lagi</template>
              </NotifRow>
            </div>
          </section>

          <!-- Draft terlupakan -->
          <section v-if="draft_late.length > 0" class="bg-white rounded-2xl border border-violet-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-3.5 bg-violet-50 border-b border-violet-100">
              <span class="w-2 h-2 rounded-full bg-violet-400 shrink-0"/>
              <p class="text-xs font-bold uppercase tracking-widest text-violet-600 flex-1">Draft Terlupakan</p>
              <span class="text-xs font-bold text-violet-600 bg-violet-100 px-2 py-0.5 rounded-full">{{ draft_late.length }}</span>
            </div>
            <div class="divide-y divide-gray-50">
              <NotifRow v-for="n in draft_late" :key="n.id" :item="n" color="violet" @click="go(n)">
                <template #sub>Issue date lewat {{ n.days_past }} hari — masih draft</template>
              </NotifRow>
            </div>
          </section>

          <!-- Draft akan terbit -->
          <section v-if="draft_upcoming.length > 0" class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-3.5 bg-blue-50 border-b border-blue-100">
              <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"/>
              <p class="text-xs font-bold uppercase tracking-widest text-blue-600 flex-1">Draft Akan Terbit</p>
              <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">{{ draft_upcoming.length }}</span>
            </div>
            <div class="divide-y divide-gray-50">
              <NotifRow v-for="n in draft_upcoming" :key="n.id" :item="n" color="blue" @click="go(n)">
                <template #sub>
                  <span v-if="n.days_until === 0">Issue date hari ini — masih draft</span>
                  <span v-else>Issue date {{ n.days_until }} hari lagi — masih draft</span>
                </template>
              </NotifRow>
            </div>
          </section>

        </div>

        <!-- RIGHT: Guide panel (sticky on lg) -->
        <div class="w-full lg:w-72 shrink-0 space-y-4 lg:sticky lg:top-6">

          <!-- Legend -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-50">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Arti Warna</p>
            </div>
            <div class="divide-y divide-gray-50">
              <LegendItem color="red" label="Overdue">
                Invoice terverifikasi yang belum dibayar dan sudah melewati tanggal jatuh tempo.
              </LegendItem>
              <LegendItem color="orange" label="Jatuh Tempo Hari Ini">
                Invoice terverifikasi dengan due date tepat hari ini.
              </LegendItem>
              <LegendItem color="amber" label="Segera Jatuh Tempo">
                Invoice terverifikasi dengan due date dalam 3 hari ke depan.
              </LegendItem>
              <LegendItem color="violet" label="Draft Terlupakan">
                Invoice masih draft padahal issue date sudah lewat — kemungkinan lupa diverifikasi.
              </LegendItem>
              <LegendItem color="blue" label="Draft Akan Terbit">
                Invoice masih draft dengan issue date dalam 5 hari ke depan.
              </LegendItem>
            </div>
          </div>

          <!-- Yang tidak muncul -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-50">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tidak Masuk Notifikasi</p>
            </div>
            <div class="px-4 py-3 space-y-2.5">
              <ExcludeRow icon="check">
                Invoice <span class="font-semibold text-emerald-600">Lunas</span> — sudah selesai, tidak perlu tindakan.
              </ExcludeRow>
              <ExcludeRow icon="snowflake">
                Invoice <span class="font-semibold text-sky-600">Frozen</span> — sengaja ditangguhkan.
              </ExcludeRow>
              <ExcludeRow icon="arrow">
                Invoice <span class="font-semibold text-orange-500">Carried</span> — tunggakan sudah dipindah ke periode baru.
              </ExcludeRow>
              <ExcludeRow icon="slash">
                Invoice <span class="font-semibold text-gray-500">Tidak Aktif</span> — sengaja dinonaktifkan.
              </ExcludeRow>
              <ExcludeRow icon="chain">
                Invoice <span class="font-semibold text-gray-500">chain (C-/R-/P-)</span> — hanya HEAD yang dipantau.
              </ExcludeRow>
            </div>
          </div>

          <!-- Tips -->
          <div class="bg-indigo-50 rounded-2xl border border-indigo-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-indigo-100">
              <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Tips</p>
            </div>
            <div class="px-4 py-3 space-y-2.5">
              <p class="text-[11px] text-indigo-700 leading-relaxed">
                Klik notif mana pun untuk langsung loncat ke halaman invoice client yang bersangkutan.
              </p>
              <p class="text-[11px] text-indigo-700 leading-relaxed">
                Invoice overdue bisa langsung <span class="font-semibold">dinonaktifkan</span> dari menu titik tiga di halaman client — jika memang tidak akan ditagih lagi.
              </p>
              <p class="text-[11px] text-indigo-700 leading-relaxed">
                Draft terlupakan: cek apakah perlu diverifikasi atau dihapus. Jika sudah tidak relevan, nonaktifkan atau hapus invoice tersebut.
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { defineComponent, h } from 'vue'

const props = defineProps({
  overdue:         { type: Array, default: () => [] },
  due_today:       { type: Array, default: () => [] },
  due_soon:        { type: Array, default: () => [] },
  draft_late:      { type: Array, default: () => [] },
  draft_upcoming:  { type: Array, default: () => [] },
  total:           { type: Number, default: 0 },
})

function go(n) {
  router.visit(route('invoices.client', n.client_id) + '?highlight=' + n.id)
}

// ── Color map ───────────────────────────────────────────────
const colorMap = {
  red:    { icon: 'text-red-500',    bg: 'bg-red-100',    sub: 'text-red-400',    hover: 'hover:bg-red-50/50',    dot: 'bg-red-500' },
  orange: { icon: 'text-orange-500', bg: 'bg-orange-100', sub: 'text-orange-400', hover: 'hover:bg-orange-50/50', dot: 'bg-orange-400' },
  amber:  { icon: 'text-amber-500',  bg: 'bg-amber-100',  sub: 'text-amber-500',  hover: 'hover:bg-amber-50/50',  dot: 'bg-amber-400' },
  violet: { icon: 'text-violet-500', bg: 'bg-violet-100', sub: 'text-violet-400', hover: 'hover:bg-violet-50/50', dot: 'bg-violet-400' },
  blue:   { icon: 'text-blue-500',   bg: 'bg-blue-100',   sub: 'text-blue-400',   hover: 'hover:bg-blue-50/50',   dot: 'bg-blue-400' },
}

// ── NotifRow ────────────────────────────────────────────────
const NotifRow = defineComponent({
  props: { item: Object, color: String },
  emits: ['click'],
  setup(p, { slots, emit }) {
    const c = colorMap[p.color] ?? colorMap.red
    return () => h('button', {
      class: `w-full flex items-center gap-4 px-5 py-3.5 text-left transition-colors ${c.hover}`,
      onClick: () => emit('click'),
    }, [
      h('div', { class: `w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${c.bg}` },
        h('svg', { class: `w-4 h-4 ${c.icon}`, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' },
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' })
        )
      ),
      h('div', { class: 'flex-1 min-w-0' }, [
        h('p', { class: 'text-sm font-semibold text-gray-800 truncate' }, p.item.client_name),
        h('p', { class: 'text-xs text-gray-400 truncate mt-0.5' }, p.item.category),
      ]),
      h('div', { class: 'text-right shrink-0' }, [
        h('p', { class: 'text-xs font-mono font-medium text-gray-500' }, p.item.invoice_number),
        h('p', { class: `text-[11px] font-medium mt-0.5 ${c.sub}` }, slots.sub?.()),
      ]),
      h('svg', { class: 'w-4 h-4 text-gray-200 shrink-0', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' },
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 5l7 7-7 7' })
      ),
    ])
  },
})

// ── LegendItem ──────────────────────────────────────────────
const legendDotMap = {
  red: 'bg-red-500', orange: 'bg-orange-400', amber: 'bg-amber-400', violet: 'bg-violet-400', blue: 'bg-blue-400',
}
const legendLabelMap = {
  red: 'text-red-600', orange: 'text-orange-600', amber: 'text-amber-600', violet: 'text-violet-600', blue: 'text-blue-600',
}

const LegendItem = defineComponent({
  props: { color: String, label: String },
  setup(p, { slots }) {
    const dot   = legendDotMap[p.color]   ?? 'bg-gray-300'
    const label = legendLabelMap[p.color] ?? 'text-gray-600'
    return () => h('div', { class: 'flex gap-3 px-4 py-3' }, [
      h('span', { class: `w-2 h-2 rounded-full ${dot} mt-1 shrink-0` }),
      h('div', [
        h('p', { class: `text-[11px] font-bold ${label} mb-0.5` }, p.label),
        h('p', { class: 'text-[11px] text-gray-400 leading-relaxed' }, slots.default?.()),
      ]),
    ])
  },
})

// ── ExcludeRow ──────────────────────────────────────────────
const iconPaths = {
  check:    'M5 13l4 4L19 7',
  snowflake:'M12 3v18M3 12h18M5.636 5.636l12.728 12.728M18.364 5.636L5.636 18.364',
  arrow:    'M13 5l7 7-7 7M5 12h15',
  slash:    'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636',
  chain:    'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
}

const ExcludeRow = defineComponent({
  props: { icon: String },
  setup(p, { slots }) {
    const d = iconPaths[p.icon] ?? iconPaths.check
    return () => h('div', { class: 'flex items-start gap-2.5' }, [
      h('div', { class: 'w-5 h-5 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 mt-0.5' },
        h('svg', { class: 'w-2.5 h-2.5 text-gray-400', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' },
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d })
        )
      ),
      h('p', { class: 'text-[11px] text-gray-400 leading-relaxed' }, slots.default?.()),
    ])
  },
})
</script>
