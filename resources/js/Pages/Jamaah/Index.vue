<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Data/Pagination.vue';
import DataBadge from '@/Components/Data/DataBadge.vue';
import FilterDropdown from '@/Components/Data/FilterDropdown.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    jamaahs: Object,
    filters: Object,
    desas: Array,
    kelompoks: Array,
    dropdowns: Object,
});

const search = ref(props.filters?.search || '');
const filterValues = ref({
    desa_id: props.filters?.desa_id || '',
    kelompok_id: props.filters?.kelompok_id || '',
    jenis_kelamin: props.filters?.jenis_kelamin || '',
    status_pernikahan: props.filters?.status_pernikahan || '',
    kategori_usia: props.filters?.kategori_usia || '',
    kelas_generus: props.filters?.kelas_generus || '',
    kategori_sodaqoh: props.filters?.kategori_sodaqoh || '',
    status_mubaligh: props.filters?.status_mubaligh || '',
});

const showDeleteModal = ref(false);
const deleteId = ref(null);
const deleteName = ref('');

let searchTimeout = null;

const doSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    router.get(route('jamaah.index'), {
        search: search.value,
        ...filterValues.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const confirmDelete = (id, name) => {
    deleteId.value = id;
    deleteName.value = name;
    showDeleteModal.value = true;
};

const deleteJamaah = () => {
    router.delete(route('jamaah.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Data Jamaah">
        <template #header>
            Data Jamaah
        </template>

        <div class="space-y-4">
            <!-- Search Bar -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama jamaah..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @input="doSearch"
                    />
                </div>
                <div class="flex gap-2">
                    <a :href="route('export.jamaah', filterValues)" class="inline-flex items-center">
                        <Button variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export CSV
                        </Button>
                    </a>
                    <Link :href="route('import.index')">
                        <Button variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Import CSV
                        </Button>
                    </Link>
                    <Link :href="route('jamaah.create')">
                        <Button variant="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Jamaah
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <FilterDropdown
                v-model="filterValues"
                :desas="desas"
                :kelompoks="kelompoks"
                :dropdowns="dropdowns"
                @filter="applyFilters"
            />

            <!-- Data Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L/P</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelompok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="jamaah in jamaahs.data" :key="jamaah.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-bold text-sm mr-3">
                                            {{ jamaah.nama_lengkap.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ jamaah.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ jamaah.no_telepon || '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <DataBadge :status="jamaah.jenis_kelamin" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ jamaah.age || '-' }} th
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ jamaah.desa || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ jamaah.kelompok || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <DataBadge v-if="jamaah.status_pernikahan" :status="jamaah.status_pernikahan" />
                                    <span v-else class="text-gray-400 text-sm">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link :href="route('jamaah.show', jamaah.id)" class="text-blue-600 hover:text-blue-900">Lihat</Link>
                                    <Link :href="route('jamaah.edit', jamaah.id)" class="text-yellow-600 hover:text-yellow-900">Edit</Link>
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-900"
                                        @click="confirmDelete(jamaah.id, jamaah.nama_lengkap)"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="jamaahs.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="mt-2">Tidak ada data jamaah ditemukan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 pb-4">
                    <Pagination :links="jamaahs.links" />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" title="Konfirmasi Hapus" @close="showDeleteModal = false">
            <p class="text-gray-600">
                Apakah Anda yakin ingin menghapus data <strong>{{ deleteName }}</strong>?
            </p>
            <template #footer>
                <Button variant="secondary" @click="showDeleteModal = false">Batal</Button>
                <Button variant="danger" @click="deleteJamaah">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
