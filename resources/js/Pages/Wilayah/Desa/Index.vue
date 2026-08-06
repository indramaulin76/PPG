<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Modal from '@/Components/UI/Modal.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import { useAuth } from '@/Composables/useAuth';

const props = defineProps({
    desas: Object,
});

const { isSuperAdmin } = useAuth();
const showModal = ref(false);
const editMode = ref(false);
const editId = ref(null);

const form = useForm({
    nama_desa: '',
    kode_desa: '',
});

const openCreate = () => {
    editMode.value = false;
    editId.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (desa) => {
    editMode.value = true;
    editId.value = desa.id;
    form.nama_desa = desa.nama_desa;
    form.kode_desa = desa.kode_desa || '';
    showModal.value = true;
};

const submit = () => {
    if (editMode.value) {
        form.put(route('wilayah.desa.update', editId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('wilayah.desa.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const showDeleteModal = ref(false);
const deleteId = ref(null);
const deleteName = ref('');

const confirmDelete = (id, name) => {
    deleteId.value = id;
    deleteName.value = name;
    showDeleteModal.value = true;
};

const deleteDesa = () => {
    router.delete(route('wilayah.desa.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Manajemen Desa">
        <template #header>
            Manajemen Desa
        </template>

        <div class="space-y-4 sm:space-y-6">
            <div class="flex justify-end" v-if="isSuperAdmin">
                <Button variant="primary" class="!rounded-xl shadow-lg shadow-blue-100" @click="openCreate">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Desa
                </Button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Desa</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Kode</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Jml Kelompok</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Jml Jamaah</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="desa in desas.data" :key="desa.id" class="group hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm mr-3 group-hover:from-blue-500 group-hover:to-indigo-600 group-hover:text-white transition-all duration-300">
                                            {{ desa.nama_desa.charAt(0) }}
                                        </div>
                                        <div class="text-sm font-bold text-gray-900">{{ desa.nama_desa }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ desa.kode_desa || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ desa.kelompoks_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ desa.jamaahs_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap" v-if="isSuperAdmin">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit(desa)" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-all" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.5 2.5 0 113.536 3.536L12 14.207H11v-1h1l8.586-8.586z" /></svg>
                                        </button>
                                        <button @click="confirmDelete(desa.id, desa.nama_desa)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="desas.data.length === 0" class="px-6 py-16 text-center text-gray-400 bg-white">
                    <svg class="mx-auto h-12 w-12 opacity-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-sm font-bold uppercase tracking-widest">Belum ada data desa</p>
                </div>

                <div class="px-6 py-6 border-t border-gray-50 bg-gray-50/30">
                    <Pagination :links="desas.links" />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" :title="editMode ? 'Edit Desa' : 'Tambah Desa Baru'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Input
                    v-model="form.nama_desa"
                    label="Nama Desa"
                    placeholder="Masukkan nama desa"
                    :error="form.errors.nama_desa"
                    required
                />
                <Input
                    v-model="form.kode_desa"
                    label="Kode Desa"
                    placeholder="Contoh: DSA001"
                    :error="form.errors.kode_desa"
                />
            </form>
            <template #footer>
                <Button variant="secondary" @click="showModal = false">Batal</Button>
                <Button variant="primary" :loading="form.processing" @click="submit">
                    {{ editMode ? 'Simpan Perubahan' : 'Tambah Desa' }}
                </Button>
            </template>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" title="Konfirmasi Hapus" @close="showDeleteModal = false">
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus desa <strong>{{ deleteName }}</strong>?</p>
            <template #footer>
                <Button variant="secondary" @click="showDeleteModal = false">Batal</Button>
                <Button variant="danger" @click="deleteDesa">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
