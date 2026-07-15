<template>
  <AppLayout title="Invoice">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-gray-900 tracking-tight">Invoice</h2>
          <p class="text-sm text-gray-400 mt-0.5">
            <template v-if="search || statusFilter !== 'all'">
              {{ filteredClients.length }} dari {{ clients.length }} client
            </template>
            <template v-else>
              {{ clients.length }} client · {{ summary.total_invoices }} invoice
            </template>
          </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button @click="showReset = true"
            class="inline-flex items-center gap-1.5 px-3 py-2 border border-red-200 text-red-500 text-xs font-medium rounded-xl hover:bg-red-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Reset
          </button>
          <button @click="openExport"
            class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 text-gray-600 text-xs font-medium rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export
          </button>
          <button @click="goCreateInvoice()"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Invoice
          </button>
        </div>
      </div>

      <!-- Summary stats -->
      <div class="space-y-3">

        <!-- Grup: Status Pembayaran -->
        <div>
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-0.5">Status Pembayaran</p>
          <div class="grid grid-cols-2 gap-3">

            <!-- Belum Dibayar -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
              <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-full -translate-y-8 translate-x-8 opacity-60"/>
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Belum Dibayar</p>
              <p class="text-xl font-bold text-gray-900 mt-1.5 truncate">{{ fmtShort(summary.total_outstanding) }}</p>
              <p class="text-[10px] text-amber-500 mt-1.5 font-medium">outstanding</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"/>
                <span class="text-[10px] text-gray-400">{{ summary.count_unpaid }} invoice aktif</span>
              </div>
            </div>

            <!-- Lunas -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
              <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -translate-y-8 translate-x-8 opacity-60"/>
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Lunas</p>
              <p class="text-xl font-bold text-gray-900 mt-1.5 truncate">{{ fmtShort(summary.total_paid_amount) }}</p>
              <p class="text-[10px] text-emerald-500 mt-1.5 font-medium">sudah dibayarkan</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>
                <span class="text-[10px] text-gray-400">{{ summary.count_paid }} invoice lunas</span>
              </div>
            </div>

          </div>
        </div>

        <!-- Grup: Status Invoice -->
        <div>
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-0.5">Status Invoice</p>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

            <!-- Overdue -->
            <div class="rounded-2xl border shadow-sm p-4 relative overflow-hidden"
              :class="summary.count_overdue > 0 ? 'bg-red-50/70 border-red-100' : 'bg-white border-gray-100'">
              <div class="absolute top-0 right-0 w-20 h-20 rounded-full -translate-y-8 translate-x-8 opacity-60"
                :class="summary.count_overdue > 0 ? 'bg-red-100' : 'bg-gray-50'"/>
              <p class="text-[11px] font-semibold uppercase tracking-wide"
                :class="summary.count_overdue > 0 ? 'text-red-400' : 'text-gray-400'">Overdue</p>
              <p class="text-xl font-bold mt-1.5"
                :class="summary.count_overdue > 0 ? 'text-red-600' : 'text-gray-900'">{{ summary.count_overdue }}</p>
              <p class="text-[10px] mt-1.5 font-medium"
                :class="summary.count_overdue > 0 ? 'text-red-400' : 'text-gray-300'">invoice jatuh tempo</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full" :class="summary.count_overdue > 0 ? 'bg-red-400' : 'bg-gray-200'"/>
                <span class="text-[10px]" :class="summary.count_overdue > 0 ? 'text-red-400' : 'text-gray-400'">
                  {{ summary.count_overdue > 0 ? 'perlu tindakan segera' : 'semua aman' }}
                </span>
              </div>
            </div>

            <!-- Draft -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
              <div class="absolute top-0 right-0 w-20 h-20 bg-gray-50 rounded-full -translate-y-8 translate-x-8 opacity-60"/>
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Draft Baru</p>
              <p class="text-xl font-bold text-gray-900 mt-1.5">{{ summary.count_draft }}</p>
              <p class="text-[10px] text-gray-400 mt-1.5 font-medium">belum pernah dikirim</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"/>
                <span class="text-[10px] text-gray-400">send_status: unsent</span>
              </div>
            </div>

            <!-- Frozen -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
              <div class="absolute top-0 right-0 w-20 h-20 bg-sky-50 rounded-full -translate-y-8 translate-x-8 opacity-60"/>
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Frozen</p>
              <p class="text-xl font-bold text-gray-900 mt-1.5">{{ summary.count_frozen }}</p>
              <p class="text-[10px] text-sky-500 mt-1.5 font-medium">ditangguhkan</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"/>
                <span class="text-[10px] text-gray-400">{{ summary.total_invoices }} total invoice</span>
              </div>
            </div>

            <!-- Tidak Aktif -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 relative overflow-hidden">
              <div class="absolute top-0 right-0 w-20 h-20 bg-orange-50 rounded-full -translate-y-8 translate-x-8 opacity-60"/>
              <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Tidak Aktif</p>
              <p class="text-xl font-bold text-gray-900 mt-1.5">{{ summary.count_inactive }}</p>
              <p class="text-[10px] text-orange-400 mt-1.5 font-medium">dinonaktifkan</p>
              <div class="flex items-center gap-1.5 mt-2">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-300"/>
                <span class="text-[10px] text-gray-400">bisa diaktifkan kembali</span>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Search + Filter + Sort -->
      <div class="flex flex-col sm:flex-row gap-2.5">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Cari client, kota, PIC..."
            class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 shadow-sm"/>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl shadow-sm p-1">
            <button v-for="f in filterOptions" :key="f.value" @click="statusFilter = f.value"
              class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors whitespace-nowrap"
              :class="statusFilter === f.value
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
              {{ f.label }}
            </button>
          </div>
          <div class="relative">
            <select v-model="sortBy"
              class="appearance-none pl-3 pr-7 py-2 text-xs font-medium bg-white border border-gray-200 rounded-xl shadow-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-300 cursor-pointer">
              <option value="name">A–Z</option>
              <option value="outstanding">Outstanding</option>
              <option value="overdue">Overdue</option>
              <option value="last_invoice">Invoice Terbaru</option>
              <option value="total">Total Lifetime</option>
            </select>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Empty filtered -->
      <div v-if="sortedClients.length === 0 && clients.length > 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-14 text-center">
        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <p class="text-gray-500 text-sm font-medium">Tidak ada client yang sesuai filter</p>
        <button @click="search = ''; statusFilter = 'all'"
          class="mt-2 text-xs text-indigo-500 hover:underline">
          Reset filter
        </button>
      </div>

      <!-- Empty base -->
      <div v-else-if="clients.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
          <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <p class="text-gray-500 text-sm font-semibold">Belum ada client aktif</p>
        <p class="text-gray-400 text-xs mt-1">Tambahkan client dan buat invoice pertama.</p>
      </div>

      <!-- Client cards grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="client in sortedClients" :key="client.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group">

          <!-- Card header -->
          <div class="px-5 pt-5 pb-4">
            <div class="flex items-start gap-3.5">
              <!-- Avatar -->
              <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 text-white font-bold text-base shadow-sm"
                :class="avatarColor(client.company_name)">
                {{ client.company_name.charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-1">{{ client.company_name }}</h3>
                  <div class="flex items-center gap-1 shrink-0">
                    <span v-if="client.overdue_count"
                      class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-lg text-[10px] font-bold bg-red-100 text-red-600">
                      <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                      </svg>
                      {{ client.overdue_count }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                  <span v-if="client.category"
                    class="text-[10px] font-semibold text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-md">
                    {{ client.category.name }}
                  </span>
                  <span v-if="client.city" class="text-[10px] text-gray-400">{{ client.city }}</span>
                </div>
                <p v-if="client.pic" class="text-[11px] text-gray-400 mt-0.5 truncate">{{ client.pic }}</p>
              </div>
            </div>

            <!-- Status pills row -->
            <div class="flex flex-wrap gap-1 mt-3.5">
              <!-- Paid -->
              <span v-if="client.paid_count"
                class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>
                {{ client.paid_count }} Lunas
              </span>
              <!-- Antrean Kirim (verified+unpaid): sent_count = doc=verified, pay=unpaid -->
              <span v-if="client.sent_count"
                class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-600">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"/>
                {{ client.sent_count }} Antrean
                <span v-if="client.unpaid_count > 0" class="font-normal text-amber-400">({{ client.unpaid_count }}× kirim)</span>
              </span>
              <!-- Draft -->
              <span v-if="client.draft_count"
                class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"/>
                {{ client.draft_count }} Draft
              </span>
              <!-- Frozen -->
              <span v-if="client.frozen_count"
                class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-600">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"/>
                {{ client.frozen_count }} Frozen
              </span>
              <span v-if="!totalInvoices(client)" class="text-[10px] text-gray-300 italic">Belum ada invoice</span>
            </div>
          </div>

          <!-- Divider -->
          <div class="h-px bg-gray-50 mx-5"/>

          <!-- Financial info -->
          <div class="px-5 py-3.5 grid grid-cols-2 gap-x-4 gap-y-2.5">
            <div>
              <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Outstanding</p>
              <p class="text-sm font-bold mt-0.5" :class="client.unpaid_amount > 0 ? 'text-red-500' : 'text-gray-300'">
                {{ client.unpaid_amount > 0 ? fmtShort(client.unpaid_amount) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Total Lifetime</p>
              <p class="text-sm font-bold text-gray-700 mt-0.5">
                {{ client.total_amount > 0 ? fmtShort(client.total_amount) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Invoice Terakhir</p>
              <p class="text-[11px] font-medium text-gray-600 mt-0.5">
                {{ client.invoices_max_issue_date ? fmtDate(client.invoices_max_issue_date) : '—' }}
              </p>
            </div>
            <div>
              <p class="text-[9px] text-gray-400 uppercase tracking-wider font-semibold">Jenis Produk</p>
              <p class="text-[11px] font-medium text-gray-600 mt-0.5">
                {{ client.product_type_count ? `${client.product_type_count} kategori` : '—' }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-auto border-t border-gray-50 px-4 py-3 flex items-center gap-2">
            <button @click="goCreateInvoice(client.id)"
              class="flex items-center justify-center gap-1.5 flex-1 text-[11px] px-3 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition-colors font-semibold">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Buat Invoice
            </button>
            <button @click="goViewClient(client.id)"
              class="flex items-center justify-center gap-1.5 flex-1 text-[11px] px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors font-semibold">
              Lihat Invoice
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

        </div>
      </div>

    </div>

    <!-- Export Modal -->
    <Teleport to="body">
      <div v-if="showExport" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showExport = false"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-base font-semibold text-gray-900">Export Invoice</h3>
              <p class="text-xs text-gray-400 mt-0.5">Download laporan ke format XLSX</p>
            </div>
            <button @click="showExport = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="space-y-4">
            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Rentang Bulan</p>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="text-xs text-gray-400 mb-1 block">Dari</label>
                  <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="month" v-model="exportFrom"
                      class="w-full pl-8 pr-2 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-colors"/>
                  </div>
                </div>
                <div>
                  <label class="text-xs text-gray-400 mb-1 block">Sampai</label>
                  <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="month" v-model="exportTo" :min="exportFrom"
                      class="w-full pl-8 pr-2 py-2 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-colors"/>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Status Invoice</p>
              <div class="flex flex-wrap gap-1.5">
                <button v-for="s in exportStatusOptions" :key="s.value"
                  @click="toggleExportStatus(s.value)"
                  class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors"
                  :class="exportStatuses.includes(s.value)
                    ? s.activeClass
                    : 'border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600'">
                  {{ s.label }}
                </button>
              </div>
              <p class="text-[10px] text-gray-400 mt-1.5">
                {{ exportStatuses.length === 0 ? 'Semua status akan di-export' : exportStatuses.length + ' status dipilih' }}
              </p>
            </div>
          </div>

          <div class="flex gap-2 mt-6">
            <button @click="showExport = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="doExport"
              class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download XLSX
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Reset Demo -->
    <Teleport to="body">
      <div v-if="showReset" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showReset = false"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Hapus Semua Invoice?</p>
              <p class="text-xs text-gray-400 mt-0.5">Client tetap aman, hanya invoice yang dihapus.</p>
            </div>
          </div>
          <div class="flex gap-2 mt-5">
            <button @click="showReset = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="doReset"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">
              Ya, Hapus Semua
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { useSecurityGate } from '@/Composables/useSecurityGate'

const props = defineProps({ clients: Array, summary: Object })

const { requireGate } = useSecurityGate()

async function goCreateInvoice(clientId = null) {
  const passed = await requireGate()
  if (!passed) return
  router.visit(clientId ? route('invoices.create', { client_id: clientId }) : route('invoices.create'))
}

async function goViewClient(clientId) {
  const passed = await requireGate()
  if (!passed) return
  router.visit(route('invoices.client', clientId))
}

async function openExport() {
  const passed = await requireGate()
  if (!passed) return
  showExport.value = true
}

const search      = ref('')
const statusFilter = ref('all')
const sortBy      = ref('name')

const filterOptions = [
  { value: 'all',     label: 'Semua' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'unpaid',  label: 'Unpaid' },
  { value: 'sent',    label: 'Sent' },
]

const filteredClients = computed(() => {
  let list = props.clients
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(c =>
      c.company_name.toLowerCase().includes(q) ||
      (c.city || '').toLowerCase().includes(q) ||
      (c.pic  || '').toLowerCase().includes(q)
    )
  }
  if (statusFilter.value === 'overdue') list = list.filter(c => c.overdue_count > 0)
  if (statusFilter.value === 'unpaid')  list = list.filter(c => c.unpaid_count  > 0)
  if (statusFilter.value === 'sent')    list = list.filter(c => c.sent_count    > 0)
  return list
})

const sortedClients = computed(() => {
  const list = [...filteredClients.value]
  if (sortBy.value === 'outstanding')  return list.sort((a, b) => b.unpaid_amount - a.unpaid_amount)
  if (sortBy.value === 'overdue')      return list.sort((a, b) => b.overdue_count - a.overdue_count)
  if (sortBy.value === 'total')        return list.sort((a, b) => b.total_amount - a.total_amount)
  if (sortBy.value === 'last_invoice') return list.sort((a, b) => {
    const da = a.invoices_max_issue_date ? new Date(a.invoices_max_issue_date) : new Date(0)
    const db = b.invoices_max_issue_date ? new Date(b.invoices_max_issue_date) : new Date(0)
    return db - da
  })
  return list.sort((a, b) => a.company_name.localeCompare(b.company_name))
})

// Reset Demo
const showReset = ref(false)
function doReset() {
  router.delete(route('invoices.reset-all'), {
    onSuccess: () => { showReset.value = false },
  })
}

// Export
const showExport      = ref(false)
const now             = new Date()
const thisMonth       = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
const exportFrom      = ref(thisMonth)
const exportTo        = ref(thisMonth)
const exportStatuses  = ref([])

const exportStatusOptions = [
  { value: 'paid',     label: 'Lunas',      activeClass: 'border-emerald-400 bg-emerald-50 text-emerald-700' },
  { value: 'verified', label: 'Aktif',      activeClass: 'border-blue-400 bg-blue-50 text-blue-700' },
  { value: 'draft',    label: 'Draft',      activeClass: 'border-gray-400 bg-gray-100 text-gray-700' },
  { value: 'frozen',   label: 'Dibekukan',  activeClass: 'border-slate-400 bg-slate-100 text-slate-700' },
  { value: 'carried',  label: 'Carry',      activeClass: 'border-orange-400 bg-orange-50 text-orange-700' },
]

function toggleExportStatus(val) {
  const idx = exportStatuses.value.indexOf(val)
  if (idx === -1) exportStatuses.value.push(val)
  else exportStatuses.value.splice(idx, 1)
}

function doExport() {
  const params = new URLSearchParams({ from: exportFrom.value, to: exportTo.value })
  exportStatuses.value.forEach(s => params.append('status[]', s))
  window.location.href = `/invoices/export?${params.toString()}`
  showExport.value = false
}

// Helpers
const totalInvoices = c =>
  (c.draft_count ?? 0) + (c.sent_count ?? 0) + (c.paid_count ?? 0) + (c.frozen_count ?? 0)

function fmtShort(v) {
  if (!v) return 'Rp 0'
  if (v >= 1_000_000_000) return 'Rp ' + (v / 1_000_000_000).toFixed(1).replace('.0', '') + 'M'
  if (v >= 1_000_000)     return 'Rp ' + (v / 1_000_000).toFixed(1).replace('.0', '') + 'jt'
  if (v >= 1_000)         return 'Rp ' + (v / 1_000).toFixed(0) + 'rb'
  return 'Rp ' + v
}

const fmtDate = d =>
  new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })

function avatarColor(name) {
  const palette = [
    'bg-indigo-600', 'bg-blue-600', 'bg-violet-600',
    'bg-emerald-600', 'bg-teal-600', 'bg-rose-600',
    'bg-amber-600', 'bg-cyan-600',
  ]
  return palette[(name?.charCodeAt(0) ?? 0) % palette.length]
}
</script>
