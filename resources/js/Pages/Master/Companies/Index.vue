<template>
  <AppLayout title="Perusahaan">
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Perusahaan</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ companies.length }} perusahaan · {{ categories.length }} total kategori</p>
        </div>
        <button @click="openAdd"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Perusahaan
        </button>
      </div>

      <!-- Empty state -->
      <div v-if="companies.length === 0"
        class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada perusahaan</p>
        <p class="text-xs text-gray-400 mt-1 mb-5">Tambahkan perusahaan untuk mengelompokkan kategori proyek</p>
        <button @click="openAdd"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Tambah Perusahaan
        </button>
      </div>

      <!-- Company Cards (Lemari) -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div v-for="company in companies" :key="company.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <!-- Card Header -->
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-white tracking-wide">{{ company.code }}</span>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ company.name }}</p>
                <p v-if="company.description" class="text-xs text-gray-400 truncate mt-0.5">{{ company.description }}</p>
              </div>
            </div>
            <div class="flex items-center gap-1 shrink-0 ml-2">
              <button @click="openAssign(company)" title="Kelola Kategori"
                class="p-1.5 rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
              </button>
              <button @click="openEdit(company)" title="Edit"
                class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414A2 2 0 019 13z"/>
                </svg>
              </button>
              <button @click="destroy(company)" title="Hapus"
                class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Category list inside the "lemari" -->
          <div class="px-5 py-3">
            <div v-if="company.project_categories.length === 0"
              class="flex items-center gap-2 py-3 text-xs text-gray-300 italic">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
              Belum ada kategori — klik ikon list untuk assign
            </div>
            <div v-else class="flex flex-wrap gap-2 py-2">
              <div v-for="cat in company.project_categories" :key="cat.id"
                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-100">
                <span class="text-[10px] font-bold text-indigo-500 font-mono">{{ cat.code }}</span>
                <span class="text-xs text-gray-600">{{ cat.name }}</span>
              </div>
            </div>
          </div>

          <!-- Footer count -->
          <div class="px-5 py-2.5 border-t border-gray-50 bg-gray-50/40">
            <span class="text-[11px] text-gray-400">
              {{ company.project_categories.length }} kategori terdaftar
            </span>
          </div>

        </div>
      </div>

      <!-- Unassigned categories -->
      <div v-if="unassigned.length > 0" class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          <p class="text-xs font-semibold text-amber-700">{{ unassigned.length }} kategori belum masuk perusahaan</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <div v-for="cat in unassigned" :key="cat.id"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-amber-200">
            <span class="text-[10px] font-bold text-amber-500 font-mono">{{ cat.code }}</span>
            <span class="text-xs text-gray-600">{{ cat.name }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Modal Tambah / Edit ──────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="closeForm"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-5">
            {{ editTarget ? 'Edit Perusahaan' : 'Tambah Perusahaan' }}
          </h3>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Nama Perusahaan <span class="text-red-400">*</span></label>
              <input v-model="form.name" type="text" placeholder="PT Smartoop Indonesia"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400"/>
              <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Kode <span class="text-red-400">*</span></label>
              <input v-model="form.code" type="text" placeholder="SMP" maxlength="20"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400"/>
              <p class="mt-1 text-[11px] text-gray-400">Singkatan singkat untuk label di tabel (maks 20 karakter)</p>
              <p v-if="errors.code" class="mt-1 text-xs text-red-500">{{ errors.code }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Deskripsi</label>
              <textarea v-model="form.description" rows="2" placeholder="Keterangan tambahan..."
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 resize-none"/>
            </div>
            <div class="flex gap-2 pt-1">
              <button type="button" @click="closeForm"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="submitting"
                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors disabled:opacity-50">
                {{ submitting ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal Assign Kategori ───────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showAssign" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="showAssign = false"/>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-sm font-semibold text-gray-900">Kelola Kategori</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ assignTarget?.name }}</p>
            </div>
            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg font-mono">
              {{ assignTarget?.code }}
            </span>
          </div>

          <p class="text-xs text-gray-500 mb-3">Pilih kategori proyek yang masuk ke perusahaan ini:</p>

          <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1">
            <label v-for="cat in categories" :key="cat.id"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors"
              :class="assignIds.includes(cat.id) ? 'bg-indigo-50 border border-indigo-100' : 'bg-gray-50 hover:bg-gray-100 border border-transparent'">
              <input type="checkbox" :value="cat.id" v-model="assignIds"
                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"/>
              <span class="text-[10px] font-bold text-indigo-400 font-mono w-10 shrink-0">{{ cat.code }}</span>
              <span class="text-sm text-gray-700">{{ cat.name }}</span>
              <span v-if="cat.company_id && cat.company_id !== assignTarget?.id"
                class="ml-auto text-[10px] text-amber-500 font-medium shrink-0">pindah</span>
            </label>
          </div>

          <div class="flex gap-2 mt-5">
            <button @click="showAssign = false"
              class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
              Batal
            </button>
            <button @click="submitAssign" :disabled="submitting"
              class="flex-1 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors disabled:opacity-50">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  companies:  Array,
  categories: Array,
})

// Computed: unassigned categories
const unassigned = computed(() =>
  props.categories.filter(c => !c.company_id)
)

// ── Form modal (add / edit) ──────────────────────────────────────
const showForm    = ref(false)
const editTarget  = ref(null)
const submitting  = ref(false)
const errors      = ref({})

const form = ref({ name: '', code: '', description: '' })

function openAdd() {
  editTarget.value  = null
  form.value        = { name: '', code: '', description: '' }
  errors.value      = {}
  showForm.value    = true
}

function openEdit(company) {
  editTarget.value  = company
  form.value        = { name: company.name, code: company.code, description: company.description ?? '' }
  errors.value      = {}
  showForm.value    = true
}

function closeForm() {
  showForm.value = false
}

function submitForm() {
  submitting.value = true
  errors.value     = {}

  const payload = { ...form.value, code: form.value.code.toUpperCase() }

  if (editTarget.value) {
    router.patch(route('master.companies.update', editTarget.value.id), payload, {
      onSuccess: () => { showForm.value = false },
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  } else {
    router.post(route('master.companies.store'), payload, {
      onSuccess: () => { showForm.value = false },
      onError:   (e) => { errors.value = e },
      onFinish:  () => { submitting.value = false },
    })
  }
}

function destroy(company) {
  if (!confirm(`Hapus "${company.name}"? Semua kategori di dalamnya akan dilepas.`)) return
  router.delete(route('master.companies.destroy', company.id))
}

// ── Assign modal ─────────────────────────────────────────────────
const showAssign  = ref(false)
const assignTarget = ref(null)
const assignIds    = ref([])

function openAssign(company) {
  assignTarget.value = company
  assignIds.value    = company.project_categories.map(c => c.id)
  showAssign.value   = true
}

function submitAssign() {
  submitting.value = true
  router.patch(route('master.companies.assign', assignTarget.value.id), { category_ids: assignIds.value }, {
    onSuccess: () => { showAssign.value = false },
    onFinish:  () => { submitting.value = false },
  })
}
</script>
