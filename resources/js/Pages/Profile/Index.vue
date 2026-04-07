<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    no_telepon: props.user.no_telepon || '',
    password: '',
    password_confirmation: '',
});

const formatRole = (role) => {
    return role.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getRoleBadgeColor = (role) => {
    switch(role) {
        case 'super_admin': return 'bg-purple-100 text-purple-800';
        case 'admin_desa': return 'bg-orange-100 text-orange-800';
        case 'admin_kelompok': return 'bg-blue-100 text-blue-800';
        case 'developer': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const submit = () => {
    form.put(route('profile.update'), {
        onSuccess: () => {
            form.password = '';
            form.password_confirmation = '';
        },
    });
};
</script>

<template>
    <AppLayout title="Profil Saya">
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                Profil Saya
            </h2>
        </template>

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- User Info Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl uppercase shadow-sm">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ user.name }}</h3>
                            <p class="text-gray-500">@{{ user.username }}</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full uppercase mt-1" :class="getRoleBadgeColor(user.role)">
                                {{ formatRole(user.role) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 text-xs uppercase tracking-wider">Scope</p>
                            <p class="font-medium text-gray-900">{{ user.scope_label }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 text-xs uppercase tracking-wider">Bergabung</p>
                            <p class="font-medium text-gray-900">{{ user.created_at }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Profil</h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nama Lengkap" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="username" value="Username" />
                            <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.username" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="no_telepon" value="Nomor WhatsApp" />
                            <TextInput id="no_telepon" v-model="form.no_telepon" type="text" placeholder="Contoh: 6281234567890" class="mt-1 block w-full" />
                            <p class="mt-1 text-xs text-gray-500">Nomor WhatsApp untuk notifikasi. Format: 6281234567890</p>
                            <InputError :message="form.errors.no_telepon" class="mt-2" />
                        </div>

                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Ubah Password</h4>
                            <p class="text-xs text-gray-500 mb-3">Kosongkan jika tidak ingin mengubah password</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="password" value="Password Baru" />
                                    <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <PrimaryButton :disabled="form.processing">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
