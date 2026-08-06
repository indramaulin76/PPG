<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatsGrid from '@/Components/Dashboard/StatsGrid.vue';
import DashboardCharts from '@/Components/Dashboard/DashboardCharts.vue';
import RecentJamaahTable from '@/Components/Dashboard/RecentJamaahTable.vue';
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
    { name: 'Total Desa', value: formatNumber(props.stats.totalDesa), icon: 'map', color: 'bg-orange-500', shadow: 'shadow-orange-100' },
    { name: 'Total Kelompok', value: formatNumber(props.stats.totalKelompok), icon: 'collection', color: 'bg-indigo-600', shadow: 'shadow-indigo-100' },
    { name: 'Rasio L/P', value: props.stats.genderRatio, icon: 'scale', color: 'bg-purple-600', shadow: 'shadow-purple-100' },
]);
</script>

<template>
    <AppLayout title="Dashboard Super Admin">
        <template #header>
            Dashboard
        </template>

        <div class="space-y-6 sm:space-y-8">
            <StatsGrid :cards="statCards" />

            <DashboardCharts
                age-title="Sebaran Usia Global"
                :age-distribution="ageDistribution"
                :marital-distribution="maritalDistribution"
            />

            <RecentJamaahTable title="Jamaah Terbaru" :items="recentJamaah" scope-column="wilayah" />
        </div>
    </AppLayout>
</template>
