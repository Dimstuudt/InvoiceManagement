<template>
  <AppLayout title="Notifikasi">
    <div class="max-w-3xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Notifikasi</h2>
          <p class="text-sm text-gray-400 mt-0.5">Invoice yang perlu perhatian</p>
        </div>
        <span class="text-sm font-semibold px-3 py-1 rounded-full"
          :class="total > 0 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">
          {{ total > 0 ? total + ' item' : 'Semua aman' }}
        </span>
      </div>

      <!-- Empty -->
      <div v-if="total === 0" class="bg-white rounded-2xl border border-gray-100 py-16 flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center">
          <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Semua invoice aman</p>
        <p class="text-sm text-gray-400 text-center max-w-xs">
          Tidak ada yang overdue, mendekati jatuh tempo, atau draft belum diverifikasi.
        </p>
      </div>

      <!-- Sections -->
      <template v-else>

        <!-- Overdue -->
        <NotifSection
          v-if="overdue.length > 0"
          title="Overdue"
          :count="overdue.length"
          dot-class="bg-red-500 animate-pulse"
          header-class="bg-red-50 border-red-100"
          label-class="text-red-600">
          <NotifRow
            v-for="n in overdue" :key="n.id"
            :item="n"
            color="red"
            @click="go(n)">
            <template #sub>{{ n.days_overdue }} hari lewat jatuh tempo</template>
          </NotifRow>
        </NotifSection>

        <!-- Jatuh tempo hari ini -->
        <NotifSection
          v-if="due_today.length > 0"
          title="Jatuh Tempo Hari Ini"
          :count="due_today.length"
          dot-class="bg-orange-400"
          header-class="bg-orange-50 border-orange-100"
          label-class="text-orange-600">
          <NotifRow
            v-for="n in due_today" :key="n.id"
            :item="n"
            color="orange"
            @click="go(n)">
            <template #sub>Jatuh tempo hari ini</template>
          </NotifRow>
        </NotifSection>

        <!-- Segera jatuh tempo -->
        <NotifSection
          v-if="due_soon.length > 0"
          title="Segera Jatuh Tempo"
          :count="due_soon.length"
          dot-class="bg-amber-400"
          header-class="bg-amber-50 border-amber-100"
          label-class="text-amber-600">
          <NotifRow
            v-for="n in due_soon" :key="n.id"
            :item="n"
            color="amber"
            @click="go(n)">
            <template #sub>{{ n.days_until }} hari lagi</template>
          </NotifRow>
        </NotifSection>

        <!-- Draft belum diverifikasi -->
        <NotifSection
          v-if="draft_unverified.length > 0"
          title="Draft Belum Diverifikasi"
          :count="draft_unverified.length"
          dot-class="bg-blue-400"
          header-class="bg-blue-50 border-blue-100"
          label-class="text-blue-600">
          <NotifRow
            v-for="n in draft_unverified" :key="n.id"
            :item="n"
            color="blue"
            @click="go(n)">
            <template #sub>
              <span v-if="n.days_until_issue < 0">Issue date lewat {{ Math.abs(n.days_until_issue) }} hari lalu</span>
              <span v-else-if="n.days_until_issue === 0">Issue date hari ini — segera verifikasi</span>
              <span v-else>Issue date {{ n.days_until_issue }} hari lagi</span>
            </template>
          </NotifRow>
        </NotifSection>

      </template>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { defineComponent, h } from 'vue'

const props = defineProps({
  overdue:          { type: Array, default: () => [] },
  due_today:        { type: Array, default: () => [] },
  due_soon:         { type: Array, default: () => [] },
  draft_unverified: { type: Array, default: () => [] },
  total:            { type: Number, default: 0 },
})

function go(n) {
  router.visit(route('invoices.client', n.client_id) + '?highlight=' + n.id)
}

// ── Inline sub-components ────────────────────────────────────────────────────

const NotifSection = defineComponent({
  props: {
    title:       String,
    count:       Number,
    dotClass:    String,
    headerClass: String,
    labelClass:  String,
  },
  setup(p, { slots }) {
    return () => h('div', { class: 'bg-white rounded-2xl border border-gray-100 overflow-hidden' }, [
      h('div', { class: `flex items-center gap-2 px-5 py-3 border-b ${p.headerClass}` }, [
        h('span', { class: `w-2 h-2 rounded-full ${p.dotClass}` }),
        h('p', { class: `text-xs font-bold uppercase tracking-wider ${p.labelClass}` }, p.title),
        h('span', { class: `ml-auto text-xs font-bold ${p.labelClass} opacity-70` }, p.count),
      ]),
      h('div', { class: 'divide-y divide-gray-50' }, slots.default?.()),
    ])
  },
})

const colorMap = {
  red:    { icon: 'text-red-500',    bg: 'bg-red-100',    sub: 'text-red-400',    hover: 'hover:bg-red-50/40' },
  orange: { icon: 'text-orange-500', bg: 'bg-orange-100', sub: 'text-orange-400', hover: 'hover:bg-orange-50/40' },
  amber:  { icon: 'text-amber-500',  bg: 'bg-amber-100',  sub: 'text-amber-500',  hover: 'hover:bg-amber-50/40' },
  blue:   { icon: 'text-blue-500',   bg: 'bg-blue-100',   sub: 'text-blue-400',   hover: 'hover:bg-blue-50/40' },
}

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
        // calendar / clock icon (same for all for simplicity)
        h('svg', { class: `w-4 h-4 ${c.icon}`, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' },
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' })
        )
      ),
      h('div', { class: 'flex-1 min-w-0' }, [
        h('p', { class: 'text-sm font-semibold text-gray-800 truncate' }, p.item.client_name),
        h('p', { class: 'text-xs text-gray-400 truncate mt-0.5' }, p.item.category),
      ]),
      h('div', { class: 'text-right shrink-0' }, [
        h('p', { class: 'text-xs font-mono text-gray-500' }, p.item.invoice_number),
        h('p', { class: `text-[11px] font-medium mt-0.5 ${c.sub}` }, slots.sub?.()),
      ]),
      h('svg', { class: 'w-4 h-4 text-gray-300 shrink-0', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' },
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 5l7 7-7 7' })
      ),
    ])
  },
})
</script>
