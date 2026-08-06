<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatsGrid from '@/Components/Dashboard/StatsGrid.vue';
import DashboardCharts from '@/Components/Dashboard/DashboardCharts.vue';
import RecentJamaahTable from '@/Components/Dashboard/RecentJamaahTable.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalJamaah: 0,
            totalKK: 0,
            totalDesa: 0,
            totalKelompok: 0,
            genderRatio: '50:50',
        }),
    },
    ageDistribution: Object,
    maritalDistribution: Object,
    recentJamaah: Array,
});

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num);

const statCards = computed(() => [
    { name: 'Total Jamaah', value: formatNumber(props.stats.totalJamaah), icon: 'users', color: 'bg-blue-600', shadow: 'shadow-blue-100' },
    { name: 'Total Kelompok', value: formatNumber(props.stats.totalKelompok), icon: 'collection', color: 'bg-indigo-600', shadow: 'shadow-indigo-100' },
    { name: 'Total KK', value: formatNumber(props.stats.totalKK), icon: 'home', color: 'bg-emerald-600', shadow: 'shadow-emerald-100' },
    { name: 'Rasio L/P', value: props.stats.genderRatio, icon: 'scale', color: 'bg-purple-600', shadow: 'shadow-purple-100' },
]);
</script>

<template>
    <AppLayout title="Dashboard Admin Desa">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('admin.index')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <span class="hidden sm:inline">Kelola Admin</span>
                        <span class="sm:hidden">Admin</span>
                    </Link>
                    <Link :href="route('wilayah.kelompok.index')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        <span class="hidden sm:inline">Kelola Kelompok</span>
                        <span class="sm:hidden">Kel.</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 sm:space-y-8">
            <StatsGrid :cards="statCards" />

            <DashboardCharts
                age-title="Sebaran Usia (Lingkup Desa)"
                :age-distribution="ageDistribution"
                :marital-distribution="maritalDistribution"
            />

            <RecentJamaahTable title="Jamaah Terbaru (Desa)" :items="recentJamaah" scope-column="kelompok" />
        </div>
    </AppLayout>
</template>
