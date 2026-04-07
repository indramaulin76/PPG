<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/UI/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    admins: Object,
    filters: Object,
    allowedRoles: Array,
    desas: Array,
    kelompoks: Array,
});

const search = ref(props.filters.search || '');
const filterDesa = ref(props.filters.desa_id || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingAdmin = ref(null);

// Form for Create/Edit
const form = useForm({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    role: '',
    desa_id: '',
    kelompok_id: '',
    is_active: true,
});

// Watch filters to debounce and update
let timeout;
watch([search, filterDesa], ([sValue, dValue]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.index'), { 
            search: sValue,
            desa_id: dValue 
        }, { preserveState: true, replace: true });
    }, 300);
});

// Computeds for dynamic form fields
const showDesaDropdown = computed(() => {
    return form.role === 'admin_desa' || form.role === 'admin_kelompok';
});

// Filter kelompoks based on selected desa (if applicable)
const filteredKelompoks = computed(() => {
    if (!form.desa_id) return [];
    return props.kelompoks.filter(k => k.desa_id == form.desa_id);
});

const openCreateModal = () => {
    form.reset();
    
    // If only 1 desa available (Admin Desa logged in), select it
    if (props.desas.length === 1) {
        form.desa_id = props.desas[0].id;
    }
    // If only 1 role available (Admin Desa logged in), select it
    if (props.allowedRoles.length === 1) {
        form.role = props.allowedRoles[0];
    }
    
    showCreateModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    form.reset();
    form.clearErrors();
};

const openEditModal = (admin) => {
    editingAdmin.value = admin;
    form.reset();
    form.name = admin.name;
    form.username = admin.username;
    form.role = admin.role;
    form.desa_id = admin.desa_id || '';
    form.kelompok_id = admin.kelompok_id || '';
    form.is_active = !!admin.is_active;
    
    showEditModal.value = true;
};

const submitCreate = () => {
    form.post(route('admin.store'), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const submitEdit = () => {
    form.put(route('admin.update', editingAdmin.value.id), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const deleteAdmin = (admin) => {
    if (confirm('Apakah Anda yakin ingin menghapus admin ini?')) {
        router.delete(route('admin.destroy', admin.id));
    }
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

const formatRole = (role) => {
    return role.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <AppLayout title="Kelola Admin">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                    Kelola Admin
                </h2>
                <PrimaryButton type="button" @click="openCreateModal">
                    + Tambah Admin
                </PrimaryButton>
            </div>
        </template>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <TextInput
                            v-model="search"
                            placeholder="Cari nama atau username..."
                            class="pl-10 w-full"
                        />
                    </div>
                    <div class="w-full md:w-64" v-if="desas.length > 1">
                        <select 
                            v-model="filterDesa"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Semua Desa</option>
                            <option v-for="desa in desas" :key="desa.id" :value="desa.id">
                                {{ desa.nama_desa }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Role</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Wilayah</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="admin in admins.data" :key="admin.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">
                                        {{ admin.name.charAt(0) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900" :title="admin.name">{{ admin.name }}</div>
                                        <div class="text-xs text-gray-500">@{{ admin.username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="px-2 inline-flex text-[10px] leading-5 font-semibold rounded-full uppercase" :class="getRoleBadgeColor(admin.role)">
                                    {{ formatRole(admin.role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">
                                <div v-if="admin.desa" class="font-medium text-gray-700">{{ admin.desa }}</div>
                                <div v-if="admin.kelompok" class="text-xs text-gray-400">{{ admin.kelompok }}</div>
                                <div v-if="!admin.desa && !admin.kelompok" class="text-gray-400 italic">Seluruh Sistem</div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="admin.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ admin.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button @click="openEditModal(admin)" class="text-indigo-600 hover:text-indigo-900 font-bold mr-3">Edit</button>
                                <button @click="deleteAdmin(admin)" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="admins.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                Tidak ada admin ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200" v-if="admins.links.length > 3">
                 <div class="flex justify-center">
                     <template v-for="(link, k) in admins.links" :key="k">
                        <Link
                            v-if="link.url"
                            class="px-3 py-1 mx-1 border rounded text-sm hover:bg-gray-50"
                            :class="{'bg-blue-50 text-blue-600 border-blue-200': link.active}"
                            :href="link.url"
                            v-html="link.label"
                        />
                     </template>
                 </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">
                    {{ showCreateModal ? 'Tambah Admin Baru' : 'Edit Admin' }}
                </h2>

                <form @submit.prevent="showCreateModal ? submitCreate() : submitEdit()">
                    <div class="space-y-4">
                        <!-- Name & Email -->
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

                        <!-- Role Selection -->
                        <div>
                            <InputLabel for="role" value="Role Admin" />
                            <select id="role" v-model="form.role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled>Pilih Role</option>
                                <option v-for="role in allowedRoles" :key="role" :value="role">
                                    {{ formatRole(role) }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role" class="mt-2" />
                        </div>

                        <!-- Scope Selection -->
                        <div v-if="form.role === 'admin_desa' || form.role === 'admin_kelompok'">
                            <InputLabel for="desa_id" value="Wilayah Desa" />
                             <select id="desa_id" v-model="form.desa_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" :disabled="desas.length === 1">
                                <option value="">Pilih Desa</option>
                                <option v-for="desa in desas" :key="desa.id" :value="desa.id">
                                    {{ desa.nama_desa }}
                                </option>
                            </select>
                            <InputError :message="form.errors.desa_id" class="mt-2" />
                        </div>

                        <div v-if="form.role === 'admin_kelompok'">
                            <InputLabel for="kelompok_id" value="Wilayah Kelompok" />
                             <select id="kelompok_id" v-model="form.kelompok_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Pilih Kelompok</option>
                                <option v-for="kelompok in filteredKelompoks" :key="kelompok.id" :value="kelompok.id">
                                    {{ kelompok.nama_kelompok }}
                                </option>
                            </select>
                            <InputError :message="form.errors.kelompok_id" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="pt-2">
                            <hr class="my-2" />
                            <p class="text-[10px] text-gray-500 mb-2 uppercase tracking-wider" v-if="showEditModal">Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                        
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" :required="showCreateModal" />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" :required="showCreateModal" />
                        </div>

                        <div class="flex items-center gap-2 bg-gray-50 p-3 rounded-lg border">
                             <input type="checkbox" id="is_active" v-model="form.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                             <label for="is_active" class="text-sm font-bold text-gray-700">Akun ini diaktifkan</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModal">Batal</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            {{ showCreateModal ? 'Simpan Akun' : 'Update Akun' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
