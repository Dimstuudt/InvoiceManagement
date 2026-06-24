<template>
  <AppLayout title="Tambah Signature">
    <div class="max-w-xl">
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Tambah Signature</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama</label>
            <input v-model="form.name" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.name ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jabatan</label>
            <input v-model="form.position" type="text" placeholder="e.g. Direktur Utama" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.position ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.position" class="mt-1 text-xs text-red-500">{{ form.errors.position }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gambar Tanda Tangan <span class="text-gray-400 font-normal">(opsional)</span></label>
            <ImageUpload :error="form.errors.signature_image" @change="form.signature_image = $event" />
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
            <Link :href="route('master.signatures.index')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-300 hover:border-gray-400 rounded-lg transition-colors">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', position: '', signature_image: null });
function submit() { form.post(route('master.signatures.store')); }
</script>
