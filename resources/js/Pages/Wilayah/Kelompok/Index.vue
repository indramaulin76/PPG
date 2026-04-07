<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Select from '@/Components/UI/Select.vue';
import Pagination from '@/Components/Data/Pagination.vue';

const props = defineProps({
    kelompoks: Object,
    desas: Array,
    filters: Object,
    isAdminDesa: Boolean,
});

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role);
const filterDesa = ref(props.filters?.desa_id || '');

watch(filterDesa, (val) => {
    router.get(route('wilayah.kelompok.index'), { desa_id: val }, { preserveState: true });
});
</script>

<template>
    <AppLayout title="Manajemen Kelompok">
        <template #header>
            Manajemen Kelompok
        </template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="w-64" v-if="userRole === 'super_admin'">
                    <Select
                        v-model="filterDesa"
                        :options="desas"
                        option-value="id"
                        option-label="nama_desa"
                        placeholder="Filter by Desa"
                    />
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kelompok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Desa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jml Jamaah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(kelompok, index) in kelompoks.data" :key="kelompok.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ (kelompoks.current_page - 1) * kelompoks.per_page + index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ kelompok.nama_kelompok }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ kelompok.desa?.nama_desa || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ kelompok.jamaahs_count }}</td>
                        </tr>
                        <tr v-if="kelompoks.data.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Belum ada data kelompok.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-6 pb-4">
                    <Pagination :links="kelompoks.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
