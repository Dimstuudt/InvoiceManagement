<template>
  <AppLayout title="Semua Invoice">
    <div class="relative">

      <!-- Lock overlay -->
      <Transition name="lock-fade">
        <div v-if="!bypassActive"
          class="absolute inset-0 flex items-start justify-center z-20 pt-16">
          <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 px-8 py-10 text-center max-w-sm w-full mx-4">
            <div class="w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 mb-1.5">Halaman Terkunci</h3>
            <p class="text-xs text-gray-400 leading-relaxed">
              Aktifkan Bypass Verifikasi melalui ikon kunci di topbar.
            </p>
          </div>
        </div>
      </Transition>

      <div class="space-y-4 transition-[filter] duration-300"
        :class="!bypassActive ? 'blur-sm pointer-events-none select-none' : ''">

        <!-- ── Header ─────────────────────────────────────────────── -->
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Semua Invoice</h2>
            <p class="text-sm text-gray-400 mt-0.5">
              {{ periodDisplayLabel }} &middot; {{ invoices.length }} invoice
              <span v-if="activeFilterCount > 0" class="text-indigo-500">(terfilter)</span>
            </p>
          </div>

          <!-- Export dropdown -->
          <div class="relative" ref="exportRef">
            <button @click="showExport = !showExport"
              class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Export
              <svg class="w-3 h-3 opacity-70 transition-transform" :class="showExport ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div v-if="showExport"
              class="absolute right-0 top-full mt-1.5 w-60 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih Kolom</p>
                <div class="flex gap-3">
                  <button @click="exportCols = EXPORT_COLS.map(c=>c.key)" class="text-[10px] text-indigo-500 font-semibold hover:text-indigo-700">Semua</button>
                  <button @click="exportCols = []" class="text-[10px] text-gray-400 font-semibold hover:text-red-500">Hapus</button>
                </div>
              </div>
              <div class="px-4 py-3 grid grid-cols-2 gap-x-2 gap-y-1.5">
                <label v-for="c in EXPORT_COLS" :key="c.key"
                  class="flex items-center gap-2 cursor-pointer group">
                  <input type="checkbox" :value="c.key" v-model="exportCols"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer flex-shrink-0"/>
                  <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors">{{ c.label }}</span>
                </label>
              </div>
              <div class="px-4 pb-3">
                <a :href="exportUrl"
                  class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                  Download XLSX
                </a>
                <p class="text-[10px] text-gray-400 text-center mt-1.5 font-mono truncate" :title="exportFilename">{{ exportFilename }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Period bar ──────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
          <!-- Presets + month nav on same row -->
          <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1">
              <button v-for="p in PRESETS" :key="p.key"
                @click="applyPreset(p)"
                :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                  activePreset === p.key
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                {{ p.label }}
              </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1"/>

            <!-- Month navigator -->
            <div class="flex items-center gap-1.5">
              <button @click="shiftMonth(-1)"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <span class="text-xs font-semibold text-gray-600 min-w-[100px] text-center tabular-nums">{{ navMonthLabel }}</span>
              <button @click="shiftMonth(1)"
                :disabled="isAtCurrentMonth"
                :class="['w-7 h-7 flex items-center justify-center rounded-lg border transition-colors',
                  isAtCurrentMonth
                    ? 'border-gray-100 text-gray-300 cursor-not-allowed'
                    : 'border-gray-200 text-gray-500 hover:border-indigo-300 hover:text-indigo-600']">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Date range inputs -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dari</span>
              <input type="date" v-model="localFrom" :max="localTo || todayStr"
                class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-700 font-mono bg-white"
                @change="activePreset = null"/>
            </div>
            <span class="text-gray-300">—</span>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">S/D</span>
              <input type="date" v-model="localTo" :min="localFrom" :max="todayStr"
                class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-700 font-mono bg-white"
                @change="activePreset = null"/>
            </div>
            <button @click="applyDateRange"
              :disabled="!localFrom || !localTo"
              class="flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-100 disabled:text-gray-400 text-white text-xs font-semibold rounded-lg transition-colors">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              Tampilkan
            </button>
          </div>
        </div>

        <!-- ── Filter bar ──────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <!-- Toggle + chips -->
          <div class="flex items-center gap-2 px-4 py-3 flex-wrap min-h-[52px]">
            <button @click="showFilter = !showFilter"
              :class="['flex items-center gap-2 px-3.5 py-2 text-xs font-semibold border rounded-xl transition-all flex-shrink-0',
                activeFilterCount > 0
                  ? 'bg-indigo-600 border-indigo-600 text-white'
                  : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50']">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
              </svg>
              Filter
              <span v-if="activeFilterCount > 0"
                class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-black leading-none bg-white/30">
                {{ activeFilterCount }}
              </span>
            </button>

            <!-- Active chips -->
            <div class="flex items-center gap-1.5 flex-wrap flex-1 min-w-0">
              <span v-for="s in (filters.statuses ?? [])" :key="'s'+s"
                :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold', statusChipClass(s)]">
                {{ statusDisplayLabel(s) }}
                <button @click="removeStatus(s)" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
              <span v-for="s in (filters.payment_statuses ?? [])" :key="'ps'+s"
                :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold',
                  s === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700']">
                {{ s === 'paid' ? 'Lunas' : 'Belum Dibayar' }}
                <button @click="removePaymentStatus(s)" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
              <span v-for="s in (filters.send_statuses ?? [])" :key="'ss'+s"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700">
                {{ SEND_LABELS[s] }}
                <button @click="removeSendStatus(s)" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
              <span v-if="filters.client_id"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">
                {{ clientName(filters.client_id) }}
                <button @click="removeClient" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
              <span v-for="id in (filters.company_ids ?? [])" :key="'co'+id"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                {{ companyName(id) }}
                <button @click="removeCompany(id)" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
              <span v-for="id in (filters.category_ids ?? [])" :key="'ca'+id"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-700">
                {{ categoryName(id) }}
                <button @click="removeCategory(id)" class="opacity-60 hover:opacity-100 font-black leading-none">×</button>
              </span>
            </div>

            <button v-if="activeFilterCount > 0" @click="resetFilters"
              class="text-xs text-gray-400 hover:text-red-500 transition-colors font-medium px-2 py-1 flex-shrink-0">
              Reset
            </button>
          </div>

          <!-- Filter panel -->
          <div v-show="showFilter" class="border-t border-gray-100 px-5 py-5 space-y-6 bg-gray-50/40">

            <!-- Status Pembayaran -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Status Pembayaran</p>
              <div class="flex flex-wrap gap-2">
                <label v-for="s in PAYMENT_OPTIONS" :key="s.key"
                  :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition-all select-none text-xs font-semibold',
                    draftFilter.paymentStatuses.includes(s.key) ? s.active : 'border-gray-200 text-gray-500 bg-white hover:border-gray-300 hover:bg-gray-50']">
                  <input type="checkbox" :value="s.key" v-model="draftFilter.paymentStatuses" class="hidden"/>
                  {{ s.label }}
                </label>
              </div>
            </div>

            <!-- Status Invoice -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Status Invoice</p>
              <div class="flex flex-wrap gap-2">
                <label v-for="s in STATUS_OPTIONS" :key="s.key"
                  :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition-all select-none text-xs font-semibold',
                    draftFilter.statuses.includes(s.key) ? s.active : 'border-gray-200 text-gray-500 bg-white hover:border-gray-300 hover:bg-gray-50']">
                  <input type="checkbox" :value="s.key" v-model="draftFilter.statuses" class="hidden"/>
                  {{ s.label }}
                </label>
              </div>
            </div>

            <!-- Send status -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Send Status</p>
              <div class="flex flex-wrap gap-2">
                <label v-for="(label, key) in SEND_LABELS" :key="key"
                  :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-xl border cursor-pointer transition-all select-none text-xs font-semibold',
                    draftFilter.sendStatuses.includes(key)
                      ? 'border-violet-300 bg-violet-50 text-violet-700'
                      : 'border-gray-200 text-gray-500 bg-white hover:border-gray-300 hover:bg-gray-50']">
                  <input type="checkbox" :value="key" v-model="draftFilter.sendStatuses" class="hidden"/>
                  {{ label }}
                </label>
              </div>
            </div>

            <!-- Client -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Client</p>
              <select v-model="draftFilter.clientId"
                class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white w-full max-w-sm"
                :class="draftFilter.clientId ? 'border-sky-300 bg-sky-50' : ''">
                <option value="">Semua Client</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
              </select>
            </div>

            <!-- Company → Category cascading -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Perusahaan &amp; Kategori</p>
              <div class="space-y-2 max-w-lg">
                <div v-for="co in companies" :key="co.id"
                  class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                  <!-- Company checkbox -->
                  <label class="flex items-center gap-2.5 px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox"
                      :checked="isCompanyChecked(co.id)"
                      v-indeterminate="isCompanyIndeterminate(co.id)"
                      @change="toggleCompany(co.id)"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer flex-shrink-0"/>
                    <span class="text-xs font-bold text-gray-700">{{ co.name }}</span>
                    <span class="ml-auto text-[10px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-md">{{ co.code }}</span>
                  </label>
                  <!-- Category sub-items -->
                  <div v-if="catsForCompany(co.id).length > 0"
                    class="border-t border-gray-100 px-4 py-2.5 bg-gray-50/50 flex flex-wrap gap-1.5">
                    <label v-for="cat in catsForCompany(co.id)" :key="cat.id"
                      :class="['flex items-center gap-1.5 px-2.5 py-1 rounded-lg border cursor-pointer transition-all text-[11px] font-semibold',
                        draftFilter.categoryIds.includes(cat.id)
                          ? 'border-teal-300 bg-teal-50 text-teal-700'
                          : 'border-gray-200 bg-white text-gray-500 hover:border-gray-300']">
                      <input type="checkbox" :value="cat.id" v-model="draftFilter.categoryIds"
                        class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-3 h-3 cursor-pointer flex-shrink-0"/>
                      {{ cat.name }}
                      <span class="font-mono opacity-50 text-[10px]">{{ cat.code }}</span>
                    </label>
                  </div>
                </div>
                <p v-if="!companies?.length" class="text-xs text-gray-400 italic">Tidak ada perusahaan terdaftar.</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 pt-1">
              <button @click="applyFilter"
                class="px-6 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
                Terapkan
              </button>
              <button @click="resetFilters"
                class="px-4 py-2 text-xs font-semibold text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- ── Table ───────────────────────────────────────────────── -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div v-if="invoices.length === 0" class="py-20 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-400">Tidak ada invoice di periode ini</p>
            <button v-if="activeFilterCount > 0" @click="resetFilters"
              class="mt-3 text-xs text-indigo-600 hover:underline">
              Hapus filter
            </button>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
              <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                  <th class="text-left px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide w-12">#</th>
                  <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nomor Invoice</th>
                  <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Client</th>
                  <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide hidden lg:table-cell">Terbit</th>
                  <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide hidden lg:table-cell">Jatuh Tempo</th>
                  <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                  <th class="text-right px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Nominal</th>
                  <th class="w-28 px-3"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="inv in invoices" :key="inv.id"
                  :class="['border-b border-gray-50 transition-colors group',
                    inv.is_overdue ? 'bg-red-50/25 hover:bg-red-50/50' : 'hover:bg-gray-50/60']">

                  <td class="px-5 py-3.5">
                    <span class="text-xs font-bold text-gray-400 tabular-nums font-mono">{{ pad(inv.seq) }}</span>
                  </td>

                  <td class="px-4 py-3.5">
                    <span @click="goToInvoice(inv)"
                      class="text-xs font-mono text-indigo-600 hover:text-indigo-800 cursor-pointer bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded-md transition-colors inline-block whitespace-nowrap">
                      {{ inv.invoice_number }}
                    </span>
                    <p v-if="inv.project_category?.company?.code"
                      class="text-[10px] font-bold text-gray-400 font-mono mt-0.5">
                      {{ inv.project_category.company.code }}
                    </p>
                  </td>

                  <td class="px-4 py-3.5">
                    <span class="text-sm text-gray-800 font-medium">{{ inv.client?.company_name ?? '—' }}</span>
                    <p v-if="inv.project_category?.name" class="text-[10px] text-gray-400 mt-0.5">{{ inv.project_category.name }}</p>
                  </td>

                  <td class="px-4 py-3.5 hidden lg:table-cell">
                    <span class="text-xs text-gray-500 tabular-nums">{{ fmtDate(inv.issue_date) }}</span>
                  </td>

                  <td class="px-4 py-3.5 hidden lg:table-cell">
                    <span class="text-xs tabular-nums"
                      :class="inv.is_overdue ? 'text-red-500 font-semibold' : 'text-gray-400'">
                      {{ fmtDate(inv.due_date) }}
                      <span v-if="inv.is_overdue"> ⚠</span>
                    </span>
                  </td>

                  <td class="px-4 py-3.5">
                    <div class="flex flex-col gap-1">
                      <span :class="['inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold w-fit', statusBadgeClass(inv)]">
                        {{ statusDisplayLabel(computeStatus(inv)) }}
                      </span>
                      <span :class="['inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium w-fit',
                        inv.payment_status === 'paid'
                          ? 'bg-emerald-100 text-emerald-600'
                          : 'bg-orange-50 text-orange-500']">
                        {{ inv.payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar' }}
                      </span>
                    </div>
                  </td>

                  <td class="px-5 py-3.5 text-right">
                    <span class="text-sm font-bold tabular-nums"
                      :class="inv.payment_status === 'paid' ? 'text-emerald-600' : inv.is_overdue ? 'text-red-600' : 'text-gray-900'">
                      {{ fmtRp(inv.computed_total) }}
                    </span>
                    <p class="text-[10px] mt-0.5"
                      :class="inv.payment_status === 'paid' ? 'text-emerald-400' : inv.is_overdue ? 'text-red-400' : 'text-gray-400'">
                      {{ nominalSub(inv) }}
                    </p>
                  </td>

                  <td class="px-3 py-3.5">
                    <div class="flex items-center gap-1 justify-end">
                      <!-- Nonaktifkan: selalu tampil untuk overdue -->
                      <button v-if="inv.is_overdue && inv.document_status !== 'inactive'"
                        @click.stop="confirmDeactivate(inv)"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 hover:border-red-300 transition-colors whitespace-nowrap">
                        Nonaktifkan
                      </button>
                      <!-- Aktifkan: selalu tampil untuk inactive -->
                      <button v-if="inv.document_status === 'inactive'"
                        @click.stop="confirmReactivate(inv)"
                        class="px-2.5 py-1 rounded-lg text-[10px] font-semibold text-blue-600 border border-blue-200 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 transition-colors whitespace-nowrap">
                        Aktifkan
                      </button>
                      <!-- Icon actions: muncul saat hover -->
                      <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click.stop="openReceipt(inv)"
                          class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Receipt">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                        </button>
                        <a :href="route('invoices.download', inv.id)" @click.stop
                          class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" title="Download PDF">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>

              <tfoot>
                <tr class="bg-gray-50/80 border-t-2 border-gray-100">
                  <td colspan="5" class="px-5 py-3 text-xs font-semibold text-gray-500">
                    Total · {{ periodDisplayLabel }}
                  </td>
                  <td colspan="2" class="px-5 py-3 text-right">
                    <span class="text-sm font-bold text-gray-900 tabular-nums">{{ fmtRp(summary.total_nilai) }}</span>
                    <p class="text-[10px] text-gray-400 mt-0.5 tabular-nums whitespace-nowrap">
                      Outstanding <span class="text-blue-500">{{ fmtRp(summary.total_outstanding) }}</span>
                      &middot; Overdue <span class="text-red-500">{{ fmtRp(summary.total_overdue) }}</span>
                    </p>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

      </div><!-- /blur wrapper -->

      <!-- Receipt modal -->
      <Teleport to="body">
        <div v-if="receiptUrl" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="receiptUrl = null"/>
          <div class="relative z-10 w-full max-w-4xl h-[90vh] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
              <p class="text-sm font-semibold text-gray-700">Preview Receipt</p>
              <div class="flex items-center gap-2">
                <a :href="receiptUrl" target="_blank"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                  Buka tab baru
                </a>
                <button @click="receiptUrl = null"
                  class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
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

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed, reactive, onMounted, onBeforeUnmount, watch } from 'vue'
import { useSecurityGate } from '@/Composables/useSecurityGate'
import Swal from 'sweetalert2'

// Directive untuk set .indeterminate (DOM property, tidak bisa pakai :attr biasa)
const vIndeterminate = {
  mounted(el, { value }) { el.indeterminate = !!value },
  updated(el, { value }) { el.indeterminate = !!value },
}

const { bypassActive } = useSecurityGate()

const props = defineProps({
  invoices:   Array,
  from_date:  String,
  to_date:    String,
  clients:    Array,
  companies:  Array,
  categories: Array,
  summary:    Object,
  filters:    Object,
})

// ── Constants ──────────────────────────────────────────────────
const MONTHS = ['Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember']

const PRESETS = [
  { key: 'month', label: 'Bulan Ini' },
  { key: '3m',    label: '3 Bulan'   },
  { key: '6m',    label: '6 Bulan'   },
  { key: 'year',  label: 'Tahun Ini' },
]

// Status invoice — document_status + computed (overdue/outstanding)
const STATUS_OPTIONS = [
  { key: 'overdue',     label: 'Overdue',       active: 'border-red-300 bg-red-50 text-red-700'          },
  { key: 'outstanding', label: 'Outstanding',   active: 'border-blue-300 bg-blue-50 text-blue-700'       },
  { key: 'draft',       label: 'Draft',         active: 'border-gray-400 bg-gray-100 text-gray-700'      },
  { key: 'verified',    label: 'Terverifikasi', active: 'border-amber-300 bg-amber-50 text-amber-700'    },
  { key: 'inactive',    label: 'Tidak Aktif',   active: 'border-gray-300 bg-gray-100 text-gray-500'      },
  { key: 'frozen',      label: 'Frozen',        active: 'border-violet-300 bg-violet-50 text-violet-700' },
  { key: 'carried',     label: 'Carried',       active: 'border-orange-300 bg-orange-50 text-orange-700' },
]

// Status pembayaran — payment_status
const PAYMENT_OPTIONS = [
  { key: 'unpaid', label: 'Belum Dibayar', active: 'border-red-300 bg-red-50 text-red-700'             },
  { key: 'paid',   label: 'Lunas',         active: 'border-emerald-300 bg-emerald-50 text-emerald-700' },
]

const SEND_LABELS = {
  unsent: 'Belum Dikirim',
  send1:  'Terkirim 1×',
  send2:  'Terkirim 2×',
  send3:  'Terkirim 3×',
  send4:  'Terkirim 4×',
  send5:  'Terkirim 5×',
}

const EXPORT_COLS = [
  { key: 'seq',            label: 'Seq'         },
  { key: 'invoice_number', label: 'No. Invoice' },
  { key: 'client',         label: 'Client'      },
  { key: 'category',       label: 'Kategori'    },
  { key: 'issue_date',     label: 'Terbit'      },
  { key: 'due_date',       label: 'Jatuh Tempo' },
  { key: 'status',         label: 'Status'      },
  { key: 'overdue',        label: 'Overdue'     },
  { key: 'nominal',        label: 'Nominal'     },
]

// ── Period / date range ────────────────────────────────────────
const now       = new Date()
const todayStr  = now.toISOString().slice(0, 10)
const localFrom = ref(props.from_date)
const localTo   = ref(props.to_date)
const activePreset = ref(detectPreset(props.from_date, props.to_date))

// Month nav tracks a reference month (default: current)
const navYear  = ref(now.getFullYear())
const navMonth = ref(now.getMonth() + 1)

function isoDate(y, m, d) {
  return `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`
}

function detectPreset(from, to) {
  if (!from || !to) return null
  const n  = new Date()
  const y  = n.getFullYear()
  const m  = n.getMonth() + 1
  if (from === isoDate(y, m, 1) && to === todayStr) return 'month'
  const ago3 = new Date(n); ago3.setMonth(ago3.getMonth()-3); ago3.setDate(1)
  if (from === ago3.toISOString().slice(0,10) && to === todayStr) return '3m'
  const ago6 = new Date(n); ago6.setMonth(ago6.getMonth()-6); ago6.setDate(1)
  if (from === ago6.toISOString().slice(0,10) && to === todayStr) return '6m'
  if (from === `${y}-01-01` && to === todayStr) return 'year'
  return null
}

function presetRange(key) {
  const n = new Date()
  const y = n.getFullYear()
  const m = n.getMonth() + 1
  if (key === 'month') return [isoDate(y, m, 1), todayStr]
  if (key === '3m') {
    const d = new Date(n); d.setMonth(d.getMonth()-3); d.setDate(1)
    return [d.toISOString().slice(0,10), todayStr]
  }
  if (key === '6m') {
    const d = new Date(n); d.setMonth(d.getMonth()-6); d.setDate(1)
    return [d.toISOString().slice(0,10), todayStr]
  }
  if (key === 'year') return [`${y}-01-01`, todayStr]
  return [localFrom.value, localTo.value]
}

function applyPreset(p) {
  const [from, to] = presetRange(p.key)
  activePreset.value = p.key
  localFrom.value    = from
  localTo.value      = to
  navYear.value  = now.getFullYear()
  navMonth.value = now.getMonth() + 1
  fetchWith(from, to)
}

function applyDateRange() {
  if (!localFrom.value || !localTo.value) return
  activePreset.value = detectPreset(localFrom.value, localTo.value)
  fetchWith(localFrom.value, localTo.value)
}

const navMonthLabel = computed(() => `${MONTHS[navMonth.value - 1]} ${navYear.value}`)

const isAtCurrentMonth = computed(() =>
  navYear.value === now.getFullYear() && navMonth.value === now.getMonth() + 1
)

function shiftMonth(dir) {
  let m = navMonth.value + dir
  let y = navYear.value
  if (m > 12) { m = 1;  y++ }
  if (m < 1)  { m = 12; y-- }
  // Don't go into the future
  if (y > now.getFullYear() || (y === now.getFullYear() && m > now.getMonth() + 1)) return
  navYear.value  = y
  navMonth.value = m
  const lastDay  = new Date(y, m, 0).getDate()
  const isCurrent = (y === now.getFullYear() && m === now.getMonth() + 1)
  const from = isoDate(y, m, 1)
  const to   = isCurrent ? todayStr : isoDate(y, m, lastDay)
  localFrom.value    = from
  localTo.value      = to
  activePreset.value = isCurrent ? 'month' : null
  fetchWith(from, to)
}

const periodDisplayLabel = computed(() => {
  const p = PRESETS.find(x => x.key === activePreset.value)
  if (p) return p.label
  if (!localFrom.value || !localTo.value) return '—'
  return `${fmtDate(localFrom.value)} — ${fmtDate(localTo.value)}`
})

function fetchWith(from, to) {
  router.get(route('invoices.numbering'), {
    from_date: from,
    to_date:   to,
    ...buildActivePayload(),
  }, { preserveScroll: true })
}

// ── Filter state ───────────────────────────────────────────────
const showFilter = ref(false)

// Draft: local working copy inside the panel
const draftFilter = reactive({
  statuses:        [...(props.filters?.statuses         ?? [])],
  paymentStatuses: [...(props.filters?.payment_statuses ?? [])],
  sendStatuses:    [...(props.filters?.send_statuses    ?? [])],
  clientId:        props.filters?.client_id ?? '',
  companyIds:      [...(props.filters?.company_ids  ?? [])],
  categoryIds:     [...(props.filters?.category_ids ?? [])],
})

// Applied: what the server currently has (from props)
const filters = computed(() => props.filters ?? {
  statuses: [], send_statuses: [], client_id: null, company_ids: [], category_ids: [],
})

const activeFilterCount = computed(() => {
  const f = filters.value
  return (f.statuses?.length         ?? 0)
    +    (f.payment_statuses?.length  ?? 0)
    +    (f.send_statuses?.length     ?? 0)
    +    (f.client_id ? 1 : 0)
    +    (f.company_ids?.length       ?? 0)
    +    (f.category_ids?.length      ?? 0)
})

function buildActivePayload() {
  const f = filters.value
  const p = {}
  if (f.statuses?.length)         p.statuses         = f.statuses
  if (f.payment_statuses?.length) p.payment_statuses = f.payment_statuses
  if (f.send_statuses?.length)    p.send_statuses    = f.send_statuses
  if (f.client_id)                p.client_id        = f.client_id
  if (f.company_ids?.length)      p.company_ids      = f.company_ids
  if (f.category_ids?.length)     p.category_ids     = f.category_ids
  return p
}

function applyFilter() {
  showFilter.value = false
  const p = { from_date: localFrom.value, to_date: localTo.value }
  if (draftFilter.statuses.length)         p.statuses         = draftFilter.statuses
  if (draftFilter.paymentStatuses.length)  p.payment_statuses = draftFilter.paymentStatuses
  if (draftFilter.sendStatuses.length)     p.send_statuses    = draftFilter.sendStatuses
  if (draftFilter.clientId)                p.client_id        = draftFilter.clientId
  if (draftFilter.companyIds.length)       p.company_ids      = draftFilter.companyIds
  if (draftFilter.categoryIds.length)      p.category_ids     = draftFilter.categoryIds
  router.get(route('invoices.numbering'), p, { preserveScroll: true })
}

function resetFilters() {
  draftFilter.statuses         = []
  draftFilter.paymentStatuses  = []
  draftFilter.sendStatuses     = []
  draftFilter.clientId         = ''
  draftFilter.companyIds       = []
  draftFilter.categoryIds      = []
  showFilter.value             = false
  router.get(route('invoices.numbering'), { from_date: localFrom.value, to_date: localTo.value }, { preserveScroll: true })
}

// Instant-remove from chips (apply current period + remaining filters)
function chipRemove(overrides) {
  const f = filters.value
  const p = {
    from_date:    localFrom.value,
    to_date:      localTo.value,
    statuses:     f.statuses      ?? [],
    send_statuses:f.send_statuses ?? [],
    client_id:    f.client_id,
    company_ids:  f.company_ids   ?? [],
    category_ids: f.category_ids  ?? [],
    ...overrides,
  }
  // Clean empty arrays/nulls
  Object.keys(p).forEach(k => { if (Array.isArray(p[k]) && !p[k].length) delete p[k]; if (p[k] === null || p[k] === '') delete p[k] })
  router.get(route('invoices.numbering'), p, { preserveScroll: true })
}
function removeStatus(s)         { chipRemove({ statuses:         (filters.value.statuses         ?? []).filter(x=>x!==s) }) }
function removePaymentStatus(s)  { chipRemove({ payment_statuses: (filters.value.payment_statuses ?? []).filter(x=>x!==s) }) }
function removeSendStatus(s)     { chipRemove({ send_statuses:    (filters.value.send_statuses    ?? []).filter(x=>x!==s) }) }
function removeClient()          { chipRemove({ client_id: null }) }
function removeCompany(id)       { chipRemove({ company_ids:      (filters.value.company_ids      ?? []).filter(x=>x!=id) }) }
function removeCategory(id)      { chipRemove({ category_ids:     (filters.value.category_ids     ?? []).filter(x=>x!=id) }) }

// Sync draft when server responds
watch(() => props.filters, (f) => {
  if (!f) return
  draftFilter.statuses         = [...(f.statuses         ?? [])]
  draftFilter.paymentStatuses  = [...(f.payment_statuses ?? [])]
  draftFilter.sendStatuses     = [...(f.send_statuses    ?? [])]
  draftFilter.clientId         = f.client_id ?? ''
  draftFilter.companyIds       = [...(f.company_ids      ?? [])]
  draftFilter.categoryIds      = [...(f.category_ids     ?? [])]
}, { deep: true })

// Sync localFrom/localTo when server navigates
watch(() => [props.from_date, props.to_date], ([f, t]) => {
  localFrom.value    = f
  localTo.value      = t
  activePreset.value = detectPreset(f, t)
})

// ── Company → Category cascading ──────────────────────────────
function catsForCompany(coId) {
  return (props.categories ?? []).filter(c => c.company_id == coId)
}

function isCompanyChecked(coId) {
  const cats = catsForCompany(coId)
  if (!cats.length) return draftFilter.companyIds.includes(coId)
  return cats.every(c => draftFilter.categoryIds.includes(c.id))
}

function isCompanyIndeterminate(coId) {
  const cats    = catsForCompany(coId)
  if (!cats.length) return false
  const checked = cats.filter(c => draftFilter.categoryIds.includes(c.id))
  return checked.length > 0 && checked.length < cats.length
}

function toggleCompany(coId) {
  const cats = catsForCompany(coId)
  if (!cats.length) {
    const i = draftFilter.companyIds.indexOf(coId)
    if (i === -1) draftFilter.companyIds.push(coId)
    else draftFilter.companyIds.splice(i, 1)
    return
  }
  if (isCompanyChecked(coId)) {
    draftFilter.categoryIds = draftFilter.categoryIds.filter(id => !cats.find(c => c.id === id))
  } else {
    cats.forEach(c => { if (!draftFilter.categoryIds.includes(c.id)) draftFilter.categoryIds.push(c.id) })
  }
}

// ── Status helpers ─────────────────────────────────────────────
function computeStatus(inv) {
  if (inv.document_status === 'inactive') return 'inactive'
  if (inv.document_status === 'frozen')   return 'frozen'
  if (inv.document_status === 'carried')  return 'carried'
  if (inv.payment_status  === 'paid')     return 'paid'
  if (inv.document_status === 'draft')    return 'draft'
  if (inv.is_overdue)                     return 'overdue'
  if (inv.is_outstanding)                 return 'outstanding'
  return 'verified'
}

function statusDisplayLabel(s) {
  return STATUS_OPTIONS.find(x => x.key === s)?.label ?? s
}

function statusBadgeClass(inv) {
  const map = {
    draft:       'bg-gray-100 text-gray-500',
    outstanding: 'bg-blue-100 text-blue-600',
    overdue:     'bg-red-100 text-red-600',
    paid:        'bg-emerald-100 text-emerald-600',
    inactive:    'bg-gray-100 text-gray-400',
    frozen:      'bg-violet-100 text-violet-600',
    carried:     'bg-orange-100 text-orange-600',
    verified:    'bg-amber-100 text-amber-600',
  }
  return map[computeStatus(inv)] ?? 'bg-gray-100 text-gray-500'
}

function statusChipClass(s) {
  const chipMap = {
    overdue:     'bg-red-100 text-red-700',
    outstanding: 'bg-blue-100 text-blue-700',
    draft:       'bg-gray-100 text-gray-600',
    verified:    'bg-amber-100 text-amber-700',
    inactive:    'bg-gray-100 text-gray-500',
    frozen:      'bg-violet-100 text-violet-700',
    carried:     'bg-orange-100 text-orange-700',
  }
  return chipMap[s] ?? 'bg-gray-100 text-gray-600'
}

function nominalSub(inv) {
  if (inv.payment_status  === 'paid')     return '✓ Lunas'
  if (inv.document_status === 'draft')    return 'Belum dikirim'
  if (inv.document_status === 'inactive') return 'Tidak aktif'
  if (inv.document_status === 'frozen')   return 'Frozen'
  if (inv.is_overdue)                     return 'Lewat jatuh tempo'
  if (inv.is_outstanding)                 return 'Sedang berjalan'
  return 'Belum dibayar'
}

// ── Actions ────────────────────────────────────────────────────
function goToInvoice(inv) {
  if (inv.client_id) router.visit(route('invoices.client', inv.client_id) + '?highlight=' + inv.id)
}

async function confirmDeactivate(inv) {
  const r = await Swal.fire({
    icon: 'warning', title: 'Nonaktifkan Invoice?',
    html: `<b>${inv.invoice_number}</b><br><span style="font-size:13px;color:#6b7280">Invoice ditandai tidak aktif. Bisa diaktifkan kembali kapan saja.</span>`,
    showCancelButton: true, reverseButtons: true,
    confirmButtonColor: '#6B7280', cancelButtonColor: '#E5E7EB',
    confirmButtonText: 'Ya, Nonaktifkan', cancelButtonText: 'Batal',
  })
  if (r.isConfirmed) router.post(route('invoices.deactivate', inv.id), {}, { preserveScroll: true })
}

async function confirmReactivate(inv) {
  const r = await Swal.fire({
    icon: 'question', title: 'Aktifkan Kembali?',
    html: `<b>${inv.invoice_number}</b><br><span style="font-size:13px;color:#6b7280">Invoice akan kembali ke status aktif (verified).</span>`,
    showCancelButton: true, reverseButtons: true,
    confirmButtonColor: '#3B82F6', cancelButtonColor: '#E5E7EB',
    confirmButtonText: 'Ya, Aktifkan', cancelButtonText: 'Batal',
  })
  if (r.isConfirmed) router.post(route('invoices.reactivate', inv.id), {}, { preserveScroll: true })
}

const receiptUrl = ref(null)
function openReceipt(inv) { receiptUrl.value = route('invoices.receipt', inv.id) }

// ── Export ─────────────────────────────────────────────────────
const showExport = ref(false)
const exportRef  = ref(null)
const exportCols = ref(EXPORT_COLS.map(c => c.key))

const exportUrl = computed(() => {
  const p = new URLSearchParams()
  p.set('from_date', localFrom.value || props.from_date)
  p.set('to_date',   localTo.value   || props.to_date)
  const f = filters.value
  f.statuses?.forEach(s          => p.append('statuses[]',         s))
  f.payment_statuses?.forEach(s  => p.append('payment_statuses[]', s))
  f.send_statuses?.forEach(s     => p.append('send_statuses[]',    s))
  if (f.client_id)             p.set('client_id', f.client_id)
  f.company_ids?.forEach(id  => p.append('company_ids[]',  id))
  f.category_ids?.forEach(id => p.append('category_ids[]', id))
  exportCols.value.forEach(c => p.append('columns[]', c))
  return route('invoices.numbering.export') + '?' + p.toString()
})

const exportFilename = computed(() =>
  `invoice-${localFrom.value || props.from_date}-sd-${localTo.value || props.to_date}.xlsx`
)

function closeExport(e) {
  if (exportRef.value && !exportRef.value.contains(e.target)) showExport.value = false
}
onMounted(()      => document.addEventListener('mousedown', closeExport))
onBeforeUnmount(() => document.removeEventListener('mousedown', closeExport))

// ── Lookup helpers ─────────────────────────────────────────────
function clientName(id)   { return props.clients?.find(c => c.id == id)?.company_name ?? '' }
function companyName(id)  { return props.companies?.find(c => c.id == id)?.name ?? '' }
function categoryName(id) { return props.categories?.find(c => c.id == id)?.name ?? '' }

// ── Format helpers ─────────────────────────────────────────────
function pad(n) { return String(n).padStart(3, '0') }

function fmtDate(d) {
  if (!d) return '—'
  // Laravel bisa kirim 'YYYY-MM-DD' atau 'YYYY-MM-DDTHH:...' — normalize dulu
  const s  = typeof d === 'string' && d.includes('T') ? d : d + 'T00:00:00'
  const dt = new Date(s)
  if (isNaN(dt.getTime())) return '—'
  return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function fmtRp(v) {
  if (v == null) return '—'
  return 'Rp ' + Number(v).toLocaleString('id-ID', { maximumFractionDigits: 0 })
}

</script>

<style scoped>
.lock-fade-enter-active { transition: opacity .2s ease, transform .25s cubic-bezier(.34,1.56,.64,1); }
.lock-fade-leave-active { transition: opacity .15s ease; }
.lock-fade-enter-from   { opacity: 0; transform: translateY(-8px) scale(.97); }
.lock-fade-leave-to     { opacity: 0; }
</style>
