<script setup>
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';

defineProps({
    ageTitle: {
        type: String,
        default: 'Sebaran Usia',
    },
    ageDistribution: Object,
    maritalDistribution: Object,
});
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-900 mb-6">{{ ageTitle }}</h3>
            <div class="h-[300px]">
                <BarChart :data="ageDistribution" />
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Status Pernikahan</h3>
            <div class="h-[300px] flex items-center justify-center">
                <PieChart :data="maritalDistribution" />
            </div>
        </div>
    </div>
</template>
