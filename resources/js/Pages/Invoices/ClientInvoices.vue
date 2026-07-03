<template>
  <AppLayout :title="client.company_name">
    <div class="space-y-4">

      <!-- Back -->
      <div>
        <Link :href="route('invoices.index')"
          class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Kembali ke daftar client
        </Link>
      </div>

      <!-- Client Profile Card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start gap-4">
          <!-- Avatar -->
          <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-xl font-bold shrink-0 shadow-sm">
            {{ client.company_name?.charAt(0)?.toUpperCase() ?? '?' }}
          </div>
          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3 flex-wrap">
              <div>
                <h2 class="text-lg font-bold text-gray-900 leading-tight">{{ client.company_name }}</h2>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                  <span v-if="client.category"
                    class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                    {{ client.category.name }}
                  </span>
                  <span v-if="client.city" class="text-xs text-gray-400">{{ client.city }}</span>
                  <span class="text-xs text-gray-300">·</span>
                  <span class="text-xs text-gray-400">{{ invoices.length }} invoice</span>
                </div>
              </div>
              <Link :href="route('invoices.create', { client_id: client.id })"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Invoice
              </Link>
            </div>
            <!-- Meta row -->
            <div class="flex flex-wrap gap-x-6 gap-y-2 mt-3.5 pt-3.5 border-t border-gray-50">
              <div v-if="client.pic">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">PIC</p>
                <p class="text-sm text-gray-700 mt-0.5">{{ client.pic }}</p>
              </div>
              <div v-if="client.emails?.length > 0">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Email</p>
                <p class="text-sm text-gray-700 mt-0.5">{{ client.emails.map(e => e.email).join(' · ') }}</p>
              </div>
              <div v-if="client.address">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Alamat</p>
                <p class="text-sm text-gray-700 mt-0.5">{{ client.address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="invoices.length === 0"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Belum ada invoice untuk client ini.</p>
      </div>

      <template v-else>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
              <svg class="w-4.5 h-4.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <p class="text-[11px] text-gray-400 font-medium">Total Invoice</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ invoices.length }}</p>
            <p class="text-[10px] text-gray-300 mt-1">semua status</p>
          </div>

          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
              <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-[11px] text-gray-400 font-medium">Sudah Dibayar</p>
            <p class="text-base font-bold text-gray-800 mt-0.5 truncate">{{ fmtCurrency(summary.paid) }}</p>
            <p class="text-[10px] text-emerald-500 mt-1">{{ summary.paidCount }} invoice</p>
          </div>

          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
              <svg class="w-4.5 h-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-[11px] text-gray-400 font-medium">Menunggu Bayar</p>
            <p class="text-base font-bold text-gray-800 mt-0.5 truncate">{{ fmtCurrency(summary.pending) }}</p>
            <p class="text-[10px] text-amber-500 mt-1">{{ summary.pendingCount }} invoice</p>
          </div>

          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
              <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <p class="text-[11px] text-gray-400 font-medium">Layanan Berulang</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ recurringGroups.length }}</p>
            <p class="text-[10px] text-indigo-400 mt-1">produk aktif</p>
          </div>

        </div>

        <!-- Tips Panel -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <button @click="tipsOpen = !tipsOpen"
            class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
              </svg>
              <span class="text-xs font-semibold text-gray-600">Cara pakai fitur</span>
            </div>
            <svg class="w-4 h-4 text-gray-300 transition-transform" :class="tipsOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="tipsOpen" class="border-t border-gray-50 px-5 py-4 grid sm:grid-cols-2 gap-4">

            <!-- Tip: Quick actions -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Aksi cepat</p>
                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Arahkan kursor ke baris invoice untuk memunculkan tombol aksi:
                  <span class="font-medium text-emerald-600">Paid</span> (tandai lunas),
                  <span class="font-medium text-sky-600">Freeze</span> (bekukan), atau
                  <span class="font-medium text-indigo-600">Perbarui</span> (lanjutkan yang beku).
                </p>
              </div>
            </div>

            <!-- Tip: Freeze -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-sky-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M5.636 5.636l12.728 12.728M18.364 5.636L5.636 18.364"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Freeze — tunda perpanjangan</p>
                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Klik <span class="font-medium text-sky-600">Freeze</span> di invoice Draft/Sent untuk membekukannya. Invoice tidak akan dihitung jatuh tempo. Saat client mau lanjut, klik <span class="font-medium text-indigo-600">Perbarui</span> — pilih tanggal mulai baru dan durasi perpanjangan.</p>
              </div>
            </div>

            <!-- Tip: Chain -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Perpanjangan otomatis</p>
                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Saat invoice recurring di-Paid, sistem otomatis membuat draft invoice berikutnya sesuai interval yang sudah diset (↻ N bln). Timeline menampilkan jarak antar periode dengan badge <span class="font-medium text-indigo-500">↻ N bln</span>.</p>
              </div>
            </div>

            <!-- Tip: Carry -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Carry — utang ke periode berikutnya</p>
                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Klik <span class="font-medium text-orange-600">Carry</span> di invoice Sent/Unpaid yang punya interval. Invoice lama jadi <span class="font-semibold bg-orange-50 text-orange-600 px-1 rounded">Carried</span>, invoice baru dibuat dengan <span class="font-medium">tunggakan</span> bulan lama otomatis masuk di PDF dan detail invoice.</p>
              </div>
            </div>

            <!-- Tip: Reaktivasi -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Reaktivasi — hidupkan kembali layanan</p>
                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">
                  Digunakan saat client berhenti langganan lalu mau lanjut lagi.
                  Klik <span class="font-medium text-violet-600">Reaktivasi</span> di invoice terakhir yang sudah <span class="font-semibold bg-emerald-50 text-emerald-700 px-1 rounded">Paid</span> →
                  isi tanggal mulai baru + durasi → sistem akan generate ulang invoice-invoice baru sesuai interval,
                  termasuk periode yang terlewat selama jeda. Hasilnya muncul sebagai chain baru di tab yang sama.
                </p>
              </div>
            </div>

            <!-- Tip: Status -->
            <div class="flex gap-3">
              <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold text-gray-700">Arti status</p>
                <div class="flex flex-wrap gap-1.5 mt-1.5">
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600">Sent</span>
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Dibayarkan</span>
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-red-50 text-red-600">Unpaid</span>
                  <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-sky-100 text-sky-600">Frozen</span>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ── ALL INVOICES (unified tabs) ───────────────────────────── -->
        <div v-if="recurringGroups.length > 0 || standaloneInvoices.length > 0">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <!-- Tab bar -->
            <div class="flex overflow-x-auto border-b border-gray-100 scrollbar-none">

              <!-- Recurring tabs -->
              <button v-for="(group, i) in recurringGroups" :key="group.root.id"
                @click="activeTab = i"
                class="flex-shrink-0 flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                :class="activeTab === i
                  ? 'border-indigo-500 text-indigo-600 bg-indigo-50/60'
                  : 'border-transparent text-gray-400 hover:text-gray-700 hover:bg-gray-50'">
                <span class="w-2 h-2 rounded-full shrink-0"
                  :class="hasOverdue(group) ? 'bg-red-400' : activeInvoice(group)?.status === 'sent' ? 'bg-blue-400' : activeInvoice(group)?.status === 'paid' ? 'bg-emerald-400' : 'bg-gray-300'"/>
                {{ recurringTabLabels[i] }}
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                  :class="activeTab === i ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400'">
                  {{ group.invoices.length }}
                </span>
              </button>

              <!-- Mandiri tab (selalu paling kanan, hanya tampil kalau ada) -->
              <button v-if="standaloneInvoices.length > 0"
                @click="activeTab = recurringGroups.length"
                class="flex-shrink-0 flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
                :class="activeTab === recurringGroups.length
                  ? 'border-gray-500 text-gray-700 bg-gray-50/60'
                  : 'border-transparent text-gray-400 hover:text-gray-700 hover:bg-gray-50'">
                Invoice Mandiri
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                  :class="activeTab === recurringGroups.length ? 'bg-gray-200 text-gray-600' : 'bg-gray-100 text-gray-400'">
                  {{ standaloneInvoices.length }}
                </span>
              </button>
            </div>

            <!-- ── Tab content: Recurring ── -->
            <template v-if="activeTab < recurringGroups.length && recurringGroups[activeTab]">
              <div :key="'rec-' + activeTab">
                <!-- Summary bar -->
                <div class="px-5 py-3 flex items-center justify-between gap-4 border-b border-gray-50"
                  :class="chainHeaderClass(recurringGroups[activeTab])">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span v-if="recurringGroups[activeTab].invoices[0]?.interval_months"
                      class="text-xs font-medium text-violet-700 bg-violet-100 px-2 py-0.5 rounded-full">
                      ↻ tiap {{ recurringGroups[activeTab].invoices[0].invoice_type === 'yearly'
                        ? (recurringGroups[activeTab].invoices[0].interval_months / 12) + ' tahun'
                        : recurringGroups[activeTab].invoices[0].interval_months + ' bulan' }}
                    </span>
                    <span class="text-xs text-gray-400">
                      {{ recurringGroups[activeTab].invoices.length }} periode &bull; mulai {{ fmtDate(recurringGroups[activeTab].root.issue_date) }}
                    </span>
                    <span v-if="hasOverdue(recurringGroups[activeTab])"
                      class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">
                      Jatuh tempo
                    </span>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-gray-900">{{ fmtCurrency(chainTotal(recurringGroups[activeTab])) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">
                      <span class="text-emerald-600 font-semibold">{{ fmtCurrency(chainPaidTotal(recurringGroups[activeTab])) }}</span> terbayar
                    </p>
                  </div>
                </div>

                <!-- Timeline -->
                <div>
                  <template v-if="recurringGroups[activeTab]?.invoices.length > 0">

                    <!-- ── Single loop: urutan kronologis dipertahankan ── -->
                    <template v-for="(invoice, idx) in recurringGroups[activeTab].invoices" :key="invoice.id">

                      <!-- Full-size: kode normal (no C-/R- prefix) -->
                      <div v-if="!isSmallSubCode(invoice)"
                        class="flex items-start border-t border-gray-50 transition-colors group"
                        :class="invoice.carried_from_id ? 'bg-emerald-50/20 hover:bg-emerald-50/40' : 'hover:bg-gray-50/60'">
                        <div class="flex flex-col items-center w-[57px] shrink-0 pt-3.5 self-stretch">
                          <div class="w-6 h-6 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm shrink-0 z-10" :class="dotClass(invoice)">
                            <svg v-if="invoice.status === 'paid'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <div v-else class="w-1.5 h-1.5 rounded-full bg-white"/>
                          </div>
                          <div v-if="idx < recurringGroups[activeTab].invoices.length - 1"
                            class="flex-1 w-px mt-1"
                            :class="invoice.status === 'paid' ? 'bg-emerald-300' : 'bg-gray-200'"/>
                        </div>
                        <div class="flex-1 min-w-0 py-3 pr-4">
                          <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                              <div class="flex items-center gap-2 flex-wrap">
                                <span v-if="invoice.carried_from_id"
                                  class="inline-flex items-center gap-0.5 text-[9px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 px-1.5 py-0.5 rounded uppercase tracking-wide shrink-0">
                                  <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                  Carry Head
                                </span>
                                <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                                  class="font-mono text-sm font-semibold text-indigo-600 hover:text-indigo-800 hover:underline truncate">
                                  {{ invoice.invoice_number }}
                                </Link>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold shrink-0" :class="statusClass(invoice.status)">
                                  {{ statusLabel(invoice.status) }}
                                </span>
                                <span v-if="isPastDue(invoice)" class="text-[10px] font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded shrink-0">
                                  lewat {{ daysPastDue(invoice) }} hari
                                </span>
                              </div>
                              <p class="text-xs text-gray-500 mt-1">{{ fmtDateShort(invoice.issue_date) }} <span class="text-gray-300">→</span> {{ fmtDateShort(invoice.due_date) }}</p>
                              <p v-if="idx === 0" class="text-[10px] text-gray-300 mt-0.5">Dibuat {{ fmtDateTime(invoice.created_at) }}</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                              <div class="text-right mr-1">
                                <p class="text-sm font-semibold text-gray-800 whitespace-nowrap">{{ fmtCurrency(invoiceTotal(invoice)) }}</p>
                                <p v-if="invoice.tax_percentage" class="text-[10px] text-violet-500 mt-0.5">+PPN {{ invoice.tax_percentage }}%</p>
                              </div>
                              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button v-if="invoice.status === 'sent'" @click="markPaid(invoice)" class="px-2 py-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md transition-colors">Paid</button>
                                <button v-if="['draft','sent','unpaid'].includes(invoice.status) && invoice.interval_months && !hasChild(invoice)" @click="carryInvoice(invoice)" class="px-2 py-1 text-[10px] font-semibold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-md transition-colors">Carry</button>
                                <button v-if="['draft','sent','unpaid'].includes(invoice.status) && invoice.interval_months && !invoice.prepay_chain_id" @click="prepayInvoice(invoice)" class="px-2 py-1 text-[10px] font-semibold text-teal-600 bg-teal-50 hover:bg-teal-100 rounded-md transition-colors">Prepay</button>
                                <button v-if="invoice.status === 'unpaid' && invoice.interval_months && !hasChild(invoice) && !invoice.is_reaktivasi" @click="reactivateInvoice(invoice)" class="px-2 py-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md transition-colors">Reaktivasi</button>
                                <button v-if="['draft','sent'].includes(invoice.status) && invoice.parent_invoice_id" @click="freezeInvoice(invoice)" class="px-2 py-1 text-[10px] font-semibold text-sky-600 bg-sky-50 hover:bg-sky-100 rounded-md transition-colors">Freeze</button>
                                <button v-if="invoice.status === 'frozen' && !hasChild(invoice)" @click="openResume(invoice)" class="px-2 py-1 text-[10px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors">Perbarui</button>
                                <button @click.stop="toggleMenu($event, invoice)" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <button @click="deleteInvoice(invoice)" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Compact inline: C-/R- prefix (indented, posisi kronologis) -->
                      <div v-else
                        class="border-t border-gray-50 group"
                        :class="invoice.invoice_number.startsWith('C-') ? 'hover:bg-orange-50/40' : invoice.invoice_number.startsWith('P-') ? 'hover:bg-teal-50/30' : invoice.invoice_number.startsWith('F-') ? 'hover:bg-sky-50/30' : 'hover:bg-violet-50/30'">
                        <div class="ml-7 flex items-start"
                          :class="idx < recurringGroups[activeTab].invoices.length - 1
                            ? (invoice.status === 'paid' ? 'border-l-2 border-emerald-200' : 'border-l-2 border-gray-100')
                            : ''">
                          <div class="flex items-start w-[30px] shrink-0 pt-2.5">
                            <div class="w-3 h-px mt-1.5 shrink-0" :class="invoice.status === 'paid' ? 'bg-emerald-200' : 'bg-gray-200'"/>
                            <div class="w-3.5 h-3.5 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm shrink-0" :class="dotClass(invoice)">
                              <svg v-if="invoice.status === 'paid'" class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                              </svg>
                              <div v-else class="w-1 h-1 rounded-full bg-white"/>
                            </div>
                          </div>
                          <div class="flex-1 min-w-0 py-2 pr-4">
                            <div class="flex items-center justify-between gap-2">
                              <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                  <span v-if="invoice.invoice_number.startsWith('C-')"
                                    class="text-[9px] font-bold text-orange-700 bg-orange-100 border border-orange-200 px-1 py-0.5 rounded uppercase tracking-wide shrink-0">
                                    Carry
                                  </span>
                                  <span v-else-if="invoice.invoice_number.startsWith('P-')"
                                    class="text-[9px] font-bold text-teal-700 bg-teal-100 border border-teal-200 px-1 py-0.5 rounded uppercase tracking-wide shrink-0">
                                    Prepay
                                  </span>
                                  <span v-else-if="invoice.invoice_number.startsWith('F-')"
                                    class="text-[9px] font-bold text-sky-700 bg-sky-100 border border-sky-200 px-1 py-0.5 rounded uppercase tracking-wide shrink-0">
                                    Frozen
                                  </span>
                                  <span v-else
                                    class="text-[9px] font-bold text-violet-700 bg-violet-100 border border-violet-200 px-1 py-0.5 rounded uppercase tracking-wide shrink-0">
                                    Reaktivasi
                                  </span>
                                  <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                                    class="font-mono text-xs font-semibold text-gray-500 hover:text-indigo-600 hover:underline truncate">
                                    {{ invoice.invoice_number }}
                                  </Link>
                                  <span class="px-1 py-0.5 rounded text-[9px] font-semibold shrink-0" :class="statusClass(invoice.status)">
                                    {{ statusLabel(invoice.status) }}
                                  </span>
                                  <span v-if="isPastDue(invoice)" class="text-[9px] font-semibold text-red-500 shrink-0">lewat {{ daysPastDue(invoice) }}h</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ fmtDateShort(invoice.issue_date) }} → {{ fmtDateShort(invoice.due_date) }}</p>
                              </div>
                              <div class="flex items-center gap-0.5 shrink-0">
                                <p class="text-xs font-semibold text-gray-500 whitespace-nowrap mr-1">{{ fmtCurrency(invoiceTotal(invoice)) }}</p>
                                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                  <Link :href="route('invoices.edit', invoice.id)" class="px-1.5 py-0.5 text-[9px] font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition-colors">Edit</Link>
                                  <button @click="deleteInvoice(invoice)" class="p-1 rounded text-red-400 hover:bg-red-50 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                    </template>

                  </template>
                </div>
              </div>
            </template>

            <!-- ── Tab content: Invoice Mandiri ── -->
            <template v-if="activeTab === recurringGroups.length">
              <div key="mandiri">
                <div v-if="standaloneInvoices.length === 0" class="px-5 py-10 text-center text-sm text-gray-300">
                  Tidak ada invoice mandiri.
                </div>
                <div v-for="invoice in standaloneInvoices" :key="invoice.id"
                  class="flex items-center gap-4 px-5 py-3.5 border-t border-gray-50 transition-colors group"
                  :class="isPastDue(invoice) ? 'bg-red-50/30 hover:bg-red-50/50' : 'hover:bg-gray-50/50'">
                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold shrink-0" :class="statusClass(invoice.status)">
                    {{ statusLabel(invoice.status) }}
                  </span>
                  <div class="flex-1 min-w-0">
                    <Link :href="route('invoices.show', invoice.id) + '?back=' + encodeURIComponent($page.url)"
                      class="font-mono text-sm font-semibold text-indigo-600 hover:text-indigo-800 hover:underline block truncate">
                      {{ invoice.invoice_number }}
                    </Link>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ invoice.project_category?.name ?? '—' }} &bull;
                      {{ fmtDateShort(invoice.issue_date) }} <span class="text-gray-300">→</span>
                      <span :class="isPastDue(invoice) ? 'text-red-500 font-medium' : ''">{{ fmtDateShort(invoice.due_date) }}</span>
                      <span v-if="isPastDue(invoice)" class="ml-1 text-red-500 font-semibold">· lewat {{ daysPastDue(invoice) }} hari</span>
                    </p>
                    <p class="text-[10px] text-gray-300 mt-0.5">Dibuat {{ fmtDateTime(invoice.created_at) }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-gray-800">{{ fmtCurrency(invoiceTotal(invoice)) }}</p>
                    <p v-if="invoice.tax_percentage" class="text-[10px] text-violet-500 mt-0.5">+PPN {{ invoice.tax_percentage }}%</p>
                  </div>
                  <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    <button @click.stop="toggleMenu($event, invoice)" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                    </button>
                    <button @click="deleteInvoice(invoice)" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </template>

          </div>
        </div>


      </template>
    </div>

    <!-- Three-dot Dropdown -->
    <Teleport to="body">
      <div v-if="activeMenu !== null && menuInvoice"
        class="fixed w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
        :style="{ top: menuPosition.top + 'px', right: menuPosition.right + 'px' }">
        <a :href="route('invoices.download', menuInvoice.id)"
          class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Download PDF
        </a>
        <button @click="openReceipt(menuInvoice); activeMenu = null"
          class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
          <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Receipt
        </button>
        <a :href="route('invoices.print-view', menuInvoice.id)" target="_blank"
          class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
          <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          Print Invoice
        </a>
      </div>
    </Teleport>

    <!-- Receipt Modal -->
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

    <!-- Modal Perbarui (resume frozen) -->
    <Teleport to="body">
      <div v-if="resumeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <h3 class="text-sm font-bold text-gray-900 mb-1">Lanjutkan Invoice</h3>
          <p class="text-xs text-gray-400 mb-5">Tentukan tanggal mulai dan durasi perpanjangan berikutnya.</p>
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
              <input type="date" v-model="resumeForm.issue_date" :min="resumeMinDate"
                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Perpanjangan ({{ resumeTarget?.invoice_type === 'yearly' ? 'tahun' : 'bulan' }})
              </label>
              <select v-model="resumeForm.interval_months"
                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <template v-if="resumeTarget?.invoice_type === 'yearly'">
                  <option v-for="n in 5" :key="n" :value="n * 12">{{ n }} tahun</option>
                </template>
                <template v-else>
                  <option v-for="n in 36" :key="n" :value="n">{{ n }} bulan</option>
                </template>
              </select>
            </div>
          </div>
          <div class="flex gap-2 mt-6">
            <button @click="resumeModal = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
              Batal
            </button>
            <button @click="submitResume"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
              Lanjutkan
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({ client: Object, invoices: Array });

// ── Tabs ────────────────────────────────────────────────────
const activeTab     = ref(0)
const tipsOpen      = ref(false)
const activeInvoice = (group) => group.invoices[0] ?? null

// ── Menu / Modal state ──────────────────────────────────────
const receiptUrl  = ref(null);
const activeMenu  = ref(null);
const menuInvoice = ref(null);
const menuPosition = ref({ top: 0, right: 0 });

function toggleMenu(event, invoice) {
  if (activeMenu.value === invoice.id) { activeMenu.value = null; return; }
  const rect       = event.currentTarget.getBoundingClientRect();
  const menuHeight = 116;
  const spaceBelow = window.innerHeight - rect.bottom;
  const top = spaceBelow < menuHeight
    ? rect.top + window.scrollY - menuHeight - 4
    : rect.bottom + window.scrollY + 4;
  menuPosition.value = { top, right: window.innerWidth - rect.right };
  menuInvoice.value  = invoice;
  activeMenu.value   = invoice.id;
}
function closeMenu() { activeMenu.value = null; }
onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

function openReceipt(invoice) { receiptUrl.value = route('invoices.receipt', invoice.id); }

// ── Grouping logic ──────────────────────────────────────────
function getDescendants(parentId) {
  return props.invoices
    .filter(inv => inv.parent_invoice_id === parentId)
    .sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date))
    .flatMap(child => [child, ...getDescendants(child.id)]);
}

const groups = computed(() => {
  const roots = props.invoices
    .filter(inv => !inv.parent_invoice_id)
    .sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date));

  return roots.map(root => {
    const descendants = getDescendants(root.id);
    const all = [root, ...descendants].sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date));
    const isRecurring = descendants.length > 0 || !!root.interval_months;
    return { root, invoices: all, isRecurring };
  });
});

const recurringGroups    = computed(() => groups.value.filter(g => g.isRecurring));
const standaloneInvoices = computed(() => groups.value.filter(g => !g.isRecurring).map(g => g.root));

const recurringTabLabels = computed(() => {
  const count = {}
  for (const g of recurringGroups.value) {
    const name = g.root.project_category?.name ?? '—'
    count[name] = (count[name] ?? 0) + 1
  }
  const seen = {}
  return recurringGroups.value.map(g => {
    const name = g.root.project_category?.name ?? '—'
    if (count[name] > 1) {
      seen[name] = (seen[name] ?? 0) + 1
      return `${name} (${seen[name]})`
    }
    return name
  })
})

// ── Financial helpers ───────────────────────────────────────
function invoiceTotal(inv) {
  // subtotal = sum of item.amount - item.discount per item
  const itemsSum = parseFloat(inv.items_sum_amount) || 0
  const itemsDiscountSum = parseFloat(inv.items_sum_discount) || 0
  const subtotal = Math.max(0, itemsSum - itemsDiscountSum)

  // invoice-level discount
  let afterDiscount = subtotal
  if (inv.discount_value) {
    const disc = inv.discount_type === 'percent'
      ? subtotal * (inv.discount_value / 100)
      : parseFloat(inv.discount_value)
    afterDiscount = Math.max(0, subtotal - disc)
  }

  // DPP + tax
  const dppBase = inv.is_dpp ? afterDiscount * (11 / 12) : afterDiscount
  const tax = inv.tax_percentage ? dppBase * (inv.tax_percentage / 100) : 0

  return afterDiscount + tax
}
function chainTotal(group)     { return group.invoices.filter(i => i.status !== 'frozen').reduce((s, inv) => s + invoiceTotal(inv), 0); }
function chainPaidTotal(group) { return group.invoices.filter(i => i.status === 'paid').reduce((s, inv) => s + invoiceTotal(inv), 0); }

const summary = computed(() => {
  const paid    = props.invoices.filter(i => i.status === 'paid');
  const pending = props.invoices.filter(i => !['paid','frozen','carried'].includes(i.status));
  return {
    paid:         paid.reduce((s, i) => s + invoiceTotal(i), 0),
    paidCount:    paid.length,
    pending:      pending.reduce((s, i) => s + invoiceTotal(i), 0),
    pendingCount: pending.length,
  };
});

// ── Date / currency helpers ─────────────────────────────────
const fmtDate = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
  : '-';

const fmtDateShort = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: '2-digit' })
  : '-';

const fmtDateTime = d => d
  ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
  : '-';

const fmtCurrency = v => v != null
  ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
  : '-';

const isPastDue    = inv => !['paid','frozen','carried'].includes(inv.status) && new Date(inv.due_date) < new Date(new Date().toDateString());
const daysPastDue  = inv => Math.floor((new Date(new Date().toDateString()) - new Date(inv.due_date)) / 86400000);
const hasOverdue   = group => group.invoices.some(isPastDue);

// ── Status display ──────────────────────────────────────────
const statusLabel = s => ({ draft: 'Draft', sent: 'Sent', paid: 'Dibayarkan', unpaid: 'Unpaid', frozen: 'Frozen', carried: 'Carried' }[s] ?? s);
const statusClass = s => ({
  draft:   'bg-gray-100 text-gray-500',
  sent:    'bg-blue-50 text-blue-600 ring-1 ring-blue-200',
  paid:    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
  unpaid:  'bg-red-50 text-red-600 ring-1 ring-red-200',
  frozen:  'bg-sky-100 text-sky-600 ring-1 ring-sky-200',
  carried: 'bg-orange-50 text-orange-600 ring-1 ring-orange-200',
}[s] ?? 'bg-gray-100 text-gray-500');

const dotClass = inv => {
  if (inv.status === 'frozen')  return 'bg-sky-400';
  if (inv.status === 'carried') return 'bg-orange-400';
  if (isPastDue(inv))           return 'bg-red-500';
  return { draft: 'bg-gray-300', sent: 'bg-blue-400', paid: 'bg-emerald-500', unpaid: 'bg-red-400' }[inv.status] ?? 'bg-gray-300';
};

// ── Chain card styling ──────────────────────────────────────
function chainBorderClass(group) {
  if (hasOverdue(group))              return 'border-red-200';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'border-emerald-200';
  return 'border-gray-100';
}
function chainHeaderClass(group) {
  if (hasOverdue(group))              return 'bg-red-50/60';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'bg-emerald-50/60';
  return 'bg-gray-50/60';
}
function chainIconClass(group) {
  if (hasOverdue(group))              return 'bg-red-100 text-red-500';
  const last = group.invoices.at(-1);
  if (last?.status === 'paid')        return 'bg-emerald-100 text-emerald-600';
  return 'bg-indigo-100 text-indigo-500';
}

// ── Month gap between two consecutive invoices ──────────────
function monthGap(newer, older) {
  // Use newer invoice's interval_months if set (inherited from when it was generated)
  if (newer.interval_months) return newer.interval_months
  // Fallback: compute from issue_date diff
  const d1 = new Date(newer.issue_date)
  const d2 = new Date(older.issue_date)
  return Math.max(1, Math.round(((d1.getFullYear() - d2.getFullYear()) * 12 + (d1.getMonth() - d2.getMonth()))))
}

// ── Actions ─────────────────────────────────────────────────
function hasChild(invoice) {
  return props.invoices.some(inv => inv.parent_invoice_id === invoice.id)
}

function isSmallSubCode(invoice) {
  return /^[CRPF]-/.test(invoice.invoice_number)
}

function getFullSizeInvoices(groupInvoices) {
  return groupInvoices.filter(inv => !isSmallSubCode(inv))
}

function getSubListInvoices(groupInvoices) {
  return groupInvoices.filter(inv => isSmallSubCode(inv))
}

function markPaid(invoice) {
  router.patch(route('invoices.status', invoice.id), { status: 'paid' }, { preserveScroll: true })
}

function freezeInvoice(invoice) {
  router.post(route('invoices.freeze', invoice.id), {}, { preserveScroll: true })
}

function carryInvoice(invoice) {
  router.post(route('invoices.carry', invoice.id), {}, { preserveScroll: true })
}

function reactivateInvoice(invoice) {
  Swal.fire({
    title: 'Reaktivasi invoice ini?',
    html: `<div style="text-align:left;font-size:0.85rem;color:#374151">
      <p>Invoice <strong>${invoice.invoice_number}</strong> akan direaktivasi.</p>
      <p style="margin-top:0.5rem">Sistem akan membuat invoice tunggakan untuk setiap bulan yang terlewat hingga bulan depan, dengan harga yang sama.</p>
      <p style="margin-top:0.5rem;color:#059669;font-weight:600">Bayar sekaligus di invoice paling atas (bulan depan).</p>
    </div>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#059669',
    confirmButtonText: 'Ya, Reaktivasi',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.post(route('invoices.reactivate', invoice.id), {}, { preserveScroll: true })
  })
}

function isReaktivasiPair(upper, lower) {
  if (!upper || !lower) return false
  // upper adalah yang lebih baru (tampil di atas), lower lebih lama
  // chain: lower.parent = upper, both is_reaktivasi = true
  return upper.is_reaktivasi && lower.is_reaktivasi && upper.parent_invoice_id === lower.id
}

const resumeModal   = ref(false)
const resumeMinDate = ref('')
const resumeForm    = reactive({ issue_date: '', interval_months: 1 })
const resumeTarget  = ref(null)


function openResume(invoice) {
  resumeTarget.value            = invoice
  resumeMinDate.value           = invoice.issue_date ? invoice.issue_date.substring(0, 10) : ''
  resumeForm.issue_date         = ''
  resumeForm.interval_months    = invoice.interval_months ?? 1
  resumeModal.value             = true
}

function submitResume() {
  if (!resumeForm.issue_date || !resumeTarget.value) return
  router.post(route('invoices.resume', resumeTarget.value.id), {
    issue_date:      resumeForm.issue_date,
    interval_months: resumeForm.interval_months,
  }, {
    preserveScroll: true,
    onSuccess: () => { resumeModal.value = false },
  })
}

function prepayInvoice(invoice) {
  router.post(route('invoices.prepay', invoice.id), {}, { preserveScroll: true })
}

function deleteInvoice(invoice) {
  Swal.fire({
    title: 'Hapus invoice ini?',
    text: invoice.invoice_number,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('invoices.destroy', invoice.id));
  });
}
</script>
