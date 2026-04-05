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
    paket: props.filters?.paket || '',
    kategori_sodaqoh: props.filters?.kategori_sodaqoh || '',
    status_mubaligh: props.filters?.status_mubaligh || '',
});

const showDeleteModal = ref(false);
const showDeleteAllModal = ref(false);
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

const deleteAllJamaah = () => {
    router.delete(route('jamaah.destroy-all'), {
        onSuccess: () => {
            showDeleteAllModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Data Jamaah">
        <template #header>
            Data Jamaah
        </template>

        <div class="space-y-4 sm:space-y-6">
            <!-- Search & Actions Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                    <div class="relative flex-1 max-w-2xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama jama'ah..."
                            class="block w-full pl-11 pr-4 py-2.5 bg-gray-50 border-transparent rounded-xl text-sm placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            @input="doSearch"
                        />
                    </div>
                    
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-hide">
                        <a :href="route('export.jamaah', filterValues)" class="shrink-0">
                            <Button variant="secondary" size="sm" class="!rounded-xl">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export
                            </Button>
                        </a>
                        <button v-if="$page.props.auth.user.role === 'super_admin'" @click="showDeleteAllModal = true" class="shrink-0">
                            <Button variant="danger" size="sm" class="!rounded-xl shadow-lg shadow-red-100">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Kosongkan Data
                            </Button>
                        </button>
                        <Link :href="route('jamaah.create')" class="shrink-0">
                            <Button variant="primary" size="sm" class="!rounded-xl shadow-lg shadow-blue-100">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Filters Component -->
                <FilterDropdown
                    v-model="filterValues"
                    :desas="desas"
                    :kelompoks="kelompoks"
                    :dropdowns="dropdowns"
                    @filter="applyFilters"
                />
            </div>

            <!-- Data Display -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Desa</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Kelompok</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Nama Lengkap</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Tempat Lahir</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Tgl Lahir</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">L/P</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Kelas Generus</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Status Nikah</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Kategori Sodaqoh</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Dapukan</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Pekerjaan</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Dewan Guru</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">Pendidikan</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap">KBM Diminati</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50/90 backdrop-blur-sm">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="jamaah in jamaahs.data" :key="jamaah.id" class="group hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ jamaah.desa || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ jamaah.kelompok || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ jamaah.nama_lengkap }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.tempat_lahir || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.tgl_lahir || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <DataBadge :status="jamaah.jenis_kelamin" />
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.kelas_generus || '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <DataBadge v-if="jamaah.status_pernikahan" :status="jamaah.status_pernikahan" />
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.kategori_sodaqoh || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.dapukan || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.pekerjaan || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.status_mubaligh || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.pendidikan_terakhir || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ jamaah.minat_kbm || '-' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-blue-50/30 transition-colors">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('jamaah.show', jamaah.id)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                        <Link :href="route('jamaah.edit', jamaah.id)" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-all" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.5 2.5 0 113.536 3.536L12 14.207H11v-1h1l8.586-8.586z" /></svg>
                                        </Link>
                                        <button @click="confirmDelete(jamaah.id, jamaah.nama_lengkap)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (Visible below md) -->
                <div class="md:hidden divide-y divide-gray-50">
                    <div v-for="jamaah in jamaahs.data" :key="jamaah.id" class="p-4 bg-white active:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <Link :href="route('jamaah.show', jamaah.id)" class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg shadow-sm">
                                    {{ jamaah.nama_lengkap.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ jamaah.nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500 font-medium">{{ jamaah.desa || '-' }} • {{ jamaah.kelompok || '-' }}</div>
                                </div>
                            </Link>
                            <div class="flex gap-1 ml-2">
                                <Link :href="route('jamaah.edit', jamaah.id)" class="p-2 text-gray-400 hover:text-yellow-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.5 2.5 0 113.536 3.536L12 14.207H11v-1h1l8.586-8.586z" /></svg>
                                </Link>
                                <button @click="confirmDelete(jamaah.id, jamaah.nama_lengkap)" class="p-2 text-gray-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-2">
                            <div class="flex gap-1.5 flex-wrap">
                                <DataBadge :status="jamaah.jenis_kelamin" size="xs" />
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">{{ jamaah.age || '-' }} TH</span>
                            </div>
                            <DataBadge v-if="jamaah.status_pernikahan" :status="jamaah.status_pernikahan" size="xs" />
                        </div>
                    </div>
                </div>

                <div v-if="jamaahs.data.length === 0" class="px-6 py-16 text-center text-gray-400 bg-white">
                    <svg class="mx-auto h-12 w-12 opacity-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm font-bold uppercase tracking-widest">Tidak ada data ditemukan</p>
                </div>

                <!-- Pagination Area -->
                <div class="px-6 py-6 border-t border-gray-50 bg-gray-50/30">
                    <Pagination :links="jamaahs.links" />
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" title="Konfirmasi Hapus" @close="showDeleteModal = false">
            <div class="p-2 text-center sm:text-left">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Data Jamaah?</h3>
                <p class="text-sm text-gray-500">
                    Anda yakin ingin menghapus <strong>{{ deleteName }}</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <template #footer>
                <div class="flex flex-col-reverse sm:flex-row gap-2 w-full sm:w-auto">
                    <Button variant="secondary" class="w-full sm:w-auto !rounded-xl" @click="showDeleteModal = false">Batal</Button>
                    <Button variant="danger" class="w-full sm:w-auto !rounded-xl shadow-lg shadow-red-100" @click="deleteJamaah">Hapus Sekarang</Button>
                </div>
            </template>
        </Modal>

        <!-- Delete All Modal -->
        <Modal :show="showDeleteAllModal" title="Peringatan Keras!" @close="showDeleteAllModal = false">
            <div class="p-2 text-center sm:text-left">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Kosongkan Seluruh Data Jamaah?</h3>
                <p class="text-sm text-gray-500">
                    Anda akan menghapus <strong>SELURUH DATA JAMAAH</strong> secara permanen di semua wilayah. Tindakan ini tidak bisa dibatalkan dan data tidak dapat dikembalikan.
                </p>
            </div>
            <template #footer>
                <div class="flex flex-col-reverse sm:flex-row gap-2 w-full sm:w-auto">
                    <Button variant="secondary" class="w-full sm:w-auto !rounded-xl" @click="showDeleteAllModal = false">Batal</Button>
                    <Button variant="danger" class="w-full sm:w-auto !rounded-xl shadow-lg shadow-red-100" @click="deleteAllJamaah">Saya Yakin, Hapus Semua</Button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
