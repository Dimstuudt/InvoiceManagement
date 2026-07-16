<template>
  <AppLayout :title="'SPK · ' + props.client.company_name">
    <div class="max-w-5xl mx-auto space-y-5">

      <!-- Header -->
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <Link :href="route('spk.index')"
            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <div class="min-w-0">
            <h2 class="text-lg font-semibold text-gray-900 truncate">{{ props.client.company_name }}</h2>
            <p v-if="props.client.city" class="text-sm text-gray-400 mt-0.5">{{ props.client.city }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end shrink-0">
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"/>
            <span class="text-xs text-emerald-600 font-semibold">{{ assigned.length }} ada SPK</span>
          </div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"/>
            <span class="text-xs text-amber-600 font-semibold">{{ unassigned.length }} belum</span>
          </div>
          <button @click="openModal"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah SPK
          </button>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="!props.invoices.length"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <p class="text-sm text-gray-400">Belum ada invoice untuk klien ini.</p>
      </div>

      <template v-else>

        <!-- Belum Ada SPK -->
        <div v-if="unassigned.length" class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-amber-50/60 border-b border-amber-100 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"/>
            <span class="text-xs font-semibold text-amber-700">Belum Ada Nomor SPK</span>
            <span class="ml-auto text-[10px] text-amber-500">{{ unassigned.length }} invoice</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[760px]">
              <thead>
                <tr class="border-b border-gray-50">
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400">No Invoice</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-24">Tgl</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-24">Jatuh Tempo</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-22">Bayar</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-22">Dokumen</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400">Nomor SPK</th>
                  <th class="px-4 py-2.5 text-center font-medium text-gray-400 w-16">PDF</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="inv in unassigned" :key="inv.id" class="hover:bg-amber-50/20 transition-colors">
                  <InvoiceRow :inv="inv" :spk-files="props.spkFiles"
                    @edit="startEdit" @save="saveEdit" @upload="triggerUpload"/>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sudah Ada SPK -->
        <div v-if="assigned.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-emerald-50/40 border-b border-gray-100 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"/>
            <span class="text-xs font-semibold text-emerald-700">Sudah Ada Nomor SPK</span>
            <span class="ml-auto text-[10px] text-emerald-600">{{ assigned.length }} invoice</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[760px]">
              <thead>
                <tr class="border-b border-gray-50">
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400">No Invoice</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-24">Tgl</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-24">Jatuh Tempo</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-22">Bayar</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400 w-22">Dokumen</th>
                  <th class="px-4 py-2.5 text-left font-medium text-gray-400">Nomor SPK</th>
                  <th class="px-4 py-2.5 text-center font-medium text-gray-400 w-16">PDF</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="inv in assigned" :key="inv.id" class="hover:bg-gray-50/40 transition-colors">
                  <InvoiceRow :inv="inv" :spk-files="props.spkFiles"
                    @edit="startEdit" @save="saveEdit" @upload="triggerUpload"/>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </template>
    </div>

    <!-- Hidden file input for PDF upload -->
    <input ref="fileUploadRef" type="file" accept=".pdf" class="hidden" @change="onFileUploadChange"/>

    <!-- ───────────── Modal Tambah SPK ───────────── -->
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

          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Tambah SPK + Invoice Baru</h3>
              <p class="text-xs text-gray-400 mt-0.5 font-medium">{{ props.client.company_name }}</p>
            </div>
            <button @click="closeModal" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex flex-1 overflow-hidden">

            <!-- Left: Upload PDF -->
            <div class="w-2/5 border-r border-gray-100 flex flex-col">
              <div class="p-4 border-b border-gray-50">

                <!-- Parse method -->
                <div class="flex gap-1.5 mb-3">
                  <button type="button" @click="parseMethod = 'local'"
                    class="flex-1 py-1 text-[11px] font-semibold rounded-lg border-2 transition-colors"
                    :class="parseMethod === 'local' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-400 hover:border-gray-300'">
                    Lokal
                  </button>
                  <button type="button" @click="parseMethod = 'groq'"
                    class="flex-1 py-1 text-[11px] font-semibold rounded-lg border-2 transition-colors"
                    :class="parseMethod === 'groq' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-400 hover:border-gray-300'">
                    Groq
                  </button>
                  <button type="button" @click="parseMethod = 'ollama'"
                    class="flex-1 py-1 text-[11px] font-semibold rounded-lg border-2 transition-colors"
                    :class="parseMethod === 'ollama' ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-gray-200 text-gray-400 hover:border-gray-300'">
                    Ollama
                  </button>
                </div>
                <p class="text-[10px] text-gray-400 mb-3">
                  <template v-if="parseMethod === 'local'">Regex — cepat, data tidak keluar server</template>
                  <template v-else-if="parseMethod === 'groq'">AI cloud — lebih akurat, data terkirim ke Groq</template>
                  <template v-else>AI lokal — privat, data tidak keluar server</template>
                </p>

                <!-- Drop zone -->
                <div class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-200"
                  :class="dragging ? 'border-indigo-400 bg-indigo-50'
                    : parsing  ? 'border-amber-300 bg-amber-50/50 cursor-wait'
                    : parsed   ? 'border-emerald-300 bg-emerald-50/50'
                    : form.file ? 'border-indigo-200 bg-indigo-50/30'
                    : 'border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30'"
                  @dragover.prevent="dragging = true"
                  @dragleave.prevent="dragging = false"
                  @drop.prevent="onDrop"
                  @click="!parsing && $refs.pdfInput.click()">
                  <input ref="pdfInput" type="file" accept=".pdf" class="hidden" @change="onFileChange"/>

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
                      {{ parseMethod === 'local' ? 'Membaca SPK...' : parseMethod === 'ollama' ? 'Ollama sedang membaca...' : 'AI sedang membaca SPK...' }}
                    </p>
                  </template>

                  <template v-else-if="parsed">
                    <svg class="w-6 h-6 text-emerald-500 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-emerald-700 truncate px-2">{{ form.file?.name }}</p>
                    <p class="text-[10px] text-emerald-500 mt-0.5">✓ Data berhasil diekstrak</p>
                    <button type="button" @click.stop="clearFile" class="mt-1.5 text-[10px] text-gray-400 hover:text-red-500 underline">Ganti file</button>
                  </template>

                  <template v-else>
                    <svg class="w-6 h-6 text-indigo-400 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="text-xs font-semibold text-indigo-600 truncate px-2">{{ form.file?.name }}</p>
                    <button type="button" @click.stop="clearFile" class="mt-1 text-[10px] text-gray-400 hover:text-red-500 underline">Ganti</button>
                  </template>
                </div>
              </div>

              <!-- PDF Preview -->
              <div class="flex-1 overflow-hidden bg-gray-50">
                <iframe v-if="pdfUrl" :src="pdfUrl" class="w-full h-full border-0"/>
                <div v-else class="flex items-center justify-center h-full">
                  <p class="text-xs text-gray-300">Preview PDF muncul setelah file dipilih</p>
                </div>
              </div>
            </div>

            <!-- Right: Form -->
            <div class="w-3/5 overflow-y-auto">
              <div class="p-5 space-y-5">

                <!-- SPK Info -->
                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi SPK</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">No. SPK</label>
                      <input v-model="form.spk_number" type="text" placeholder="Otomatis dari dokumen SPK"
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
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Mulai</label>
                      <input v-model="form.start_date" type="date"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Selesai</label>
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

                <!-- Invoice Settings -->
                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Pengaturan Invoice</p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Kategori Project <span class="text-red-400">*</span></label>
                      <select v-model="form.project_category_id"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— pilih —</option>
                        <option v-for="pc in props.projectCategories" :key="pc.id" :value="pc.id">{{ pc.name }} ({{ pc.code }})</option>
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
                        <option v-for="ba in props.bankAccounts" :key="ba.id" :value="ba.id">{{ ba.bank_name }} – {{ ba.name }}</option>
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
                      <label class="block text-xs font-medium text-gray-500 mb-1">Jatuh Tempo <span class="font-normal text-gray-400">· +14 hari</span></label>
                      <input v-model="form.due_date" type="date"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/40 flex items-center justify-between shrink-0">
            <p class="text-xs text-gray-400">Invoice baru akan dibuat dengan nomor SPK yang ter-parse.</p>
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
import { ref, computed, watch, onUnmounted, defineComponent, h } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  client:            Object,
  invoices:          Array,
  spkFiles:          Object,
  projectCategories: Array,
  documentIssuers:   Array,
  bankAccounts:      Array,
})

const page = usePage()

// ── Invoice sections ──
const unassigned = computed(() => props.invoices.filter(inv => !inv.spk_number))
const assigned   = computed(() => props.invoices.filter(inv =>  inv.spk_number))

// ── Helpers ──
function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
function paymentLabel(s) {
  return { unpaid: 'Belum Lunas', paid: 'Lunas', partial: 'Sebagian' }[s] ?? s
}
function paymentClass(s) {
  return { paid: 'bg-emerald-50 text-emerald-600', unpaid: 'bg-red-50 text-red-500', partial: 'bg-amber-50 text-amber-600' }[s] ?? 'bg-gray-100 text-gray-400'
}
function docClass(s) {
  return { draft: 'bg-gray-100 text-gray-400', verified: 'bg-sky-50 text-sky-600', frozen: 'bg-indigo-50 text-indigo-500', carried: 'bg-amber-50 text-amber-600', inactive: 'bg-gray-100 text-gray-300' }[s] ?? 'bg-gray-100 text-gray-400'
}

// ── Inline SPK edit ──
const editingId = ref(null)
const editVal   = ref('')

function startEdit(inv) {
  editingId.value = inv.id
  editVal.value   = inv.spk_number ?? ''
}
function saveEdit(inv) {
  if (editingId.value !== inv.id) return
  const val = editVal.value.trim()
  if (val === (inv.spk_number ?? '')) { editingId.value = null; return }
  router.patch(route('spk.update-number', inv.id), { spk_number: val }, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null },
  })
}

// ── PDF upload for existing invoices ──
const fileUploadRef    = ref(null)
const uploadTargetId   = ref(null)

function triggerUpload(invId) {
  uploadTargetId.value = invId
  fileUploadRef.value.click()
}
function onFileUploadChange(e) {
  const file = e.target.files[0]
  if (!file || !uploadTargetId.value) return
  const fd = new FormData()
  fd.append('file', file)
  router.post(route('spk.upload-file', uploadTargetId.value), fd, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => { e.target.value = ''; uploadTargetId.value = null },
  })
}

// ── Invoice row component ──
const InvoiceRow = defineComponent({
  props: { inv: Object, spkFiles: Object },
  emits: ['upload'],
  setup(p, { emit }) {
    function getFile() {
      return p.spkFiles?.[p.inv.id] ?? null
    }

    const pdfSvgPath = 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'
    const uploadSvgPath = 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'

    return () => {
      const file   = getFile()
      const latest = p.inv.latest ?? p.inv  // latest = invoice terbaru di chain (berdasarkan spk_number)

      return [
        // Invoice number (parent) + link spotlight ke invoice terbaru
        h('td', { class: 'px-4 py-3' }, [
          h('div', { class: 'flex items-center gap-2' }, [
            h('a', {
              href: route('invoices.client', { client: props.client.id, highlight: latest.id }),
              class: 'font-mono font-semibold text-indigo-600 hover:text-indigo-800 hover:underline underline-offset-2 transition-colors',
            }, p.inv.invoice_number),
            p.inv.renewal_count > 0
              ? h('span', { class: 'text-[9px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full' }, `+${p.inv.renewal_count} renewal`)
              : null,
          ]),
        ]),
        // Tanggal dari invoice terbaru
        h('td', { class: 'px-4 py-3 text-gray-500 font-mono text-[11px]' }, fmtDate(latest.issue_date)),
        h('td', { class: 'px-4 py-3 text-gray-500 font-mono text-[11px]' }, fmtDate(latest.due_date)),
        // Payment badge — dari invoice terbaru (anak pertama kalau ada, kalau tidak dari parent)
        h('td', { class: 'px-4 py-3' }, [
          h('span', { class: `inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full ${paymentClass(latest.payment_status)}` },
            paymentLabel(latest.payment_status)),
        ]),
        // Doc badge — dari invoice terbaru
        h('td', { class: 'px-4 py-3' }, [
          h('span', { class: `inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full ${docClass(latest.document_status)}` },
            latest.document_status),
        ]),
        // SPK number (inline edit)
        h('td', { class: 'px-4 py-3' }, [
          h(SpkCell, { inv: p.inv }),
        ]),
        // PDF column — selalu tampil Upload, atau PDF link kalau sudah ada
        h('td', { class: 'px-4 py-3 text-center' }, [
          file
            ? h('div', { class: 'flex flex-col items-center gap-1' }, [
                h('a', {
                  href: '/storage/' + file.file_path,
                  target: '_blank',
                  class: 'inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-600 hover:text-indigo-800 underline underline-offset-2',
                }, [
                  h('svg', { class: 'w-3 h-3', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
                    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: pdfSvgPath }),
                  ]),
                  'Lihat',
                ]),
                h('button', {
                  onClick: () => emit('upload', p.inv.id),
                  class: 'text-[9px] text-gray-300 hover:text-amber-500 transition-colors',
                  title: 'Ganti file PDF',
                }, 'Ganti'),
              ])
            : h('button', {
                onClick: () => emit('upload', p.inv.id),
                class: 'inline-flex items-center gap-1 text-[10px] font-semibold text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 px-2 py-1.5 rounded-lg border border-dashed border-gray-200 hover:border-indigo-300 transition-colors',
                title: 'Upload PDF SPK',
              }, [
                h('svg', { class: 'w-3 h-3', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor', 'stroke-width': '2' }, [
                  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: uploadSvgPath }),
                ]),
                'Upload',
              ]),
        ]),
      ]
    }
  },
})

// SPK Cell (inline edit)
const SpkCell = defineComponent({
  props: { inv: Object },
  emits: ['edit', 'save'],
  setup(p, { emit }) {
    const localVal = ref(p.inv.spk_number ?? '')
    const dirty    = ref(false)

    watch(() => p.inv.spk_number, v => { if (!dirty.value) localVal.value = v ?? '' })

    function onInput(e) {
      localVal.value = e.target.value
      dirty.value    = localVal.value !== (p.inv.spk_number ?? '')
    }
    function onBlur() {
      if (dirty.value) {
        router.patch(route('spk.update-number', p.inv.id), { spk_number: localVal.value }, {
          preserveScroll: true,
          onSuccess: () => { dirty.value = false },
        })
      }
    }
    function onKeydown(e) {
      if (e.key === 'Enter') { e.target.blur() }
      if (e.key === 'Escape') { localVal.value = p.inv.spk_number ?? ''; dirty.value = false }
    }

    return () => h('div', { class: 'flex items-center gap-1.5' }, [
      h('input', {
        value: localVal.value,
        onInput,
        onBlur,
        onKeydown,
        placeholder: 'Ketik nomor SPK…',
        class: [
          'flex-1 min-w-0 text-[11px] border rounded-lg px-2.5 py-1.5 font-mono focus:outline-none focus:ring-2 transition-colors',
          dirty.value
            ? 'border-indigo-300 bg-indigo-50/40 focus:ring-indigo-400'
            : p.inv.spk_number
              ? 'border-emerald-200 bg-emerald-50/30 text-emerald-800 focus:ring-emerald-400'
              : 'border-gray-200 bg-gray-50 text-gray-600 focus:ring-indigo-400',
        ].join(' '),
      }),
      dirty.value
        ? h('span', { class: 'text-[9px] text-indigo-400 shrink-0' }, '↵ Enter')
        : null,
    ])
  },
})

// ── Modal: Tambah SPK ──
const modalOpen   = ref(false)
const dragging    = ref(false)
const parsing     = ref(false)
const saving      = ref(false)
const parsed      = ref(false)
const pdfUrl      = ref(null)
const parseMethod = ref('local')

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
  document_issuer_id:  props.documentIssuers?.[0]?.id   ?? '',
  bank_account_id:     props.bankAccounts?.[0]?.id      ?? '',
  invoice_type:        'monthly',
  interval_months:     null,
  issue_date:          '',
  due_date:            '',
})

const form = ref(defaultForm())

watch(() => form.value.file, newFile => {
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
  pdfUrl.value = newFile ? URL.createObjectURL(newFile) : null
  if (newFile) submitParse()
})

onUnmounted(() => { if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value) })

watch(() => page.props.flash?.spk, val => {
  if (!val) return
  applyParsed(val)
}, { immediate: true })

watch(() => page.props.flash?.spk_local, val => {
  if (!val) return
  applyParsed(val)
}, { immediate: true })

watch(() => page.props.flash?.error, msg => {
  if (!msg || !parsing.value) return
  parsing.value = false
  Swal.fire({ icon: 'warning', title: 'Gagal', text: msg, confirmButtonColor: '#6366f1' })
})

function applyParsed(val) {
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
  if (val.invoice_type) {
    form.value.invoice_type    = val.invoice_type
    form.value.interval_months = val.interval_months ?? null
  }
  parsed.value = true
}

function addDays(dateStr, days) {
  const d = new Date(dateStr); d.setDate(d.getDate() + days)
  return d.toISOString().slice(0, 10)
}
function autoFillDueDate() {
  if (form.value.issue_date) form.value.due_date = addDays(form.value.issue_date, 14)
}

function openModal() {
  modalOpen.value   = true
  form.value        = defaultForm()
  parsed.value      = false
  parseMethod.value = 'local'
  if (pdfUrl.value) { URL.revokeObjectURL(pdfUrl.value); pdfUrl.value = null }
}
function closeModal() { modalOpen.value = false }

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
  const routeName = parseMethod.value === 'local' ? 'spk.parse-local' : 'spk.parse'
  if (parseMethod.value !== 'local') fd.append('provider', parseMethod.value)
  router.post(route(routeName), fd, {
    forceFormData: true, preserveScroll: true, preserveState: true,
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
</script>
