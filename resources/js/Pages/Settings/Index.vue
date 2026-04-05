<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    app_name: props.settings.app_name || 'SI - JEMAAH',
    app_logo: null,
});

const logoPreview = ref(
    props.settings.app_logo 
        ? '/storage/' + props.settings.app_logo 
        : null
);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.app_logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    // Inertia will automatically send this as multipart/form-data because we have a File object
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.clearErrors();
        },
    });
};
</script>

<template>
    <AppLayout title="Pengaturan Web">
        <template #header>
            Pengaturan Website
        </template>

        <div class="max-w-3xl space-y-6">
            <Card title="⚙️ Pengaturan Tampilan Utama">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- App Name -->
                    <div>
                        <Input
                            v-model="form.app_name"
                            label="Nama Website (Title & Sidebar Utama)"
                            placeholder="Contoh: SI - JEMAAH"
                            :error="form.errors.app_name"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Nama ini akan muncul tab browser dan di pojok kiri atas aplikasi.
                        </p>
                    </div>

                    <!-- App Logo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Logo Website (Opsional)
                        </label>
                        
                        <div class="mt-2 flex items-center gap-6">
                            <!-- Preview Box -->
                            <div class="h-24 w-24 rounded-2xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden shrink-0">
                                <template v-if="logoPreview">
                                    <img :src="logoPreview" alt="Logo Preview" class="h-full w-full object-cover">
                                </template>
                                <template v-else>
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </template>
                            </div>

                            <!-- Upload Button & Info -->
                            <div>
                                <input 
                                    type="file" 
                                    id="app_logo" 
                                    accept="image/*" 
                                    class="hidden" 
                                    @change="handleLogoChange"
                                >
                                <label 
                                    for="app_logo"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 cursor-pointer"
                                >
                                    Pilih Gambar Logo
                                </label>
                                <p class="mt-2 text-xs text-gray-500">
                                    Format: JPG, PNG, GIF. Maks: 2MB.<br>
                                    Rasio terbaik 1:1 (Persegi).
                                </p>
                                <p v-if="form.errors.app_logo" class="mt-1 text-xs text-red-600 font-medium">
                                    {{ form.errors.app_logo }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end border-t border-gray-100 pt-6 mt-6">
                        <Button type="submit" variant="primary" :loading="form.processing">
                            Simpan Pengaturan
                        </Button>
                    </div>

                </form>
            </Card>
        </div>
    </AppLayout>
</template>
