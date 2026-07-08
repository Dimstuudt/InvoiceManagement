<template>
  <AppLayout :title="'SPK · ' + props.client.company_name">
    <div class="max-w-5xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('spk.index')"
          class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div class="flex-1 min-w-0">
          <h2 class="text-lg font-semibold text-gray-900 truncate">{{ props.client.company_name }}</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ props.client.spks.length }} SPK terdaftar</p>
        </div>
        <button @click="openModal"
          class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah SPK
        </button>
      </div>

      <!-- SPK Table -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="!props.client.spks.length" class="p-10 text-center">
          <p class="text-sm text-gray-400">Belum ada SPK untuk klien ini.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs min-w-[700px]">
            <thead>
              <tr class="bg-gray-50/60 border-b border-gray-100">
                <th class="px-4 py-3 text-center font-medium text-gray-400 w-10">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-400">No SPK</th>
                <th class="px-4 py-3 text-left font-medium text-gray-400 w-28">Tgl SPK</th>
                <th class="px-4 py-3 text-left font-medium text-gray-400">Layanan</th>
                <th class="px-4 py-3 text-center font-medium text-gray-400 w-32">Dokumen SPK</th>
                <th class="px-4 py-3 text-left font-medium text-gray-400 w-32">User Create</th>
                <th class="px-4 py-3 text-left font-medium text-gray-400 w-32">User Update</th>
                <th class="px-4 py-3 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="(spk, idx) in props.client.spks" :key="spk.id" class="hover:bg-gray-50/40">
                <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ idx + 1 }}</td>
                <td class="px-4 py-3">
                  <a v-if="headInvoiceId(spk)"
                    :href="route('invoices.client', { client: props.client.id, highlight: headInvoiceId(spk) })"
                    class="font-mono text-indigo-600 hover:text-indigo-800 hover:underline underline-offset-2 transition-colors">
                    {{ spk.spk_number ?? '—' }}
                  </a>
                  <span v-else class="font-mono text-gray-700">{{ spk.spk_number ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500">{{ formatDate(spk.start_date) }}</td>
                <td class="px-4 py-3 text-gray-700 max-w-[220px] truncate" :title="spk.service_name">
                  {{ spk.service_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  <a v-if="spk.file_path"
                    :href="'/storage/' + spk.file_path"
                    target="_blank"
                    class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 underline underline-offset-2">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Lihat PDF
                  </a>
                  <span v-else class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-gray-500">{{ spk.user?.name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ spk.user?.name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <button @click="confirmDelete(spk)"
                    class="p-1 rounded text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Hapus SPK">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Tambah SPK -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="closeModal"/>

        <div class="relative ml-auto w-full max-w-4xl bg-white shadow-2xl flex flex-col h-full overflow-hidden">

          <!-- Panel Header -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Tambah SPK</h3>
              <p class="text-xs text-gray-400 mt-0.5 font-medium">{{ props.client.company_name }}</p>
            </div>
            <button @click="closeModal" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Panel Body -->
          <div class="flex flex-1 overflow-hidden">

            <!-- Left: Upload + Preview -->
            <div class="w-2/5 border-r border-gray-100 flex flex-col">
              <div class="p-4 border-b border-gray-50">
                <div
                  class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-200"
                  :class="dragging
                    ? 'border-indigo-400 bg-indigo-50'
                    : parsing
                      ? 'border-amber-300 bg-amber-50/50 cursor-wait'
                      : parsed
                        ? 'border-emerald-300 bg-emerald-50/50'
                        : form.file
                          ? 'border-indigo-200 bg-indigo-50/30'
                          : 'border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30'"
                  @dragover.prevent="dragging = true"
                  @dragleave.prevent="dragging = false"
                  @drop.prevent="onDrop"
                  @click="!parsing && $refs.fileInput.click()">
                  <input ref="fileInput" type="file" accept=".pdf" class="hidden" @change="onFileChange"/>

                  <!-- Idle: belum ada file -->
                  <template v-if="!form.file && !parsing">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="text-xs font-medium text-gray-500">Upload PDF SPK</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">drag & drop atau klik · maks 10MB</p>
                  </template>

                  <!-- Loading: sedang dianalisis AI -->
                  <template v-else-if="parsing">
                    <svg class="w-7 h-7 text-amber-400 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <p class="text-xs font-semibold text-amber-600">AI sedang membaca SPK...</p>
                    <p class="text-[10px] text-amber-400 mt-0.5">mencocokkan data dengan sistem</p>
                  </template>

                  <!-- Done: berhasil -->
                  <template v-else-if="parsed">
                    <svg class="w-6 h-6 text-emerald-500 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-emerald-700 truncate px-2">{{ form.file?.name }}</p>
                    <p class="text-[10px] text-emerald-500 mt-0.5">✓ Data berhasil diekstrak</p>
                    <button type="button" @click.stop="clearFile"
                      class="mt-1.5 text-[10px] text-gray-400 hover:text-red-500 underline">Ganti file</button>
                  </template>

                  <!-- File dipilih, belum diproses -->
                  <template v-else>
                    <svg class="w-6 h-6 text-indigo-400 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="text-xs font-semibold text-indigo-600 truncate px-2">{{ form.file?.name }}</p>
                    <button type="button" @click.stop="clearFile"
                      class="mt-1 text-[10px] text-gray-400 hover:text-red-500 underline">Ganti</button>
                  </template>
                </div>
              </div>

              <!-- PDF Preview -->
              <div class="flex-1 overflow-hidden bg-gray-50">
                <iframe v-if="pdfUrl" :src="pdfUrl" class="w-full h-full border-0"></iframe>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-xs text-gray-300">Preview PDF muncul setelah file dipilih</p>
                </div>
              </div>
            </div>

            <!-- Right: Form -->
            <div class="w-3/5 overflow-y-auto">
              <div class="p-5 space-y-5">

                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi SPK</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">No. SPK</label>
                      <input v-model="form.spk_number" type="text"
                        placeholder="Otomatis dari dokumen SPK"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono"/>
                      <p class="text-[10px] text-gray-400 mt-1">Diisi otomatis saat upload PDF · bisa diedit manual</p>
                    </div>
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Nama Layanan <span class="text-red-400">*</span></label>
                      <input v-model="form.service_name" type="text" placeholder="Jasa pengembangan sistem..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Kontrak (Rp)</label>
                      <input v-model.number="form.contract_value" type="number"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono"/>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Mulai Kontrak</label>
                      <input v-model="form.start_date" type="date"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Selesai Kontrak</label>
                      <input v-model="form.end_date" type="date"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                      <textarea v-model="form.notes" rows="2"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"/>
                    </div>
                  </div>
                </div>

                <div class="border-t border-gray-50"/>

                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Pengaturan Invoice</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Kategori Project <span class="text-red-400">*</span></label>
                      <select v-model="form.project_category_id"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— pilih —</option>
                        <option v-for="pc in props.projectCategories" :key="pc.id" :value="pc.id">
                          {{ pc.name }} ({{ pc.code }})
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Penerbit Dokumen <span class="text-red-400">*</span></label>
                      <select v-model="form.document_issuer_id"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— pilih —</option>
                        <option v-for="di in props.documentIssuers" :key="di.id" :value="di.id">{{ di.name }}</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Rekening Bank</label>
                      <select v-model="form.bank_account_id"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— pilih —</option>
                        <option v-for="ba in props.bankAccounts" :key="ba.id" :value="ba.id">
                          {{ ba.bank_name }} – {{ ba.name }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Invoice</label>
                      <div class="flex gap-2">
                        <button type="button" @click="form.invoice_type = 'monthly'"
                          class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 transition-colors"
                          :class="form.invoice_type === 'monthly' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500'">
                          Bulanan
                        </button>
                        <button type="button" @click="form.invoice_type = 'yearly'"
                          class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 transition-colors"
                          :class="form.invoice_type === 'yearly' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-500'">
                          Tahunan
                        </button>
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Interval Tagihan</label>
                      <select v-model.number="form.interval_months"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option :value="null">— tidak berulang —</option>
                        <template v-if="form.invoice_type === 'yearly'">
                          <option :value="12">1 tahun</option>
                          <option :value="24">2 tahun</option>
                          <option :value="36">3 tahun</option>
                        </template>
                        <template v-else>
                          <option v-for="n in 12" :key="n" :value="n">{{ n }} bulan</option>
                        </template>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Invoice <span class="text-red-400">*</span></label>
                      <input v-model="form.issue_date" type="date" @change="autoFillDueDate"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">
                        Jatuh Tempo
                        <span class="font-normal text-gray-400">· +14 hari</span>
                      </label>
                      <input v-model="form.due_date" type="date"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Panel Footer -->
          <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/40 flex items-center justify-between flex-shrink-0">
            <p class="text-xs text-gray-400">Invoice akan dibuat otomatis & dikaitkan ke SPK ini.</p>
            <div class="flex gap-3">
              <button type="button" @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                Batal
              </button>
              <button type="button" @click="submitStore"
                :disabled="saving || !form.project_category_id || !form.document_issuer_id || !form.issue_date || !form.service_name"
                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ saving ? 'Menyimpan...' : 'Simpan SPK & Buat Invoice' }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, onUnmounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  client:            Object,
  projectCategories: Array,
  clientCategories:  Array,
  documentIssuers:   Array,
  bankAccounts:      Array,
})

const page     = usePage()
const modalOpen = ref(false)
const dragging  = ref(false)
const parsing   = ref(false)
const saving    = ref(false)
const parsed    = ref(false)
const pdfUrl    = ref(null)

// Form — deklarasi sebelum computed/watch
const defaultForm = () => ({
  file:                null,
  spk_number:          '',
  service_name:        '',
  contract_value:      null,
  start_date:          '',
  end_date:            '',
  duration_months:     null,
  notes:               '',
  project_category_id: props.projectCategories?.[0]?.id ?? '',
  document_issuer_id:  props.documentIssuers?.[0]?.id  ?? '',
  bank_account_id:     props.bankAccounts?.[0]?.id     ?? '',
  invoice_type:        'monthly',
  interval_months:     null,
  issue_date:          '',
  due_date:            '',
})

const form = ref(defaultForm())


watch(() => form.value.file, (newFile) => {
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
  pdfUrl.value = newFile ? URL.createObjectURL(newFile) : null
  if (newFile) submitParse()
})

onUnmounted(() => { if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value) })

const skipIntervalReset = ref(false)
watch(() => form.value.invoice_type, () => {
  if (skipIntervalReset.value) return
  form.value.interval_months = null
})

watch(() => page.props.flash?.error, (msg) => {
  if (!msg || !parsing.value) return
  parsing.value = false

  const isApiKey  = msg.includes('GROQ_API_KEY')
  const isScan    = msg.includes('scan') || msg.includes('gambar') || msg.includes('teks')
  const isTimeout = msg.includes('Koneksi') || msg.includes('gagal')

  Swal.fire({
    icon: 'warning',
    title: isApiKey  ? 'API Key Belum Dikonfigurasi'
         : isScan    ? 'PDF Tidak Bisa Dibaca'
         : isTimeout ? 'Koneksi ke Claude Gagal'
         : 'Ekstraksi Gagal',
    html: isApiKey
      ? 'Tambahkan <code class="font-mono text-xs bg-gray-100 px-1 rounded">GROQ_API_KEY</code> di file <code class="font-mono text-xs bg-gray-100 px-1 rounded">.env</code> untuk menggunakan fitur ini.'
      : isScan
      ? 'PDF sepertinya hasil scan atau gambar — teks tidak bisa dibaca otomatis.<br><span class="text-sm text-gray-500">Isi form secara manual dengan melihat preview di kiri.</span>'
      : `<span class="text-sm">${msg}</span>`,
    confirmButtonText: 'Oke, isi manual',
    confirmButtonColor: '#6366f1',
    showCancelButton: !isApiKey,
    cancelButtonText: 'Coba lagi',
    cancelButtonColor: '#e5e7eb',
  }).then(r => {
    if (!r.isConfirmed && !isApiKey) {
      // Coba lagi — re-trigger parse dengan file yang sama
      if (form.value.file) submitParse()
    }
  })
})

watch(() => page.props.flash?.spk, (val) => {
  if (!val) return
  form.value.spk_number      = val.spk_number      ?? ''
  form.value.service_name    = val.service_name    ?? ''
  form.value.contract_value  = val.contract_value  ?? null
  form.value.start_date      = val.start_date      ?? ''
  form.value.end_date        = val.end_date        ?? ''
  form.value.duration_months = val.duration_months ?? null
  form.value.notes           = val.notes           ?? ''

  if (val.start_date) {
    form.value.issue_date = val.start_date
    form.value.due_date   = addDays(val.start_date, 14)
  }

  // Field yang Claude cocokkan dari database
  if (val.project_category_id) form.value.project_category_id = val.project_category_id
  if (val.document_issuer_id)  form.value.document_issuer_id  = val.document_issuer_id
  if (val.bank_account_id)     form.value.bank_account_id     = val.bank_account_id

  skipIntervalReset.value = true
  if (val.invoice_type) {
    form.value.invoice_type    = val.invoice_type
    form.value.interval_months = val.interval_months ?? null
  } else if (val.duration_months && val.duration_months >= 12) {
    form.value.invoice_type    = 'yearly'
    form.value.interval_months = val.duration_months <= 36 ? val.duration_months : 12
  } else {
    form.value.invoice_type    = 'monthly'
    form.value.interval_months = val.duration_months ?? null
  }
  skipIntervalReset.value = false

  parsed.value = true
}, { immediate: true })

function addDays(dateStr, days) {
  const d = new Date(dateStr)
  d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}

function autoFillDueDate() {
  if (form.value.issue_date) form.value.due_date = addDays(form.value.issue_date, 14)
}

function headInvoiceId(spk) {
  if (!spk.invoices?.length) return null
  const head = spk.invoices.find(inv => !inv.parent_invoice_id)
  return (head ?? spk.invoices[spk.invoices.length - 1]).id
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function openModal() {
  modalOpen.value = true
  form.value      = defaultForm()
  parsed.value    = false
  if (pdfUrl.value) { URL.revokeObjectURL(pdfUrl.value); pdfUrl.value = null }
}

function closeModal() {
  modalOpen.value = false
}

function onFileChange(e) { form.value.file = e.target.files[0] ?? null }
function onDrop(e) {
  dragging.value = false
  const f = e.dataTransfer.files[0]
  if (f?.type === 'application/pdf') form.value.file = f
}
function clearFile() { form.value.file = null; parsed.value = false }

function submitParse() {
  if (!form.value.file || parsing.value) return
  parsing.value = true
  parsed.value  = false
  const fd = new FormData()
  fd.append('file', form.value.file)
  router.post(route('spk.parse'), fd, {
    forceFormData: true,
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { parsing.value = false },
  })
}

function submitStore() {
  if (saving.value) return
  saving.value = true

  const fd = new FormData()
  fd.append('client_id',           props.client.id)
  fd.append('spk_number',          form.value.spk_number          ?? '')
  fd.append('service_name',        form.value.service_name        ?? '')
  fd.append('contract_value',      form.value.contract_value      ?? '')
  fd.append('start_date',          form.value.start_date          ?? '')
  fd.append('end_date',            form.value.end_date            ?? '')
  fd.append('duration_months',     form.value.duration_months     ?? '')
  fd.append('notes',               form.value.notes               ?? '')
  fd.append('project_category_id', form.value.project_category_id ?? '')
  fd.append('document_issuer_id',  form.value.document_issuer_id  ?? '')
  fd.append('bank_account_id',     form.value.bank_account_id     ?? '')
  fd.append('invoice_type',        form.value.invoice_type)
  fd.append('interval_months',     form.value.interval_months     ?? '')
  fd.append('issue_date',          form.value.issue_date          ?? '')
  fd.append('due_date',            form.value.due_date            ?? '')
  if (form.value.file) fd.append('file', form.value.file)

  router.post(route('spk.store'), fd, {
    forceFormData: true,
    onFinish: () => { saving.value = false },
  })
}

function confirmDelete(spk) {
  const invoiceCount = spk.invoices?.length ?? 0
  Swal.fire({
    title: 'Hapus SPK ini?',
    html: invoiceCount
      ? `<span class="font-mono text-sm">${spk.spk_number}</span><br><span class="text-sm text-orange-500">${invoiceCount} invoice terkait juga akan ikut dihapus.</span>`
      : `<span class="font-mono text-sm">${spk.spk_number}</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  }).then(r => {
    if (r.isConfirmed) router.delete(route('spk.destroy', spk.id), { preserveScroll: true })
  })
}
</script>
