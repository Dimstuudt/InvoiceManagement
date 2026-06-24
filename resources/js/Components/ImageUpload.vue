<template>
  <div>
    <!-- Preview -->
    <div v-if="preview || currentUrl" class="mb-3">
      <img :src="preview || currentUrl" class="h-20 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1" />
    </div>

    <!-- Input -->
    <label class="flex items-center gap-3 cursor-pointer">
      <div class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:border-gray-400 rounded-lg transition-colors">
        {{ preview || currentUrl ? 'Ganti Gambar' : 'Pilih Gambar' }}
      </div>
      <span class="text-sm text-gray-500">{{ fileName || 'PNG, JPG, WEBP maks. 2MB' }}</span>
      <input type="file" class="hidden" accept="image/png,image/jpeg,image/webp" @change="handleChange" />
    </label>

    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

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
</script>
