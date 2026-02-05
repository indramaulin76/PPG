<script setup>
import { computed, ref, watch }  from 'vue';
import Select from '@/Components/UI/Select.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    desas: {
        type: Array,
        default: () => [],
    },
    kelompoks: {
        type: Array,
        default: () => [],
    },
    dropdowns: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'filter']);

const filters = ref({
    desa_id: props.modelValue.desa_id || '',
    kelompok_id: props.modelValue.kelompok_id || '',
    jenis_kelamin: props.modelValue.jenis_kelamin || '',
    status_pernikahan: props.modelValue.status_pernikahan || '',
    kategori_usia: props.modelValue.kategori_usia || '',
    kelas_generus: props.modelValue.kelas_generus || '',
    kategori_sodaqoh: props.modelValue.kategori_sodaqoh || '',
    status_mubaligh: props.modelValue.status_mubaligh || '',
});

// Filter kelompoks based on selected desa
const filteredKelompoks = computed(() => {
    if (!filters.value.desa_id) return props.kelompoks;
    return props.kelompoks.filter(k => k.desa_id == filters.value.desa_id);
});

// Watch desa change to reset kelompok
watch(() => filters.value.desa_id, () => {
    filters.value.kelompok_id = '';
});

const applyFilters = () => {
    emit('update:modelValue', { ...filters.value });
    emit('filter');
};

const clearFilters = () => {
    filters.value = {
        desa_id: '',
        kelompok_id: '',
        jenis_kelamin: '',
        status_pernikahan: '',
        kategori_usia: '',
        kelas_generus: '',
        kategori_sodaqoh: '',
        status_mubaligh: '',
    };
    emit('update:modelValue', { ...filters.value });
    emit('filter');
};

const formatDropdownOptions = (items) => {
    if (!items) return [];
    return items.map(item => ({ value: item, label: item }));
};

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const statusOptions = [
    { value: 'BELUM', label: 'Belum Menikah' },
    { value: 'MENIKAH', label: 'Menikah' },
    { value: 'JANDA', label: 'Janda' },
    { value: 'DUDA', label: 'Duda' },
];

const usiaOptions = [
    { value: 'BALITA', label: 'Balita (0-5 th)' },
    { value: 'ANAK', label: 'Anak (6-12 th)' },
    { value: 'REMAJA', label: 'Remaja (13-17 th)' },
    { value: 'PEMUDA', label: 'Pemuda (18-40 th)' },
    { value: 'DEWASA', label: 'Dewasa (41-60 th)' },
    { value: 'LANSIA', label: 'Lansia (60+ th)' },
];
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-700">Filter Data</h4>
            <button
                type="button"
                class="text-sm text-gray-500 hover:text-gray-700"
                @click="clearFilters"
            >
                Reset
            </button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
            <Select
                v-model="filters.desa_id"
                :options="desas"
                option-value="id"
                option-label="nama_desa"
                placeholder="Semua Desa"
            />
            <Select
                v-model="filters.kelompok_id"
                :options="filteredKelompoks"
                option-value="id"
                option-label="nama_kelompok"
                placeholder="Semua Kelompok"
            />
            <Select
                v-model="filters.jenis_kelamin"
                :options="genderOptions"
                placeholder="L/P"
            />
            <Select
                v-model="filters.status_pernikahan"
                :options="statusOptions"
                placeholder="Status"
            />
            <Select
                v-model="filters.kategori_usia"
                :options="usiaOptions"
                placeholder="Kategori Usia"
            />
            <Select
                v-model="filters.kelas_generus"
                :options="formatDropdownOptions(dropdowns.kelas_generus)"
                placeholder="Kelas Generus"
            />
            <Select
                v-model="filters.kategori_sodaqoh"
                :options="formatDropdownOptions(dropdowns.kategori_sodaqoh)"
                placeholder="Kategori Ekonomi"
            />
            <Select
                v-model="filters.status_mubaligh"
                :options="formatDropdownOptions(dropdowns.status_mubaligh)"
                placeholder="Status Mubaligh"
            />
        </div>
        <div class="flex justify-end mt-3">
            <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                @click="applyFilters"
            >
                Terapkan Filter
            </button>
        </div>
    </div>
</template>
