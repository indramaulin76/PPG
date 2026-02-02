<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Modal from '@/Components/UI/Modal.vue';
import Pagination from '@/Components/Data/Pagination.vue';

const props = defineProps({
    desas: Object,
});

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

        <div class="space-y-4">
            <div class="flex justify-end">
                <Button variant="primary" @click="openCreate">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Desa
                </Button>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Desa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jml Kelompok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jml Jamaah</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(desa, index) in desas.data" :key="desa.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ (desas.current_page - 1) * desas.per_page + index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ desa.nama_desa }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ desa.kode_desa || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ desa.kelompoks_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ desa.jamaahs_count }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <button class="text-yellow-600 hover:text-yellow-900" @click="openEdit(desa)">Edit</button>
                                <button class="text-red-600 hover:text-red-900" @click="confirmDelete(desa.id, desa.nama_desa)">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="desas.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada data desa.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-6 pb-4">
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
