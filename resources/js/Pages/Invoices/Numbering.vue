<template>
  <AppLayout title="Urutan Nomor Invoice">
    <div class="space-y-5">

      <!-- ── Header ─────────────────────────────────────────────── -->
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Urutan Nomor Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            Ledger penomoran ·
            <span v-if="month > 0">{{ monthName(month) }} {{ year }}</span>
            <span v-else>tahun {{ year }}</span>
            · {{ invoices.length }} invoice
          </p>
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
          <Link
            v-for="y in years" :key="y"
            :href="route('invoices.numbering', { year: y })"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors',
              y === year
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600'
            ]"
          >{{ y }}</Link>
        </div>
      </div>

      <!-- ── Month filter pills ─────────────────────────────────── -->
      <div v-if="availableMonths.length > 0" class="flex items-center gap-1.5 flex-wrap">
        <button
          @click="applyFilter({ month: 0 })"
          :class="[
            'px-3 py-1 rounded-lg text-xs font-medium transition-colors',
            month === 0
              ? 'bg-gray-800 text-white'
              : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
          ]"
        >Semua</button>
        <button
          v-for="m in availableMonths" :key="m"
          @click="applyFilter({ month: m })"
          :class="[
            'px-3 py-1 rounded-lg text-xs font-medium transition-colors',
            m === month
              ? 'bg-gray-800 text-white'
              : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-700'
          ]"
        >{{ monthName(m) }}</button>
      </div>

      <!-- ── Summary cards ──────────────────────────────────────── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-indigo-600 rounded-2xl p-4 text-white shadow-sm">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-200">Nomor Berikutnya</p>
          <p class="text-3xl font-bold mt-1 tabular-nums">{{ pad(nextSeq) }}</p>
          <p class="text-[11px] text-indigo-300 mt-1.5">global tahun {{ year }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Total Nilai</p>
          <p class="text-xl font-bold text-gray-900 mt-1 tabular-nums truncate">{{ fmtRp(summary.total_nilai) }}</p>
          <p class="text-[11px] text-gray-400 mt-1.5">{{ invoices.length }} invoice</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Sudah Lunas</p>
          <p class="text-xl font-bold text-emerald-600 mt-1 tabular-nums truncate">{{ fmtRp(summary.total_lunas) }}</p>
          <p class="text-[11px] text-emerald-500 mt-1.5">{{ invoices.filter(i => i.status === 'paid').length }} invoice paid</p>
        </div>
        <div :class="['rounded-2xl border shadow-sm p-4', summary.total_outstanding > 0 ? 'bg-amber-50 border-amber-100' : 'bg-white border-gray-100']">
          <p :class="['text-[11px] font-semibold uppercase tracking-wide', summary.total_outstanding > 0 ? 'text-amber-500' : 'text-gray-400']">Outstanding</p>
          <p :class="['text-xl font-bold mt-1 tabular-nums truncate', summary.total_outstanding > 0 ? 'text-amber-600' : 'text-gray-900']">{{ fmtRp(summary.total_outstanding) }}</p>
          <p :class="['text-[11px] mt-1.5 font-medium', summary.count_overdue > 0 ? 'text-red-500' : 'text-gray-400']">
            {{ summary.count_overdue > 0 ? `${summary.count_overdue} melewati jatuh tempo` : 'belum terbayar' }}
          </p>
        </div>
      </div>

      <!-- ── Filter + Export panel ──────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Filter bar baris utama -->
        <div class="flex items-center gap-2 px-4 py-3 flex-wrap">

          <!-- Toggle advanced filter -->
          <button
            @click="showFilter = !showFilter"
            :class="[
              'flex items-center gap-2 px-3.5 py-2 text-xs font-semibold border rounded-xl transition-all',
              activeFilterCount > 0
                ? 'bg-indigo-600 border-indigo-600 text-white'
                : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
            ]"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filter
            <span v-if="activeFilterCount > 0"
              class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-white text-indigo-600 text-[10px] font-black leading-none">
              {{ activeFilterCount }}
            </span>
          </button>

          <!-- Active filter tags -->
          <div class="flex items-center gap-1.5 flex-wrap flex-1 min-w-0">
            <span v-for="s in filters.statuses" :key="s"
              :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold', STATUS_OPTIONS.find(o => o.value === s)?.tag ?? 'bg-gray-100 text-gray-600']">
              {{ statusLabel(s) }}
              <button @click="removeStatus(s)" class="opacity-70 hover:opacity-100 leading-none">×</button>
            </span>
            <span v-if="filters.client_id && clientName(filters.client_id)"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">
              {{ clientName(filters.client_id) }}
              <button @click="removeClient" class="opacity-70 hover:opacity-100 leading-none">×</button>
            </span>
            <span v-if="filters.overdue_only"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
              Overdue saja
              <button @click="removeOverdue" class="opacity-70 hover:opacity-100 leading-none">×</button>
            </span>
            <span v-if="filters.is_marked"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700">
              Dalam Antrean saja
              <button @click="removeMarked" class="opacity-70 hover:opacity-100 leading-none">×</button>
            </span>
          </div>

          <!-- Reset semua filter -->
          <button v-if="activeFilterCount > 0"
            @click="resetFilters"
            class="text-xs text-gray-400 hover:text-red-500 transition-colors font-medium px-2 py-1.5">
            Reset filter
          </button>

          <!-- Export area: pilih kolom + tombol export -->
          <div class="relative flex items-center gap-1" ref="exportMenuRef">
            <!-- Pilih kolom -->
            <button
              @click="showColumnPicker = !showColumnPicker"
              :class="[
                'flex items-center gap-1.5 px-3 py-2 text-xs font-semibold border rounded-l-xl transition-colors',
                exportColumns.length < COLUMN_OPTIONS.length
                  ? 'bg-emerald-50 border-emerald-300 text-emerald-700'
                  : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
              ]"
              title="Pilih kolom yang diekspor"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
              </svg>
              {{ exportColumns.length }}/{{ COLUMN_OPTIONS.length }}
            </button>
            <!-- Export -->
            <a :href="exportUrl"
              class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-r-xl transition-colors shadow-sm border border-emerald-600">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Export XLSX
            </a>

            <!-- Column picker dropdown -->
            <div v-if="showColumnPicker"
              class="absolute right-0 top-full mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
              <div class="px-3 pb-2 mb-1 border-b border-gray-100 flex items-center justify-between">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Kolom Export</p>
                <div class="flex gap-2">
                  <button @click="exportColumns = COLUMN_OPTIONS.map(c => c.key)"
                    class="text-[10px] text-indigo-500 hover:text-indigo-700 font-semibold">Semua</button>
                  <button @click="exportColumns = []"
                    class="text-[10px] text-gray-400 hover:text-red-500 font-semibold">Reset</button>
                </div>
              </div>
              <label
                v-for="col in COLUMN_OPTIONS" :key="col.key"
                class="flex items-center gap-2.5 px-3 py-1.5 cursor-pointer hover:bg-gray-50 transition-colors">
                <input
                  type="checkbox"
                  :value="col.key"
                  v-model="exportColumns"
                  class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer flex-shrink-0"
                />
                <span class="text-xs text-gray-700">{{ col.label }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Advanced filter panel (collapsible) -->
        <div v-show="showFilter" class="border-t border-gray-100 px-4 py-4 space-y-4 bg-gray-50/40">

          <!-- Status chips -->
          <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Status Invoice
              <span v-if="form.statuses.length" class="text-indigo-500">({{ form.statuses.length }} dipilih)</span>
            </p>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="s in STATUS_OPTIONS" :key="s.value"
                type="button"
                @click="toggleStatus(s.value)"
                :class="[
                  'px-3 py-1.5 rounded-lg border text-xs font-bold transition-all select-none',
                  form.statuses.includes(s.value) ? s.active : s.inactive
                ]"
              >{{ s.label }}</button>
            </div>
          </div>

          <!-- Client dropdown -->
          <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Client
            </p>
            <select v-model="form.client_id"
              class="w-full max-w-xs text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"
              :class="form.client_id ? 'border-indigo-300 bg-indigo-50' : ''">
              <option value="">Semua Client</option>
              <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
            </select>
          </div>

          <!-- Boolean toggles -->
          <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
              </svg>
              Kondisi Khusus
            </p>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                @click="form.overdue_only = !form.overdue_only"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all',
                  form.overdue_only
                    ? 'bg-red-500 border-red-500 text-white'
                    : 'border-red-200 text-red-600 hover:bg-red-50'
                ]"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                </svg>
                Overdue Saja
              </button>
              <button
                type="button"
                @click="form.is_marked = !form.is_marked"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all',
                  form.is_marked
                    ? 'bg-yellow-400 border-yellow-400 text-white'
                    : 'border-yellow-300 text-yellow-700 hover:bg-yellow-50'
                ]"
              >
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                </svg>
                Dalam Antrean Saja
              </button>
            </div>
          </div>

          <!-- Terapkan / Reset -->
          <div class="flex items-center gap-2 pt-1">
            <button type="button" @click="submitFilter"
              class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors">
              Terapkan
            </button>
            <button type="button" @click="resetFilters"
              class="px-4 py-2 text-xs font-semibold text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- ── Tabel ───────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div v-if="invoices.length === 0" class="py-20 text-center">
          <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p class="text-sm text-gray-400">
            Tidak ada invoice
            {{ month > 0 ? `di ${monthName(month)} ${year}` : `di tahun ${year}` }}
            <span v-if="activeFilterCount > 0"> dengan filter aktif</span>
          </p>
          <button v-if="activeFilterCount > 0" @click="resetFilters"
            class="mt-3 text-xs text-indigo-600 hover:underline">
            Hapus filter
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-100 bg-gray-50/60">
                <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide w-16">No.</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nomor Invoice</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Client</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Terbit · Jatuh Tempo</th>
                <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                <th class="text-right px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nominal</th>
                <th class="w-28"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="inv in invoices" :key="inv.id"
                :class="[
                  'border-b border-gray-50 transition-colors group',
                  inv.is_overdue ? 'bg-red-50/40 hover:bg-red-50/70' : 'hover:bg-gray-50/60'
                ]"
              >
                <!-- Seq -->
                <td class="px-5 py-4">
                  <span class="text-sm font-bold text-gray-800 tabular-nums">{{ pad(inv.seq) }}</span>
                </td>

                <!-- Invoice number -->
                <td class="px-4 py-4 cursor-pointer" @click="router.visit(route('invoices.show', inv.id))">
                  <span class="text-xs font-mono text-indigo-600 hover:text-indigo-800 hover:underline bg-indigo-50 px-2 py-1 rounded-lg whitespace-nowrap transition-colors">
                    {{ inv.invoice_number }}
                  </span>
                </td>

                <!-- Client -->
                <td class="px-4 py-4">
                  <span class="text-sm text-gray-800 font-medium">{{ inv.client?.company_name ?? '-' }}</span>
                </td>

                <!-- Dates -->
                <td class="px-4 py-4">
                  <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-gray-500 tabular-nums">{{ fmtDate(inv.issue_date) }}</span>
                    <span class="text-xs tabular-nums" :class="inv.is_overdue ? 'text-red-500 font-semibold' : 'text-gray-400'">
                      {{ inv.is_overdue ? '⚠ ' : '' }}{{ fmtDate(inv.due_date) }}
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-4 py-4">
                  <div class="flex flex-col gap-1">
                    <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold w-fit', statusClass(inv.status)]">
                      {{ statusLabel(inv.status) }}
                    </span>
                    <span v-if="inv.is_overdue" class="text-[10px] text-red-500 font-medium">Lewati jatuh tempo</span>
                    <span v-if="inv.is_marked" class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-yellow-700 bg-yellow-50 border border-yellow-200 px-1.5 py-0.5 rounded w-fit">
                      <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                      Dalam Antrean
                    </span>
                  </div>
                </td>

                <!-- Nominal -->
                <td class="px-5 py-4 text-right">
                  <div class="flex flex-col items-end gap-0.5">
                    <span class="text-sm font-bold tabular-nums" :class="inv.status === 'paid' ? 'text-emerald-600' : 'text-gray-900'">
                      {{ fmtRp(inv.computed_total) }}
                    </span>
                    <span v-if="inv.carried_from_id" class="text-[10px] text-orange-500 font-medium">incl. tunggakan carry</span>
                    <span v-else-if="inv.is_prepay && !inv.prepay_chain_id" class="text-[10px] text-teal-500 font-medium">incl. prepay chain</span>
                    <span v-else-if="inv.is_reaktivasi && !inv.reaktivasi_chain_id" class="text-[10px] text-violet-500 font-medium">incl. reaktivasi</span>
                    <span v-if="inv.status === 'paid'" class="text-[10px] text-emerald-500 font-medium">✓ Lunas</span>
                    <span v-else-if="inv.status === 'draft'" class="text-[10px] text-gray-400">Belum dikirim</span>
                    <span v-else-if="inv.status === 'frozen'" class="text-[10px] text-violet-400">Frozen</span>
                    <span v-else class="text-[10px] font-semibold" :class="inv.is_overdue ? 'text-red-500' : 'text-amber-500'">Belum dibayar</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-3 py-4">
                  <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                    <button @click.stop="openReceipt(inv)" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Receipt">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </button>
                    <a :href="route('invoices.download', inv.id)" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" title="Download PDF" @click.stop>
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                      </svg>
                    </a>
                    <button @click.stop="router.visit(route('invoices.show', inv.id))" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Lihat detail">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Footer totals -->
            <tfoot>
              <tr class="bg-gray-50/80 border-t-2 border-gray-100">
                <td colspan="5" class="px-5 py-3 text-xs font-semibold text-gray-500">
                  Total {{ month > 0 ? monthName(month) : 'Tahun ' + year }}
                  <span v-if="activeFilterCount > 0" class="text-indigo-500">(terfilter)</span>
                </td>
                <td class="px-5 py-3 text-right">
                  <div class="flex flex-col items-end gap-0.5">
                    <span class="text-sm font-bold text-gray-900 tabular-nums">{{ fmtRp(summary.total_nilai) }}</span>
                    <span class="text-[10px] text-gray-400 tabular-nums whitespace-nowrap">
                      Lunas {{ fmtRp(summary.total_lunas) }} · Sisa {{ fmtRp(summary.total_outstanding) }}
                    </span>
                  </div>
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>

    <!-- ── Receipt Modal ───────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="receiptUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="receiptUrl = null"/>
        <div class="relative z-10 w-full max-w-4xl h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
          <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
            <p class="text-sm font-semibold text-gray-700">Preview Receipt</p>
            <div class="flex items-center gap-2">
              <a :href="receiptUrl" target="_blank"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Buka di tab baru
              </a>
              <button @click="receiptUrl = null" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          <iframe :src="receiptUrl" class="flex-1 w-full border-0 bg-gray-50"/>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  invoices:        Array,
  year:            Number,
  month:           Number,
  years:           Array,
  availableMonths: Array,
  nextSeq:         Number,
  summary:         Object,
  clients:         Array,
  filters:         Object,
})

// ── Filter state ─────────────────────────────────────────────
const STATUS_OPTIONS = [
  { value: 'draft',   label: 'Draft',       active: 'bg-gray-500   border-gray-500   text-white', inactive: 'border-gray-300   text-gray-600   hover:bg-gray-50',   tag: 'bg-gray-100 text-gray-600'   },
  { value: 'sent',    label: 'Terkirim',    active: 'bg-blue-500   border-blue-500   text-white', inactive: 'border-blue-300   text-blue-700   hover:bg-blue-50',   tag: 'bg-blue-100 text-blue-700'   },
  { value: 'paid',    label: 'Lunas',       active: 'bg-emerald-500 border-emerald-500 text-white', inactive: 'border-emerald-300 text-emerald-700 hover:bg-emerald-50', tag: 'bg-emerald-100 text-emerald-700' },
  { value: 'unpaid',  label: 'Belum Bayar', active: 'bg-red-500    border-red-500    text-white', inactive: 'border-red-300    text-red-700    hover:bg-red-50',    tag: 'bg-red-100 text-red-700'     },
  { value: 'frozen',  label: 'Frozen',      active: 'bg-violet-500 border-violet-500 text-white', inactive: 'border-violet-300 text-violet-700 hover:bg-violet-50', tag: 'bg-violet-100 text-violet-700' },
  { value: 'carried', label: 'Carried',     active: 'bg-orange-500 border-orange-500 text-white', inactive: 'border-orange-300 text-orange-700 hover:bg-orange-50', tag: 'bg-orange-100 text-orange-700' },
]

const showFilter = ref(false)

const form = reactive({
  statuses:    [...(props.filters?.statuses ?? [])],
  client_id:   props.filters?.client_id ?? '',
  overdue_only: props.filters?.overdue_only ?? false,
  is_marked:   props.filters?.is_marked ?? false,
})

const activeFilterCount = computed(() => {
  let n = form.statuses.length
  if (form.client_id)    n++
  if (form.overdue_only) n++
  if (form.is_marked)    n++
  return n
})

function toggleStatus(val) {
  const idx = form.statuses.indexOf(val)
  idx === -1 ? form.statuses.push(val) : form.statuses.splice(idx, 1)
}

function buildParams(overrides = {}) {
  const p = {
    year:  props.year,
    ...(props.month > 0 ? { month: props.month } : {}),
    ...(form.statuses.length ? { statuses: form.statuses } : {}),
    ...(form.client_id ? { client_id: form.client_id } : {}),
    ...(form.overdue_only ? { overdue_only: 1 } : {}),
    ...(form.is_marked ? { is_marked: 1 } : {}),
    ...overrides,
  }
  // bersihkan undefined/null/0
  return Object.fromEntries(Object.entries(p).filter(([, v]) => v !== undefined && v !== null && v !== '' && v !== 0 && v !== false))
}

function submitFilter() {
  showFilter.value = false
  router.get(route('invoices.numbering'), buildParams(), { preserveScroll: true })
}

function applyFilter(overrides) {
  router.get(route('invoices.numbering'), buildParams(overrides), { preserveScroll: true })
}

function resetFilters() {
  form.statuses    = []
  form.client_id   = ''
  form.overdue_only = false
  form.is_marked   = false
  showFilter.value = false
  router.get(route('invoices.numbering'), { year: props.year, ...(props.month > 0 ? { month: props.month } : {}) }, { preserveScroll: true })
}

function removeStatus(val) {
  form.statuses = form.statuses.filter(s => s !== val)
  router.get(route('invoices.numbering'), buildParams(), { preserveScroll: true })
}
function removeClient()  { form.client_id   = '';    router.get(route('invoices.numbering'), buildParams(), { preserveScroll: true }) }
function removeOverdue() { form.overdue_only = false; router.get(route('invoices.numbering'), buildParams(), { preserveScroll: true }) }
function removeMarked()  { form.is_marked   = false;  router.get(route('invoices.numbering'), buildParams(), { preserveScroll: true }) }

function clientName(id) {
  return props.clients?.find(c => c.id == id)?.company_name ?? ''
}

// ── Export column selector ────────────────────────────────────
const COLUMN_OPTIONS = [
  { key: 'seq',            label: 'Seq'           },
  { key: 'invoice_number', label: 'Nomor Invoice'  },
  { key: 'client',         label: 'Client'         },
  { key: 'category',       label: 'Kategori'       },
  { key: 'issue_date',     label: 'Tanggal Terbit' },
  { key: 'due_date',       label: 'Jatuh Tempo'    },
  { key: 'status',         label: 'Status'         },
  { key: 'overdue',        label: 'Overdue'        },
  { key: 'is_marked',      label: 'Antrean Kirim'  },
  { key: 'nominal',        label: 'Nominal'        },
]

const exportColumns    = ref(COLUMN_OPTIONS.map(c => c.key))
const showColumnPicker = ref(false)
const exportMenuRef    = ref(null)

function handleClickOutsideExport(e) {
  if (exportMenuRef.value && !exportMenuRef.value.contains(e.target)) {
    showColumnPicker.value = false
  }
}
onMounted(() => document.addEventListener('mousedown', handleClickOutsideExport))
onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutsideExport))

// Export URL — ikut semua filter aktif + kolom yang dipilih
const exportUrl = computed(() => {
  const params = new URLSearchParams()
  params.set('year', props.year)
  if (props.month > 0) params.set('month', props.month)
  ;(props.filters?.statuses ?? []).forEach(s => params.append('statuses[]', s))
  if (props.filters?.client_id) params.set('client_id', props.filters.client_id)
  if (props.filters?.overdue_only) params.set('overdue_only', '1')
  if (props.filters?.is_marked) params.set('is_marked', '1')
  exportColumns.value.forEach(c => params.append('columns[]', c))
  return route('invoices.numbering.export') + '?' + params.toString()
})

// ── Receipt modal ─────────────────────────────────────────────
const receiptUrl = ref(null)
function openReceipt(inv) {
  receiptUrl.value = route('invoices.receipt', inv.id)
}

// ── Helpers ───────────────────────────────────────────────────
function pad(n) {
  return String(n).padStart(3, '0')
}

const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']
function monthName(m) {
  return MONTHS[(m - 1)] ?? ''
}

function fmtDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function fmtRp(v) {
  if (!v && v !== 0) return '-'
  return 'Rp ' + Number(v).toLocaleString('id-ID', { maximumFractionDigits: 0 })
}

function statusLabel(s) {
  return { draft: 'Draft', sent: 'Terkirim', paid: 'Lunas', unpaid: 'Belum Bayar', frozen: 'Frozen', carried: 'Carried' }[s] ?? s
}

function statusClass(s) {
  return {
    draft:   'bg-gray-100 text-gray-500',
    sent:    'bg-blue-100 text-blue-600',
    paid:    'bg-emerald-100 text-emerald-600',
    unpaid:  'bg-red-100 text-red-600',
    frozen:  'bg-violet-100 text-violet-600',
    carried: 'bg-orange-100 text-orange-600',
  }[s] ?? 'bg-gray-100 text-gray-500'
}
</script>
