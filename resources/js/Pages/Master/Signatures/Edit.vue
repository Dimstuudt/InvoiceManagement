<template>
  <AppLayout title="Edit Signature">
    <div class="max-w-2xl mx-auto space-y-4">

      <Link :href="route('master.signatures.index')"
        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
      </Link>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

          <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Edit Signature</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ signature.name }} &bull; {{ signature.position }}</p>
          </div>

          <div class="px-6 py-6 space-y-6">

            <!-- Identitas -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                <input v-model="form.name" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                  :class="form.errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                <input v-model="form.position" type="text"
                  class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                  :class="form.errors.position ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white'" />
                <p v-if="form.errors.position" class="mt-1.5 text-xs text-red-500">{{ form.errors.position }}</p>
              </div>
            </div>

            <!-- Signature Image -->
            <div class="border-t border-gray-100 pt-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Tanda Tangan</label>
              <p class="text-xs text-gray-400 mb-3">Gunakan gambar dengan background transparan (PNG) untuk hasil terbaik</p>
              <ImageUpload :current-url="signature.signature_image_url" :error="form.errors.signature_image" @change="form.signature_image = $event" />
            </div>

          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <Link :href="route('master.signatures.index')"
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

const props = defineProps({ signature: Object });
const form = useForm({ _method: 'put', name: props.signature.name, position: props.signature.position, signature_image: null });
function submit() { form.post(route('master.signatures.update', props.signature.id)); }
</script>
