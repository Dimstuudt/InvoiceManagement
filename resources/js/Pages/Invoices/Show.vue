<template>
  <AppLayout :title="invoice.invoice_number">
    <div class="-mx-6 -mb-6 min-h-screen py-8 px-6" style="background:#c8cccf">
      <div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-2xl overflow-hidden">

        <!-- ── ACTION BAR ──────────────────────────────────────────── -->
        <div class="flex items-center gap-2 px-4 py-2.5 bg-white border-b border-gray-100">

          <!-- Kembali (kiri, icon only) -->
          <Link :href="backHref" title="Kembali"
            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>

          <div class="w-px h-5 bg-gray-200 shrink-0"/>

          <!-- Invoice info -->
          <div class="flex items-center gap-2 min-w-0 flex-1">
            <p class="font-mono text-sm font-semibold text-gray-800 truncate">{{ invoice.invoice_number }}</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium shrink-0"
              :class="{
                'bg-gray-100 text-gray-500':                   invoice.document_status === 'draft',
                'bg-blue-50 text-blue-600 ring-1 ring-blue-200': invoice.document_status === 'verified' && invoice.payment_status === 'unpaid',
                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': invoice.payment_status === 'paid',
                'bg-sky-100 text-sky-600 ring-1 ring-sky-200': invoice.document_status === 'frozen',
                'bg-orange-50 text-orange-600 ring-1 ring-orange-200': invoice.document_status === 'carried',
              }">
              {{ invoice.payment_status === 'paid' ? 'Paid' : invoice.document_status === 'frozen' ? 'Frozen' : invoice.document_status === 'carried' ? 'Carried' : invoice.document_status === 'verified' ? (invoice.send_status === 'unsent' ? 'Verified' : 'Sent') : 'Draft' }}
            </span>
            <template v-if="invoice.payment_status !== 'paid' && !['frozen','carried'].includes(invoice.document_status)">
              <select :value="localInterval" @change="saveInterval($event.target.value)"
                class="text-xs border border-indigo-200 bg-indigo-50 text-indigo-600 rounded-md px-1.5 py-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer shrink-0">
                <option value="">↻ —</option>
                <template v-if="invoice.invoice_type === 'yearly'">
                  <option v-for="n in [1,2,3,4]" :key="n" :value="n * 12">↻ {{ n }} thn</option>
                </template>
                <template v-else>
                  <option v-for="n in [1,2,3,4,6]" :key="n" :value="n">↻ {{ n }} bln</option>
                </template>
              </select>
            </template>
            <span v-else-if="invoice.interval_months"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-500 shrink-0">
              ↻ {{ invoice.invoice_type === 'yearly' ? (invoice.interval_months / 12) + ' thn' : invoice.interval_months + ' bln' }}
            </span>

            <!-- Parent / child nav -->
            <template v-if="invoice.parent || invoice.children?.length">
              <div class="w-px h-5 bg-gray-200 shrink-0"/>
              <Link v-if="invoice.parent" :href="route('invoices.show', invoice.parent.id)"
                :title="`Warisan dari: ${invoice.parent.invoice_number}`"
                class="p-1 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
              </Link>
              <Link v-if="invoice.children?.length" :href="route('invoices.show', invoice.children[0].id)"
                :title="`Perpanjangan: ${invoice.children[0].invoice_number}`"
                class="p-1 rounded-lg bg-indigo-50 text-indigo-500 hover:bg-indigo-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
              </Link>
            </template>
          </div>

          <!-- Aksi utama (kanan) -->
          <div class="flex items-center gap-1.5 shrink-0">

            <!-- Payment status select -->
            <select v-if="!['frozen','carried'].includes(invoice.document_status)"
              :value="invoice.payment_status" @change="changePaymentStatus($event.target.value)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
              <option value="unpaid">Belum Bayar</option>
              <option value="paid">Lunas</option>
            </select>
            <!-- Document status select -->
            <select v-if="invoice.payment_status !== 'paid' && !['frozen','carried'].includes(invoice.document_status)"
              :value="invoice.document_status" @change="changeDocumentStatus($event.target.value)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
              <option value="draft">Draft</option>
              <option value="verified">Terverifikasi</option>
            </select>

            <!-- Perbarui (frozen) -->
            <button v-if="invoice.document_status === 'frozen' && !invoice.children?.length"
              @click="resumeModal = true"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
              </svg>
              Perbarui
            </button>

            <!-- Edit -->
            <Link v-if="invoice.payment_status !== 'paid' && !['frozen','carried'].includes(invoice.document_status) && invoice.document_status !== 'verified'"
              :href="route('invoices.edit', invoice.id)"
              class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
              </svg>
              Edit
            </Link>

            <!-- Kirim Email -->
            <button v-if="invoice.payment_status !== 'paid' && invoice.document_status !== 'carried' && invoice.document_status === 'verified'" @click="emailModal = true"
              class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
              Kirim Email
            </button>

            <!-- Dropdown ··· (Freeze, Tandai, Hapus) -->
            <div class="relative" ref="moreMenuRef">
              <button @click="moreMenuOpen = !moreMenuOpen"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
                </svg>
              </button>
              <div v-if="moreMenuOpen"
                class="absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                <!-- Carry (utang ke periode berikutnya) -->
                <button v-if="invoice.payment_status !== 'paid' && !['frozen','carried'].includes(invoice.document_status) && invoice.interval_months && !invoice.children?.length && !isChainMember"
                  @click="carryInvoice(); moreMenuOpen = false"
                  class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-orange-600 hover:bg-orange-50 transition-colors text-left">
                  <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15M5 5l-3 7 3 7"/>
                  </svg>
                  Carry (Utang)
                </button>
                <!-- Freeze -->
                <button v-if="invoice.payment_status !== 'paid' && !['frozen','carried'].includes(invoice.document_status) && invoice.parent_invoice_id && !isChainMember"
                  @click="freezeInvoice(); moreMenuOpen = false"
                  class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-sky-600 hover:bg-sky-50 transition-colors text-left">
                  <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M5.636 5.636l12.728 12.728M18.364 5.636L5.636 18.364"/>
                  </svg>
                  Freeze
                </button>
                <!-- Tandai -->
                <button @click="toggleMark(); moreMenuOpen = false"
                  class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                  <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  {{ invoice.document_status === 'verified' ? 'Batal Antre' : 'Antre Kirim' }}
                </button>
                <div class="my-1 border-t border-gray-100"/>
                <!-- Hapus -->
                <button @click="destroy(); moreMenuOpen = false"
                  class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors text-left">
                  <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Hapus
                </button>
              </div>
            </div>

          </div>
        </div>

        <!-- NOTIF DITANDAI -->
        <div v-if="invoice.document_status === 'verified'"
          class="bg-amber-50 border-b border-amber-200 px-6 py-2.5 flex items-center gap-2.5">
          <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
          </svg>
          <p class="text-xs text-amber-700 font-medium">
            Invoice ini sedang dalam antrean kirim otomatis. Batalkan antrean terlebih dahulu jika ingin mengedit.
          </p>
          <button @click="toggleMark" class="ml-auto text-xs text-amber-600 hover:text-amber-800 underline font-medium shrink-0">
            Batal Antre
          </button>
        </div>

        <!-- NOTIF CHAIN MEMBER -->
        <div v-if="isChainMember"
          class="bg-slate-50 border-b border-slate-200 px-6 py-2.5 flex items-center gap-2.5">
          <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          <p class="text-xs text-slate-600 font-medium">
            Invoice ini adalah bagian dari chain — aksi seperti carry dan freeze dikelola dari invoice utama.
          </p>
          <Link v-if="chainHeadId"
            :href="route('invoices.show', chainHeadId)"
            class="ml-auto text-xs text-slate-500 hover:text-slate-700 underline font-medium shrink-0">
            Lihat invoice utama
          </Link>
        </div>

        <!-- NOTIF CARRIED -->
        <div v-if="invoice.document_status === 'carried'"
          class="bg-orange-50 border-b border-orange-200 px-6 py-2.5 flex items-center gap-2.5">
          <svg class="w-4 h-4 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/>
          </svg>
          <p class="text-xs text-orange-700 font-medium">
            Invoice ini di-carry — tunggakan akan masuk ke invoice berikutnya. Lunas otomatis saat invoice berikutnya dibayar.
          </p>
        </div>

        <!-- NOTIF FROZEN -->
        <div v-if="invoice.document_status === 'frozen'"
          class="bg-sky-50 border-b border-sky-200 px-6 py-2.5 flex items-center gap-2.5">
          <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M5.636 5.636l12.728 12.728M18.364 5.636L5.636 18.364"/>
          </svg>
          <p class="text-xs text-sky-700 font-medium">
            <span v-if="invoice.children?.length">Invoice ini sudah dilanjutkan — lihat invoice berikutnya di atas.</span>
            <span v-else>Invoice ini sedang dibekukan. Klik <strong>Perbarui</strong> untuk melanjutkan dengan tanggal baru.</span>
          </p>
        </div>

        <!-- ── POPUP HEADER ────────────────────────────────────────── -->
        <div class="flex items-center justify-between px-8 py-3.5 border-b border-gray-100 bg-gray-50/80">
          <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Invoice Detail:</span>
            <span class="font-mono text-sm font-bold text-gray-900 truncate">{{ invoice.invoice_number }}</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold shrink-0"
              :class="{
                'bg-sky-50 text-sky-600 ring-1 ring-sky-200': invoice.document_status === 'frozen',
                'bg-orange-50 text-orange-600 ring-1 ring-orange-200': invoice.document_status === 'carried',
                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': invoice.payment_status === 'paid',
                'bg-gray-100 text-gray-500': invoice.document_status === 'draft' && invoice.payment_status !== 'paid',
                'bg-blue-50 text-blue-600 ring-1 ring-blue-200': invoice.document_status === 'verified' && invoice.payment_status !== 'paid',
              }">
              {{
                invoice.document_status === 'frozen' ? 'Dibekukan' :
                invoice.document_status === 'carried' ? 'Carry' :
                invoice.payment_status === 'paid' ? 'Lunas' :
                invoice.document_status === 'draft' ? 'Draft' :
                ({ unsent: 'Terverifikasi', send1: 'Terkirim 1×', send2: 'Terkirim 2×', send3: 'Terkirim 3×', send4: 'Terkirim 4×', send5: 'Terkirim 5×' })[invoice.send_status] ?? 'Aktif'
              }}
            </span>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <a :href="route('invoices.download', invoice.id)"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download
            </a>
            <button @click="openPrint"
              class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
              </svg>
              Print
            </button>
          </div>
        </div>

        <!-- KOP SURAT -->
        <div v-if="invoice.document_issuer?.header_image_url">
          <img :src="invoice.document_issuer.header_image_url"
            class="w-full object-contain" style="max-height:140px;display:block" alt="Kop Surat"/>
        </div>
        <div v-else-if="invoice.document_issuer"
          class="px-8 py-5 bg-gradient-to-r from-blue-700 to-blue-600">
          <p class="text-xl font-black text-white uppercase tracking-widest">{{ invoice.document_issuer.name }}</p>
        </div>

        <!-- ── INFO ──────────────────────────────────────────────── -->
        <div class="px-8 py-6 border-b border-gray-100">
          <div class="grid grid-cols-3 gap-x-8 gap-y-5">

            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">SPK Number</p>
              <template v-if="invoice.spk_id">
                <p class="text-sm font-mono font-medium text-gray-900">{{ invoice.spk_number || '—' }}</p>
                <p class="text-[10px] text-indigo-400 mt-0.5">· terkunci · terhubung ke kontrak SPK</p>
              </template>
              <template v-else>
                <input v-if="invoice.payment_status !== 'paid'"
                  v-model="localSpkNumber" @blur="saveMeta"
                  placeholder="—"
                  class="text-sm text-gray-900 w-full border-b border-transparent hover:border-gray-300 focus:border-indigo-400 focus:outline-none bg-transparent transition-colors py-0.5"/>
                <p v-else class="text-sm font-medium text-gray-900">{{ localSpkNumber || '—' }}</p>
              </template>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Invoice Number</p>
              <p class="text-sm font-mono font-bold text-gray-900">{{ invoice.invoice_number }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Project / Category</p>
              <p class="text-sm font-medium text-gray-900">{{ invoice.project_category?.name || '—' }}</p>
            </div>

            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Issue Date</p>
              <p class="text-sm font-medium text-gray-900">{{ fmt(invoice.issue_date) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Due Date</p>
              <p class="text-sm font-medium" :class="isOverdue ? 'text-red-600' : 'text-gray-900'">
                {{ fmt(invoice.due_date) }}
                <span v-if="isOverdue" class="ml-1 text-xs font-normal">(lewat {{ overdueDays }} hari)</span>
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Recipient</p>
              <p class="text-sm font-medium text-gray-900">{{ invoice.client?.company_name || '—' }}</p>
            </div>

            <div class="col-span-2">
              <p class="text-xs text-gray-400 font-medium mb-1">Address</p>
              <p class="text-sm text-gray-900">
                {{ invoice.client?.address || '—' }}<span v-if="invoice.client?.city">, {{ invoice.client.city }}</span>
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Attention</p>
              <input v-if="invoice.payment_status !== 'paid'"
                v-model="localAttention" @blur="saveMeta"
                placeholder="—"
                class="text-sm text-gray-900 w-full border-b border-transparent hover:border-gray-300 focus:border-indigo-400 focus:outline-none bg-transparent transition-colors py-0.5"/>
              <p v-else class="text-sm text-gray-900">{{ localAttention || '—' }}</p>
            </div>

            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Issuer</p>
              <p class="text-sm font-medium text-gray-900">{{ invoice.document_issuer?.name || '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Bank Account</p>
              <p class="text-sm font-medium text-gray-900">
                {{ invoice.bank_account?.bank_name || '—' }}
                <span v-if="invoice.bank_account?.account_number" class="text-gray-400 font-mono text-xs font-normal">
                  · {{ invoice.bank_account.account_number }}
                </span>
              </p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Signature</p>
              <p class="text-sm font-medium text-gray-900">
                {{ invoice.signature?.name || '—' }}
                <span v-if="invoice.signature?.position" class="text-gray-400 font-normal text-xs">
                  ({{ invoice.signature.position }})
                </span>
              </p>
            </div>

          </div>
        </div>

        <!-- ── INVOICE ITEMS ──────────────────────────────────────── -->
        <div class="border-b border-gray-100">
          <div class="px-8 py-3 bg-gray-50 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Items</p>
          </div>
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-center text-xs font-semibold text-gray-400 px-4 py-3 w-12">No.</th>
                <th class="text-left text-xs font-semibold text-gray-400 px-4 py-3">Description</th>
                <th class="text-right text-xs font-semibold text-gray-400 px-4 py-3 w-44">Amount</th>
                <th class="text-right text-xs font-semibold text-gray-400 px-4 py-3 w-44">Discount</th>
                <th class="text-right text-xs font-semibold text-gray-400 px-4 py-3 w-44">Total</th>
                <th v-if="invoice.payment_status !== 'paid'" class="w-12 px-4 py-3"/>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in items" :key="i"
                class="border-b border-gray-100 hover:bg-gray-50/60 group transition-colors">
                <td class="px-4 py-3 text-sm text-gray-400 text-center">{{ i + 1 }}</td>
                <td class="px-4 py-3">
                  <input v-if="invoice.payment_status !== 'paid'" v-model="item.description" type="text"
                    placeholder="Deskripsi item..."
                    class="w-full text-sm text-gray-900 bg-transparent border-b border-transparent hover:border-gray-200 focus:border-indigo-400 focus:outline-none py-0.5 transition-colors placeholder:text-gray-300"/>
                  <span v-else class="text-sm text-gray-900">{{ item.description }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-1.5">
                    <span class="text-xs text-gray-400 shrink-0">Rp</span>
                    <input v-if="invoice.payment_status !== 'paid'" v-model.number="item.amount" type="number" min="0"
                      class="w-full text-sm text-right font-mono text-gray-900 bg-transparent border-b border-transparent hover:border-gray-200 focus:border-indigo-400 focus:outline-none py-0.5 transition-colors"/>
                    <span v-else class="text-sm font-mono text-gray-900">{{ formatNumber(item.amount) }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-right">
                  <template v-if="invoice.payment_status !== 'paid'">
                    <div v-if="item.discount !== null" class="flex items-center justify-end gap-1">
                      <span class="text-xs text-gray-400 shrink-0">Rp</span>
                      <input v-model.number="item.discount" type="number" min="0"
                        class="w-24 text-sm text-right font-mono text-gray-900 bg-transparent border-b border-transparent hover:border-gray-200 focus:border-indigo-400 focus:outline-none py-0.5 transition-colors"/>
                      <button @click="item.discount = null"
                        class="text-gray-300 hover:text-red-400 transition-colors text-lg leading-none shrink-0">×</button>
                    </div>
                    <button v-else @click="item.discount = 0"
                      class="text-xs text-gray-400 hover:text-indigo-500 transition-colors whitespace-nowrap">
                      + Diskon
                    </button>
                  </template>
                  <span v-else class="text-sm font-mono text-red-500">
                    {{ item.discount ? '- Rp ' + formatNumber(item.discount) : '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm font-mono font-semibold text-gray-900">{{ formatCurrency(itemTotal(item)) }}</span>
                </td>
                <td v-if="invoice.payment_status !== 'paid'" class="px-3 py-3 text-center">
                  <button @click="removeItem(i)" :disabled="items.length === 1"
                    class="opacity-0 group-hover:opacity-100 disabled:!opacity-0 text-gray-300 hover:text-red-400 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <button v-if="invoice.payment_status !== 'paid'" @click="addItem"
            class="px-5 py-3 text-xs text-indigo-500 hover:text-indigo-700 flex items-center gap-1.5 transition-colors w-full hover:bg-indigo-50/30">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Item
          </button>
        </div>

        <!-- ── INVOICE TOTALS ─────────────────────────────────────── -->
        <div class="border-b border-gray-100">
          <div class="px-8 py-3 bg-gray-50 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Invoice Totals</p>
          </div>
          <div class="px-8 py-5 space-y-3">

            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Sub Total (Items)</span>
              <span class="text-sm font-mono font-semibold text-gray-900">{{ formatCurrency(subtotal) }}</span>
            </div>

            <div class="flex items-center gap-3 py-0.5">
              <input type="checkbox" id="discountCheck" v-model="localDiscount.enabled"
                :disabled="invoice.payment_status === 'paid'"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer shrink-0 disabled:cursor-not-allowed"/>
              <label for="discountCheck" class="text-sm text-gray-600 cursor-pointer select-none w-36 shrink-0">Invoice Discount</label>
              <template v-if="localDiscount.enabled">
                <select v-model="localDiscount.type" :disabled="invoice.payment_status === 'paid'"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer shrink-0 disabled:opacity-60">
                  <option value="percent">%</option>
                  <option value="amount">Rp</option>
                </select>
                <input v-model.number="localDiscount.value" type="number" min="0"
                  :disabled="invoice.payment_status === 'paid'"
                  placeholder="0"
                  class="w-32 text-sm text-right font-mono border border-gray-200 rounded-lg px-3 py-1.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 disabled:opacity-60"/>
                <span v-if="localDiscount.type === 'percent'" class="text-xs text-gray-400 shrink-0">%</span>
                <span class="ml-auto text-sm font-mono font-semibold text-red-500 whitespace-nowrap">
                  - {{ formatCurrency(discountAmount) }}
                </span>
              </template>
              <span v-else class="ml-auto text-sm text-gray-400">—</span>
            </div>

            <div class="flex items-center gap-3 py-0.5">
              <input type="checkbox" id="dppCheck" v-model="localIsDpp"
                :disabled="invoice.payment_status === 'paid'"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer shrink-0 disabled:cursor-not-allowed"/>
              <label for="dppCheck" class="text-sm text-gray-600 cursor-pointer select-none shrink-0">DPP (11/12)</label>
              <span v-if="localIsDpp" class="text-xs text-gray-400 ml-1">
                Base: <span class="font-mono">{{ formatCurrency(dppBase) }}</span>
              </span>
              <span class="ml-auto text-sm text-gray-400">{{ localIsDpp ? '' : '—' }}</span>
            </div>

            <div class="flex items-center gap-3 py-0.5">
              <input type="checkbox" id="vatCheck" v-model="localTaxEnabled"
                :disabled="invoice.payment_status === 'paid'"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer shrink-0 disabled:cursor-not-allowed"/>
              <label for="vatCheck" class="text-sm text-gray-600 cursor-pointer select-none w-36 shrink-0">VAT (PPN)</label>
              <template v-if="localTaxEnabled">
                <input v-model.number="localTaxPct" type="number" min="0" max="100" step="0.01"
                  :disabled="invoice.payment_status === 'paid'"
                  class="w-20 text-sm text-center font-mono border border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 disabled:opacity-60"/>
                <span class="text-sm text-gray-500 shrink-0">%</span>
                <span class="ml-auto text-sm font-mono font-semibold text-gray-900 whitespace-nowrap">{{ formatCurrency(taxAmount) }}</span>
              </template>
              <span v-else class="ml-auto text-sm text-gray-400">—</span>
            </div>

            <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
              <span class="text-base font-bold text-gray-900">{{ invoice.carried_from ? 'Total Invoice' : 'Grand Total' }}</span>
              <span class="text-2xl font-black font-mono text-blue-700">{{ formatCurrency(grandTotal) }}</span>
            </div>

            <!-- Tunggakan dari carried invoice -->
            <template v-if="invoice.carried_from">
              <div class="flex items-center justify-between pt-2">
                <div>
                  <span class="text-sm text-gray-600">Tunggakan</span>
                  <span class="ml-2 text-xs font-mono text-orange-600 font-medium">{{ invoice.carried_from.invoice_number }}</span>
                </div>
                <span class="text-sm font-mono font-semibold text-orange-600">+ {{ formatCurrency(invoice.carried_total) }}</span>
              </div>
              <div class="border-t-2 border-orange-200 pt-3 mt-2 flex items-center justify-between">
                <span class="text-base font-bold text-gray-900">Total Bayar</span>
                <span class="text-2xl font-black font-mono text-orange-600">{{ formatCurrency(grandTotal + (invoice.carried_total ?? 0)) }}</span>
              </div>
            </template>
          </div>
        </div>

        <!-- ── NOTES ─────────────────────────────────────────────── -->
        <div class="border-b border-gray-100">
          <div class="px-8 py-3 bg-gray-50 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</p>
          </div>
          <div class="px-8 py-5">
            <textarea v-if="invoice.payment_status !== 'paid'"
              v-model="localNotes" @blur="saveMeta"
              placeholder="Enter additional notes (optional)"
              rows="3"
              class="w-full text-sm text-gray-700 border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white resize-none placeholder:text-gray-300 transition-colors"/>
            <p v-else class="text-sm text-gray-700 leading-relaxed">{{ localNotes || '—' }}</p>
          </div>
        </div>

        <!-- ── FOOTER ─────────────────────────────────────────────── -->
        <div class="px-8 py-4 bg-gray-50 flex items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <a :href="route('invoices.download', invoice.id)"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl transition shadow-sm">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download
            </a>
            <button @click="openPrint"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl transition shadow-sm">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
              </svg>
              Print
            </button>
          </div>

          <button v-if="invoice.payment_status !== 'paid' && invoice.document_status !== 'carried' && invoice.document_status !== 'verified'"
            @click="save()" :disabled="saving"
            class="flex items-center gap-1.5 px-5 py-2 text-sm font-semibold bg-blue-700 hover:bg-blue-800 text-white rounded-xl transition shadow-sm disabled:opacity-60">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>

      </div>
    </div>
  </AppLayout>

  <SendEmailModal
    :show="emailModal"
    :invoice="invoice"
    :client-emails="invoice.client_emails ?? []"
    :email-templates="emailTemplates ?? []"
    @close="emailModal = false"
  />

  <!-- Modal Perbarui (resume dari frozen) -->
  <Teleport to="body">
    <div v-if="resumeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h3 class="text-sm font-bold text-gray-900 mb-1">Lanjutkan Invoice</h3>
        <p class="text-xs text-gray-400 mb-5">Tentukan tanggal mulai dan durasi perpanjangan.</p>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai</label>
            <input type="date" v-model="resumeForm.issue_date"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Perpanjangan ({{ invoice.invoice_type === 'yearly' ? 'tahun' : 'bulan' }})
            </label>
            <select v-model="resumeForm.interval_months"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
              <template v-if="invoice.invoice_type === 'yearly'">
                <option v-for="n in [1,2,3,4]" :key="n" :value="n * 12">{{ n }} tahun</option>
              </template>
              <template v-else>
                <option v-for="n in [1,2,3,4,6]" :key="n" :value="n">{{ n }} bulan</option>
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
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SendEmailModal from '@/Components/SendEmailModal.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({ invoice: Object, emailTemplates: Array })

const emailModal   = ref(false)
const resumeModal  = ref(false)
const moreMenuOpen = ref(false)
const moreMenuRef  = ref(null)

function closeMoreMenu(e) {
  if (moreMenuRef.value && !moreMenuRef.value.contains(e.target)) moreMenuOpen.value = false
}
onMounted(() => document.addEventListener('click', closeMoreMenu))
onUnmounted(() => document.removeEventListener('click', closeMoreMenu))
const resumeForm  = reactive({
  issue_date:      '',
  interval_months: props.invoice.interval_months ?? 1,
})

const backHref = computed(() => {
  const back = new URLSearchParams(window.location.search).get('back')
  return back ? decodeURIComponent(back) : route('invoices.index')
})

const items  = ref(props.invoice.items.map(i => ({ ...i, discount: i.discount ?? null })))
const saving = ref(false)

const localSpkNumber  = ref(props.invoice.spk_number ?? '')
const localAttention  = ref(props.invoice.attention ?? '')
const localNotes      = ref(props.invoice.notes ?? '')
const localInterval   = ref(props.invoice.interval_months ?? '')

const localDiscount = reactive({
  enabled: !!(props.invoice.discount_type),
  type:    props.invoice.discount_type || 'percent',
  value:   props.invoice.discount_value ?? 0,
})
const localIsDpp      = ref(props.invoice.is_dpp ?? false)
const localTaxEnabled = ref(props.invoice.tax_percentage !== null && props.invoice.tax_percentage !== undefined)
const localTaxPct     = ref(props.invoice.tax_percentage ?? 11)

// ── Computed ──────────────────────────────────────────────────────────────
function itemTotal(item) {
  return Math.max(0, (parseFloat(item.amount) || 0) - (parseFloat(item.discount) || 0))
}

const subtotal = computed(() => items.value.reduce((s, i) => s + itemTotal(i), 0))

const discountAmount = computed(() => {
  if (!localDiscount.enabled || !localDiscount.value) return 0
  if (localDiscount.type === 'percent') return subtotal.value * (localDiscount.value / 100)
  return parseFloat(localDiscount.value) || 0
})

const afterDiscount = computed(() => Math.max(0, subtotal.value - discountAmount.value))
const dppBase       = computed(() => localIsDpp.value ? afterDiscount.value * (11 / 12) : afterDiscount.value)
const taxAmount     = computed(() => localTaxEnabled.value && localTaxPct.value ? dppBase.value * (localTaxPct.value / 100) : 0)
const grandTotal    = computed(() => afterDiscount.value + taxAmount.value)

const isChainMember = computed(() => /^[CRPF]-/.test(props.invoice.invoice_number))
const chainHeadId   = computed(() =>
  props.invoice.carried_from_id
  ?? props.invoice.prepay_chain_id
  ?? props.invoice.reaktivasi_chain_id
  ?? null
)

const isOverdue = computed(() => {
  if (props.invoice.payment_status === 'paid') return false
  if (props.invoice.document_status === 'frozen' || props.invoice.document_status === 'carried') return false
  return new Date(props.invoice.due_date) < new Date(new Date().toDateString())
})
const overdueDays = computed(() => {
  const diff = new Date(new Date().toDateString()) - new Date(props.invoice.due_date)
  return Math.floor(diff / 86400000)
})

// ── Formatters ────────────────────────────────────────────────────────────
const fmt = d => d
  ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
  : '—'

const formatNumber   = v => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(v || 0)
const formatCurrency = v => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v || 0)

// ── Actions ───────────────────────────────────────────────────────────────
function saveInterval(val) {
  localInterval.value = val === '' ? '' : parseInt(val)
  router.patch(route('invoices.interval', props.invoice.id),
    { interval_months: val === '' ? null : parseInt(val) },
    { preserveScroll: true }
  )
}

function saveMeta() {
  router.patch(route('invoices.meta', props.invoice.id),
    { spk_number: localSpkNumber.value || null, attention: localAttention.value || null, notes: localNotes.value || null },
    { preserveScroll: true }
  )
}

function addItem()     { items.value.push({ description: '', amount: 0, discount: null }) }
function removeItem(i) { if (items.value.length > 1) items.value.splice(i, 1) }

function buildPayload() {
  return {
    items:          items.value.map(i => ({ description: i.description, amount: i.amount, discount: i.discount || null })),
    tax_percentage: localTaxEnabled.value ? localTaxPct.value : null,
    discount_type:  localDiscount.enabled ? localDiscount.type : null,
    discount_value: localDiscount.enabled ? localDiscount.value : null,
    is_dpp:         localIsDpp.value,
    spk_number:     localSpkNumber.value || null,
    attention:      localAttention.value || null,
    notes:          localNotes.value || null,
  }
}

function save(onSuccess) {
  saving.value = true
  router.patch(route('invoices.save', props.invoice.id), buildPayload(), {
    preserveScroll: true,
    onSuccess:  () => { if (onSuccess) onSuccess() },
    onFinish:   () => { saving.value = false },
  })
}

function openPrint() {
  if (props.invoice.payment_status === 'paid') {
    window.open(route('invoices.print-view', props.invoice.id), '_blank')
    return
  }
  save(() => window.open(route('invoices.print-view', props.invoice.id), '_blank'))
}

function changePaymentStatus(val) {
  router.patch(route('invoices.status', props.invoice.id), { payment_status: val }, { preserveScroll: true })
}

function changeDocumentStatus(val) {
  router.patch(route('invoices.status', props.invoice.id), { document_status: val }, { preserveScroll: true })
}

function freezeInvoice() {
  router.post(route('invoices.freeze', props.invoice.id), {}, { preserveScroll: true })
}

function carryInvoice() {
  router.post(route('invoices.carry', props.invoice.id), {}, { preserveScroll: true })
}

function submitResume() {
  if (!resumeForm.issue_date) return
  router.post(route('invoices.resume', props.invoice.id), {
    issue_date:      resumeForm.issue_date,
    interval_months: resumeForm.interval_months,
  }, {
    onSuccess: () => { resumeModal.value = false },
  })
}

function toggleMark() {
  router.patch(route('invoices.mark', props.invoice.id), {}, { preserveScroll: true })
}

function destroy() {
  Swal.fire({
    title: 'Hapus invoice ini?',
    text: props.invoice.invoice_number,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('invoices.destroy', props.invoice.id), {
      onSuccess: () => router.visit(route('invoices.index')),
    })
  })
}
</script>
