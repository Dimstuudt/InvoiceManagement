<template>
  <AppLayout title="SPK Parser">
    <div class="max-w-4xl mx-auto space-y-6">

      <!-- Header -->
      <div>
        <h2 class="text-lg font-semibold text-gray-900">SPK Parser</h2>
        <p class="text-sm text-gray-400 mt-0.5">Upload PDF Surat Perjanjian Kerja — AI akan mengekstrak data dan membantu membuat invoice</p>
      </div>

      <!-- Upload Zone -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form @submit.prevent="submitParse">
          <div
            class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
            :class="dragging ? 'border-indigo-400 bg-indigo-50' : file ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
            @click="$refs.fileInput.click()">
            <input ref="fileInput" type="file" accept=".pdf" class="hidden" @change="onFileChange"/>

            <template v-if="!file">
              <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              <p class="text-sm font-medium text-gray-500">Drag & drop PDF SPK di sini</p>
              <p class="text-xs text-gray-400 mt-1">atau klik untuk pilih file · maks 10MB</p>
            </template>
            <template v-else>
              <svg class="w-8 h-8 text-emerald-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm font-semibold text-emerald-700">{{ file.name }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ (file.size / 1024).toFixed(0) }} KB</p>
              <button type="button" @click.stop="file = null; result = null"
                class="mt-2 text-xs text-gray-400 hover:text-red-500 underline">
                Ganti file
              </button>
            </template>
          </div>

          <div class="mt-4 flex justify-end">
            <button type="submit" :disabled="!file || parsing"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
              <svg v-if="parsing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
              </svg>
              {{ parsing ? 'AI sedang membaca SPK...' : 'Ekstrak Data dari SPK' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Error -->
      <div v-if="$page.props.flash?.error"
        class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        {{ $page.props.flash.error }}
      </div>

      <!-- Hasil Ekstraksi -->
      <div v-if="result">

        <!-- Konfirmasi Client -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Data dari SPK</h3>
              <p class="text-xs text-gray-400 mt-0.5">Edit jika ada yang kurang tepat, lalu buat invoice</p>
            </div>
            <span class="text-[10px] font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-full ring-1 ring-emerald-200">
              ✓ AI berhasil mengekstrak data
            </span>
          </div>

          <div class="p-6 space-y-6">

            <!-- SECTION: Client -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">Informasi Klien</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nama Perusahaan / Klien <span class="text-red-400">*</span></label>
                  <input v-model="result.client_name" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Kota</label>
                  <input v-model="result.client_city" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nama PIC</label>
                  <input v-model="result.pic_name" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Alamat</label>
                  <input v-model="result.client_address" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Email PIC</label>
                  <input v-model="result.pic_email" type="email"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Telepon PIC</label>
                  <input v-model="result.pic_phone" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
              </div>
            </div>

            <div class="border-t border-gray-50"/>

            <!-- SECTION: Invoice -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">Detail Invoice</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nomor SPK</label>
                  <input v-model="result.spk_number" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nama Layanan / Deskripsi Item</label>
                  <input v-model="result.service_name" type="text"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Kontrak (Rp)</label>
                  <input v-model.number="result.contract_value" type="number"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono"/>
                  <p v-if="result.contract_value_text" class="text-[10px] text-gray-400 mt-1">{{ result.contract_value_text }}</p>
                </div>

                <!-- Tipe & Interval -->
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Invoice</label>
                  <div class="flex gap-2">
                    <button type="button" @click="form.invoice_type = 'monthly'"
                      class="flex-1 py-2 text-xs font-semibold rounded-lg border-2 transition-colors"
                      :class="form.invoice_type === 'monthly' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                      Bulanan
                    </button>
                    <button type="button" @click="form.invoice_type = 'yearly'"
                      class="flex-1 py-2 text-xs font-semibold rounded-lg border-2 transition-colors"
                      :class="form.invoice_type === 'yearly' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                      Tahunan
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Interval Tagihan</label>
                  <select v-model.number="form.interval_months"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
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

                <!-- Issue Date & Due Date -->
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Invoice (Issue Date)</label>
                  <input v-model="form.issue_date" type="date" @change="autoFillDueDate"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                  <p class="text-[10px] text-gray-400 mt-1">dari start_date SPK: {{ result.start_date ?? '-' }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">
                    Jatuh Tempo (Due Date)
                    <span class="text-gray-400 font-normal ml-1">· otomatis +14 hari</span>
                  </label>
                  <input v-model="form.due_date" type="date"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-gray-500 mb-1">Catatan / Klausul</label>
                  <textarea v-model="result.notes" rows="3"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"/>
                </div>
              </div>
            </div>

            <div class="border-t border-gray-50"/>

            <!-- SECTION: Master Data untuk Invoice -->
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">Pengaturan Invoice</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Kategori Project <span class="text-red-400">*</span></label>
                  <select v-model="form.project_category_id"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— pilih —</option>
                    <option v-for="pc in props.projectCategories" :key="pc.id" :value="pc.id">
                      {{ pc.name }} ({{ pc.code }})
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Penerbit Dokumen <span class="text-red-400">*</span></label>
                  <select v-model="form.document_issuer_id"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— pilih —</option>
                    <option v-for="di in props.documentIssuers" :key="di.id" :value="di.id">
                      {{ di.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Rekening Bank</label>
                  <select v-model="form.bank_account_id"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— pilih —</option>
                    <option v-for="ba in props.bankAccounts" :key="ba.id" :value="ba.id">
                      {{ ba.bank_name }} – {{ ba.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

          </div>

          <!-- Footer Actions -->
          <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/40 flex items-center justify-between">
            <p class="text-xs text-gray-400">Client baru akan dibuat otomatis jika belum ada di sistem.</p>
            <button @click="submitStore" :disabled="saving || !form.project_category_id || !form.document_issuer_id"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
              <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              {{ saving ? 'Menyimpan...' : 'Buat Client & Invoice dari SPK' }}
            </button>
          </div>
        </div>

      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  projectCategories: Array,
  documentIssuers:   Array,
  bankAccounts:      Array,
})

const page    = usePage()
const file    = ref(null)
const parsing = ref(false)
const saving  = ref(false)
const dragging = ref(false)
const result  = ref(null)

const form = ref({
  project_category_id: props.projectCategories?.[0]?.id ?? '',
  document_issuer_id:  props.documentIssuers?.[0]?.id  ?? '',
  bank_account_id:     props.bankAccounts?.[0]?.id     ?? '',
  invoice_type:        'monthly',
  interval_months:     null,
  issue_date:          '',
  due_date:            '',
})

watch(() => page.props.flash?.spk, (val) => {
  if (!val) return
  result.value = { ...val }
  // Sinkronisasi issue_date dari start_date SPK, lalu hitung due_date +14 hari
  if (val.start_date) {
    form.value.issue_date = val.start_date
    form.value.due_date   = addDays(val.start_date, 14)
  }
  // Tebak invoice_type dari durasi kontrak
  if (val.duration_months && val.duration_months >= 12) {
    form.value.invoice_type    = 'yearly'
    form.value.interval_months = val.duration_months <= 36 ? val.duration_months : 12
  } else {
    form.value.invoice_type    = 'monthly'
    form.value.interval_months = val.duration_months ?? null
  }
}, { immediate: true })

watch(() => form.value.invoice_type, () => {
  form.value.interval_months = null
})

function addDays(dateStr, days) {
  const d = new Date(dateStr)
  d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}

function autoFillDueDate() {
  if (form.value.issue_date) {
    form.value.due_date = addDays(form.value.issue_date, 14)
  }
}

function onFileChange(e) { file.value = e.target.files[0] ?? null }
function onDrop(e) {
  dragging.value = false
  const f = e.dataTransfer.files[0]
  if (f?.type === 'application/pdf') file.value = f
}

function submitParse() {
  if (!file.value || parsing.value) return
  parsing.value = true
  result.value  = null
  const fd = new FormData()
  fd.append('file', file.value)
  router.post(route('spk.parse'), fd, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => { parsing.value = false },
  })
}

function submitStore() {
  if (saving.value) return
  saving.value = true
  router.post(route('spk.store'), {
    // Data klien & SPK dari result (sudah diedit user)
    client_name:    result.value.client_name,
    client_address: result.value.client_address,
    client_city:    result.value.client_city,
    pic_name:       result.value.pic_name,
    pic_email:      result.value.pic_email,
    pic_phone:      result.value.pic_phone,
    service_name:   result.value.service_name,
    contract_value: result.value.contract_value,
    spk_number:     result.value.spk_number,
    notes:          result.value.notes,
    // Field invoice dari form
    issue_date:          form.value.issue_date,
    due_date:            form.value.due_date,
    invoice_type:        form.value.invoice_type,
    interval_months:     form.value.interval_months,
    project_category_id: form.value.project_category_id,
    document_issuer_id:  form.value.document_issuer_id,
    bank_account_id:     form.value.bank_account_id,
  }, {
    onFinish: () => { saving.value = false },
  })
}
</script>
