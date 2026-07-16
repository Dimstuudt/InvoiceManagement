<template>
  <AppLayout title="Import SPK + Klien Baru">
    <div class="max-w-5xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('spk.index')"
          class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </Link>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-lg font-semibold text-gray-900">Import SPK + Klien Baru</h2>
            <span v-if="props.provider === 'ollama'"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-200">
              <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
              </svg>
              AI · Ollama (Lokal)
            </span>
            <span v-else
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
              <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
              </svg>
              AI · Groq
            </span>
          </div>
          <p class="text-sm text-gray-400 mt-0.5">
            <template v-if="props.provider === 'ollama'">Upload PDF SPK — AI lokal mengisi data (data tidak keluar server)</template>
            <template v-else>Upload PDF SPK — AI mengisi data klien & kontrak otomatis</template>
          </p>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col lg:flex-row overflow-hidden" style="min-height: 600px;">

        <!-- Left: Upload + PDF Preview -->
        <div class="w-full lg:w-2/5 border-b lg:border-b-0 lg:border-r border-gray-100 flex flex-col">
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

              <template v-if="!form.file && !parsing">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <p class="text-xs font-medium text-gray-500">Upload PDF SPK</p>
                <p class="text-[10px] text-gray-400 mt-0.5">drag & drop atau klik · maks 10MB</p>
              </template>

              <template v-else-if="parsing">
                <svg class="w-7 h-7 text-amber-400 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <p class="text-xs font-semibold text-amber-600">
                  {{ props.provider === 'ollama' ? 'Ollama sedang membaca SPK...' : 'AI sedang membaca SPK...' }}
                </p>
                <p class="text-[10px] text-amber-400 mt-0.5">
                  {{ props.provider === 'ollama' ? 'model lokal — bisa lebih lama' : 'mengekstrak data klien & kontrak' }}
                </p>
              </template>

              <template v-else-if="parsed">
                <svg class="w-6 h-6 text-emerald-500 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs font-semibold text-emerald-700 truncate px-2">{{ form.file?.name }}</p>
                <p class="text-[10px] text-emerald-500 mt-0.5">✓ Data berhasil diekstrak {{ props.provider === 'ollama' ? 'Ollama' : 'AI' }}</p>
                <button type="button" @click.stop="clearFile"
                  class="mt-1.5 text-[10px] text-gray-400 hover:text-red-500 underline">Ganti file</button>
              </template>

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
          <div class="flex-1 overflow-hidden bg-gray-50 relative group">
            <iframe v-if="pdfUrl" :src="pdfUrl" class="w-full h-full border-0"/>
            <div v-else class="flex items-center justify-center h-full">
              <p class="text-xs text-gray-300">Preview PDF muncul setelah file dipilih</p>
            </div>
            <button v-if="pdfUrl" @click="pdfModal = true"
              class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm rounded-xl px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-lg flex items-center gap-1.5 hover:bg-white hover:text-indigo-700 transition-colors opacity-0 group-hover:opacity-100">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
              </svg>
              Perbesar
            </button>
          </div>
        </div>

        <!-- Right: Form -->
        <div class="w-3/5 flex flex-col">
          <div class="flex-1 overflow-y-auto p-5 space-y-4">

            <!-- Section 1: Klien -->
            <div>
              <div class="flex items-center gap-2 mb-3">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-bold flex-shrink-0">1</span>
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Data Klien Baru</p>
              </div>
              <div class="grid sm:grid-cols-2 gap-3">
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nama Perusahaan <span class="text-red-400">*</span></label>
                  <input v-model="form.company_name" type="text" placeholder="PT. Contoh Indonesia"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                  <p class="text-[10px] text-gray-400 mt-1">Jika sudah ada di sistem, data klien tidak akan ditimpa.</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Kota</label>
                  <input v-model="form.city" type="text" placeholder="Jakarta"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Kategori Klien <span class="text-red-400">*</span></label>
                  <select v-model="form.client_category_id"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— pilih —</option>
                    <option v-for="cc in props.clientCategories" :key="cc.id" :value="cc.id">{{ cc.name }}</option>
                  </select>
                </div>
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nama PIC</label>
                  <input v-model="form.pic_name" type="text" placeholder="Budi Santoso"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                  <div class="flex gap-2">
                    <button type="button" @click="form.client_status = 'active_client'"
                      class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 transition-colors"
                      :class="form.client_status === 'active_client' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500'">
                      Klien Aktif
                    </button>
                    <button type="button" @click="form.client_status = 'prospect'"
                      class="flex-1 py-1.5 text-xs font-semibold rounded-lg border-2 transition-colors"
                      :class="form.client_status === 'prospect' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-500'">
                      Prospek
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Divider 2 -->
            <div class="flex items-center gap-2 -mx-5 px-5 py-2 bg-gray-50/60 border-y border-gray-100">
              <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold flex-shrink-0">2</span>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Detail SPK</p>
            </div>

            <div>
              <div class="grid sm:grid-cols-2 gap-3">
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">No. SPK</label>
                  <input v-model="form.spk_number" type="text" placeholder="Otomatis dari dokumen SPK"
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

            <!-- Divider 3 -->
            <div class="flex items-center gap-2 -mx-5 px-5 py-2 bg-gray-50/60 border-y border-gray-100">
              <span class="flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-600 text-[10px] font-bold flex-shrink-0">3</span>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Pengaturan Invoice</p>
            </div>

            <div>
              <div class="grid sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Produk / Layanan <span class="text-red-400">*</span></label>
                  <input v-model="form.service_name" type="text" placeholder="Lisensi Aplikasi, Hosting..."
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Harga Kontrak (Rp)</label>
                  <input v-model.number="form.contract_value" type="number" placeholder="30000000"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono"/>
                </div>
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
                    Jatuh Tempo <span class="font-normal text-gray-400">· +14 hari</span>
                  </label>
                  <input v-model="form.due_date" type="date"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
              </div>
            </div>

          </div>

          <!-- Footer -->
          <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/40 flex items-center justify-between flex-shrink-0">
            <p class="text-xs text-gray-400">Klien baru akan dibuat otomatis jika belum ada di sistem.</p>
            <button type="button" @click="submitStore"
              :disabled="saving || !form.company_name || !form.client_category_id || !form.service_name || !form.project_category_id || !form.document_issuer_id || !form.issue_date"
              class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
              <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ saving ? 'Menyimpan...' : 'Buat Klien + SPK + Invoice' }}
            </button>
          </div>
        </div>
      </div>

    </div>

  <!-- Modal PDF -->
  <Teleport to="body">
    <Transition name="pdf-modal">
      <div v-if="pdfModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="pdfModal = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="pdfModal = false"/>
        <div class="relative z-10 w-full max-w-5xl" style="height: 90vh;">
          <iframe :src="pdfUrl" class="w-full h-full rounded-xl border-0 shadow-2xl bg-white"/>
          <button @click="pdfModal = false"
            class="absolute -top-3 -right-3 w-8 h-8 bg-white rounded-full shadow-lg flex items-center justify-center text-gray-500 hover:text-red-500 hover:bg-red-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, watch, onUnmounted } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  provider:          { type: String, default: 'groq' },
  projectCategories: Array,
  clientCategories:  Array,
  documentIssuers:   Array,
  bankAccounts:      Array,
})

const page     = usePage()
const dragging = ref(false)
const parsing  = ref(false)
const saving   = ref(false)
const parsed   = ref(false)
const pdfUrl   = ref(null)
const pdfModal = ref(false)

const defaultForm = () => ({
  file:                null,
  company_name:        '',
  city:                '',
  pic_name:            '',
  client_category_id:  '',
  client_status:       'active_client',
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

  const isOllama  = props.provider === 'ollama'
  const isApiKey  = msg.includes('GROQ_API_KEY') || msg.includes('OLLAMA_HOST')
  const isScan    = msg.includes('scan') || msg.includes('gambar') || msg.includes('teks')
  const isTimeout = msg.includes('Koneksi') || msg.includes('gagal') || msg.includes('timeout')

  Swal.fire({
    icon: 'warning',
    title: isApiKey  ? (isOllama ? 'Ollama Belum Dikonfigurasi' : 'API Key Belum Dikonfigurasi')
         : isScan    ? 'PDF Tidak Bisa Dibaca'
         : isTimeout ? (isOllama ? 'Koneksi ke Ollama Gagal' : 'Koneksi ke AI Gagal')
         : 'Ekstraksi Gagal',
    html: isApiKey
      ? (isOllama
          ? 'Pastikan <code class="font-mono text-xs bg-gray-100 px-1 rounded">OLLAMA_HOST</code> sudah dikonfigurasi di <code class="font-mono text-xs bg-gray-100 px-1 rounded">.env</code> dan Ollama sudah berjalan.'
          : 'Tambahkan <code class="font-mono text-xs bg-gray-100 px-1 rounded">GROQ_API_KEY</code> di file <code class="font-mono text-xs bg-gray-100 px-1 rounded">.env</code> untuk menggunakan fitur ini.')
      : isScan
      ? 'PDF sepertinya hasil scan atau gambar — teks tidak bisa dibaca otomatis.<br><span class="text-sm text-gray-500">Isi form secara manual dengan melihat preview di kiri.</span>'
      : `<span class="text-sm">${msg}</span>`,
    confirmButtonText: 'Oke, isi manual',
    confirmButtonColor: '#6366f1',
    showCancelButton: !isApiKey,
    cancelButtonText: 'Coba lagi',
    cancelButtonColor: '#e5e7eb',
  }).then(r => {
    if (!r.isConfirmed && !isApiKey && form.value.file) submitParse()
  })
})

watch(() => page.props.flash?.spk, (val) => {
  if (!val) return

  if (val.company_name)       form.value.company_name       = val.company_name
  if (val.city)               form.value.city               = val.city
  if (val.pic_name)           form.value.pic_name           = val.pic_name
  if (val.client_category_id) form.value.client_category_id = val.client_category_id
  if (val.client_status)      form.value.client_status      = val.client_status

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

function onFileChange(e) { form.value.file = e.target.files[0] ?? null }
function onDrop(e) {
  dragging.value = false
  const f = e.dataTransfer.files[0]
  if (f?.type === 'application/pdf') form.value.file = f
}
function clearFile() {
  form.value = defaultForm()
  parsed.value = false
}

function submitParse() {
  if (!form.value.file || parsing.value) return
  parsing.value = true
  parsed.value  = false
  const fd = new FormData()
  fd.append('file', form.value.file)
  fd.append('with_client', '1')
  fd.append('provider', props.provider)
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
  fd.append('company_name',        form.value.company_name        ?? '')
  fd.append('city',                form.value.city                ?? '')
  fd.append('pic_name',            form.value.pic_name            ?? '')
  fd.append('client_category_id',  form.value.client_category_id  ?? '')
  fd.append('client_status',       form.value.client_status)
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

  router.post(route('spk.store-with-client'), fd, {
    forceFormData: true,
    onFinish: () => { saving.value = false },
  })
}
</script>

<style scoped>
.pdf-modal-enter-active,
.pdf-modal-leave-active { transition: opacity 0.2s ease; }
.pdf-modal-enter-from,
.pdf-modal-leave-to   { opacity: 0; }
</style>
