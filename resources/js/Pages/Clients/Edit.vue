<template>
  <AppLayout title="Edit Client">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Edit Client</h2>

        <form @submit.prevent="submit" class="space-y-5">

          <!-- Kategori -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Client Category</label>
            <select v-model="form.client_category_id" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.client_category_id ? 'border-red-400' : 'border-gray-300'">
              <option value="">-- Pilih Kategori --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
            <p v-if="form.errors.client_category_id" class="mt-1 text-xs text-red-500">{{ form.errors.client_category_id }}</p>
          </div>

          <!-- Company Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Perusahaan</label>
            <input v-model="form.company_name" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.company_name ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.company_name" class="mt-1 text-xs text-red-500">{{ form.errors.company_name }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
            <textarea v-model="form.address" rows="3" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none" :class="form.errors.address ? 'border-red-400' : 'border-gray-300'"></textarea>
            <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
          </div>

          <!-- City -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota</label>
            <input v-model="form.city" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.city ? 'border-red-400' : 'border-gray-300'" />
            <p v-if="form.errors.city" class="mt-1 text-xs text-red-500">{{ form.errors.city }}</p>
          </div>

          <!-- Director & PIC -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Direktur</label>
              <input v-model="form.director" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.director ? 'border-red-400' : 'border-gray-300'" />
              <p v-if="form.errors.director" class="mt-1 text-xs text-red-500">{{ form.errors.director }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">PIC</label>
              <input v-model="form.pic" type="text" class="w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" :class="form.errors.pic ? 'border-red-400' : 'border-gray-300'" />
              <p v-if="form.errors.pic" class="mt-1 text-xs text-red-500">{{ form.errors.pic }}</p>
            </div>
          </div>

          <!-- Phone Numbers -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor Telepon</label>
            <div class="space-y-2">
              <div v-for="(phone, i) in form.phones" :key="i" class="flex gap-2">
                <input v-model="form.phones[i]" type="text" placeholder="08xx-xxxx-xxxx"
                  class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
                <button type="button" @click="removePhone(i)" v-if="form.phones.length > 1"
                  class="px-3 py-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg border border-red-200 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
            <button type="button" @click="form.phones.push('')"
              class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah Nomor
            </button>
          </div>

          <!-- Emails -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <div class="space-y-2">
              <div v-for="(email, i) in form.emails" :key="i" class="flex gap-2">
                <input v-model="form.emails[i]" type="email" placeholder="email@perusahaan.com"
                  class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
                <button type="button" @click="removeEmail(i)" v-if="form.emails.length > 1"
                  class="px-3 py-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg border border-red-200 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
            <button type="button" @click="form.emails.push('')"
              class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah Email
            </button>
          </div>

          <!-- Social Media -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Social Media</label>
            <div class="space-y-2">
              <div v-for="(sm, i) in form.social_media" :key="i" class="flex gap-2">
                <input v-model="form.social_media[i]" type="text" placeholder="https://instagram.com/..."
                  class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
                <button type="button" @click="removeSocialMedia(i)" v-if="form.social_media.length > 1"
                  class="px-3 py-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg border border-red-200 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
            <button type="button" @click="form.social_media.push('')"
              class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah Social Media
            </button>
          </div>

          <!-- NPWP Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">NPWP Image</label>
            <ImageUpload :current-url="client.npwp_image_url" :error="form.errors.npwp_image" @change="form.npwp_image = $event" />
          </div>

          <!-- Status & Is Active -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Client Status</label>
              <select v-model="form.client_status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <option value="prospect">Prospect</option>
                <option value="active_client">Active Client</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Aktif</label>
              <button type="button" @click="form.is_active = !form.is_active"
                class="mt-1 flex items-center gap-3 group">
                <div class="relative w-11 h-6 rounded-full transition-colors duration-200"
                  :class="form.is_active ? 'bg-indigo-600' : 'bg-gray-300'">
                  <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                    :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"></div>
                </div>
                <span class="text-sm" :class="form.is_active ? 'text-indigo-600 font-medium' : 'text-gray-500'">
                  {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <Link :href="route('clients.index')"
              class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-300 hover:border-gray-400 rounded-lg transition-colors">
              Batal
            </Link>
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

const props = defineProps({
  client: Object,
  categories: Array,
});

const form = useForm({
  _method: 'put',
  client_category_id: props.client.client_category_id,
  company_name: props.client.company_name,
  address: props.client.address,
  city: props.client.city,
  director: props.client.director,
  pic: props.client.pic,
  phones: props.client.phones.length ? props.client.phones : [''],
  emails: props.client.emails.length ? props.client.emails : [''],
  social_media: props.client.social_media.length ? props.client.social_media : [''],
  npwp_image: null,
  client_status: props.client.client_status,
  is_active: props.client.is_active,
});

function removePhone(index) {
  form.phones.splice(index, 1);
}

function removeEmail(index) {
  form.emails.splice(index, 1);
}

function removeSocialMedia(index) {
  form.social_media.splice(index, 1);
}

function submit() {
  form.post(route('clients.update', props.client.id));
}
</script>
