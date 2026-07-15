<template>
  <AppLayout title="Edit Project Category">
    <div class="max-w-xl mx-auto">
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Edit Project Category</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama</label>
            <input v-model="form.name" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.name ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode</label>
            <input v-model="form.code" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.code ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.code" class="mt-1 text-xs text-red-500">{{ form.errors.code }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              Perusahaan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select v-model="form.company_id"
              class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
              :class="form.errors.company_id ? 'border-red-400' : 'border-gray-300'">
              <option value="">— Tanpa perusahaan —</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="form.errors.company_id" class="mt-1 text-xs text-red-500">{{ form.errors.company_id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none"></textarea>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</button>
            <Link :href="route('master.project-categories.index')" class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-300 hover:border-gray-400 rounded-lg transition-colors">Batal</Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ category: Object, companies: Array });
const form = useForm({
  name:        props.category.name,
  code:        props.category.code,
  description: props.category.description ?? '',
  company_id:  props.category.company_id ?? '',
});
function submit() { form.put(route('master.project-categories.update', props.category.id)); }
</script>
