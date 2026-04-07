<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Modal from '@/Components/UI/Modal.vue';
import Pagination from '@/Components/Data/Pagination.vue';

const props = defineProps({
    kelompoks: Object,
    desas: Array,
    filters: Object,
    isAdminDesa: Boolean,
});

const userRole = computed(() => $page.props.auth?.user?.role);
const filterDesa = ref(props.filters?.desa_id || '');

watch(filterDesa, (val) => {
    router.get(route('wilayah.kelompok.index'), { desa_id: val }, { preserveState: true });
});

const showModal = ref(false);
const editMode = ref(false);
const editId = ref(null);

const form = useForm({
    desa_id: '',
    nama_kelompok: '',
});

const openCreate = () => {
    editMode.value = false;
    editId.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (kelompok) => {
    editMode.value = true;
    editId.value = kelompok.id;
    form.desa_id = kelompok.desa_id;
    form.nama_kelompok = kelompok.nama_kelompok;
    showModal.value = true;
};

const submit = () => {
    if (editMode.value) {
        form.put(route('wilayah.kelompok.update', editId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('wilayah.kelompok.store'), {
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

const deleteKelompok = () => {
    router.delete(route('wilayah.kelompok.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Manajemen Kelompok">
        <template #header>
            Manajemen Kelompok
        </template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="w-64">
                    <Select
                        v-model="filterDesa"
                        :options="desas"
                        option-value="id"
                        option-label="nama_desa"
                        placeholder="Filter by Desa"
                    />
                </div>
                <Button variant="primary" @click="openCreate" v-if="userRole === 'super_admin'">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Kelompok
                </Button>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kelompok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jml Jamaah</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(kelompok, index) in kelompoks.data" :key="kelompok.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ (kelompoks.current_page - 1) * kelompoks.per_page + index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ kelompok.nama_kelompok }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ kelompok.desa?.nama_desa || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ kelompok.jamaahs_count }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <button v-if="userRole === 'super_admin' || userRole === 'admin_desa'" class="text-yellow-600 hover:text-yellow-900" @click="openEdit(kelompok)">Edit</button>
                                <button v-if="userRole === 'super_admin'" class="text-red-600 hover:text-red-900" @click="confirmDelete(kelompok.id, kelompok.nama_kelompok)">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="kelompoks.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada data kelompok.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-6 pb-4">
                    <Pagination :links="kelompoks.links" />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" :title="editMode ? 'Edit Kelompok' : 'Tambah Kelompok Baru'" @close="showModal = false">
            <form @submit.prevent="submit" class="space-y-4">
                <Select
                    v-model="form.desa_id"
                    label="Desa"
                    :options="desas"
                    option-value="id"
                    option-label="nama_desa"
                    placeholder="Pilih Desa"
                    :error="form.errors.desa_id"
                    required
                />
                <Input
                    v-model="form.nama_kelompok"
                    label="Nama Kelompok"
                    placeholder="Masukkan nama kelompok"
                    :error="form.errors.nama_kelompok"
                    required
                />
            </form>
            <template #footer>
                <Button variant="secondary" @click="showModal = false">Batal</Button>
                <Button variant="primary" :loading="form.processing" @click="submit">
                    {{ editMode ? 'Simpan Perubahan' : 'Tambah Kelompok' }}
                </Button>
            </template>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" title="Konfirmasi Hapus" @close="showDeleteModal = false">
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus kelompok <strong>{{ deleteName }}</strong>?</p>
            <template #footer>
                <Button variant="secondary" @click="showDeleteModal = false">Batal</Button>
                <Button variant="danger" @click="deleteKelompok">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
