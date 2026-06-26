<template>
  <AppLayout title="Edit Document Issuer">
    <div class="max-w-2xl mx-auto space-y-4">

      <!-- Back -->
      <Link :href="route('master.document-issuers.index')"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </Link>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <!-- Form Header -->
          <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Edit Document Issuer</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ issuer.name }}</p>
          </div>

          <div class="px-6 py-6 space-y-6">

            <!-- Nama Issuer -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nama Issuer</label>
              <input v-model="form.name" type="text"
                class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                :class="form.errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
              <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Header Image -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Header Image</label>
              <ImageUpload :current-url="issuer.header_image_url" :error="form.errors.header_image" @change="form.header_image = $event" />
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 pt-6">
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Informasi Pajak</p>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pajak</label>
                <input v-model="form.tax_id_name" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                  :class="form.errors.tax_id_name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
                <p v-if="form.errors.tax_id_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.tax_id_name }}</p>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor NPWP</label>
                <input v-model="form.tax_id_number" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                  :class="form.errors.tax_id_number ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
                <p v-if="form.errors.tax_id_number" class="mt-1.5 text-xs text-red-500">{{ form.errors.tax_id_number }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Pajak</label>
                <textarea v-model="form.tax_id_address" rows="3"
                  class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none"
                  :class="form.errors.tax_id_address ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'"></textarea>
                <p v-if="form.errors.tax_id_address" class="mt-1.5 text-xs text-red-500">{{ form.errors.tax_id_address }}</p>
              </div>
            </div>

          </div>

          <!-- Footer Actions -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <Link :href="route('master.document-issuers.index')"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 hover:border-gray-300 rounded-xl transition-colors bg-white">
              Batal
            </Link>
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>

        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ issuer: Object });
const form = useForm({ _method: 'put', name: props.issuer.name, header_image: null, tax_id_name: props.issuer.tax_id_name, tax_id_address: props.issuer.tax_id_address, tax_id_number: props.issuer.tax_id_number });
function submit() { form.post(route('master.document-issuers.update', props.issuer.id)); }
</script>
