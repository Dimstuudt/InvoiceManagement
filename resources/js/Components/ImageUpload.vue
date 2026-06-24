<template>
  <div>
    <!-- Preview state -->
    <div v-if="preview || currentUrl" class="group relative">
      <div class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
        <img :src="preview || currentUrl"
          class="w-full h-48 object-contain p-4" />
        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-200 flex items-center justify-center">
          <label class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-lg text-sm font-medium text-gray-700 shadow-md">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
              Ganti Gambar
            </span>
            <input type="file" class="hidden" accept="image/png,image/jpeg,image/webp" @change="handleChange" />
          </label>
        </div>
      </div>
      <div class="mt-2 flex items-center justify-between">
        <p class="text-xs text-gray-400 truncate">{{ fileName || 'Gambar tersimpan' }}</p>
        <button type="button" @click="clearImage"
          class="text-xs text-red-400 hover:text-red-600 transition-colors ml-2 flex-shrink-0">
          Hapus
        </button>
      </div>
    </div>

    <!-- Upload area -->
    <label v-else
      class="flex flex-col items-center justify-center w-full h-48 rounded-xl border-2 border-dashed border-gray-200 hover:border-indigo-300 bg-gray-50 hover:bg-indigo-50/30 cursor-pointer transition-all duration-200 group">
      <div class="flex flex-col items-center gap-3 pointer-events-none">
        <div class="w-12 h-12 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center group-hover:border-indigo-100 transition-colors">
          <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
        </div>
        <div class="text-center">
          <p class="text-sm font-medium text-gray-600 group-hover:text-indigo-600 transition-colors">
            Klik untuk upload gambar
          </p>
          <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP &bull; Maks. 2MB</p>
        </div>
      </div>
      <input type="file" class="hidden" accept="image/png,image/jpeg,image/webp" @change="handleChange" />
    </label>

    <p v-if="error" class="mt-1.5 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  currentUrl: { type: String, default: null },
  error: { type: String, default: null },
});

const emit = defineEmits(['change']);

const preview = ref(null);
const fileName = ref('');

function handleChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  fileName.value = file.name;
  preview.value = URL.createObjectURL(file);
  emit('change', file);
}

function clearImage() {
  preview.value = null;
  fileName.value = '';
  emit('change', null);
}
</script>
