<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';

const exportOptions = [
    {
        title: 'Export Data Jamaah',
        description: 'Download data jamaah (CSV atau Excel)',
        icon: 'table',
        action: () => window.location.href = '/export/jamaah/excel',
        actionCSV: () => window.location.href = '/export/jamaah',
    },
    {
        title: 'Laporan Per Desa',
        description: 'Download laporan ringkasan per desa',
        icon: 'chart',
        action: () => window.location.href = '/export/desa',
    },
    {
        title: 'Statistik Lengkap',
        description: 'Download statistik lengkap dalam format PDF',
        icon: 'document',
        action: () => window.location.href = '/export/statistik',
    },
];
</script>

<template>
    <AppLayout title="Laporan">
        <template #header>
            Laporan & Export Data
        </template>

        <div class="max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card class="hover:shadow-md transition-shadow">
                    <div class="text-center py-4">
                        <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Export Data Jamaah</h3>
                        <p class="text-sm text-gray-500 mb-4">Download data jama'ah (CSV atau Excel)</p>
                        <div class="flex justify-center gap-2">
                            <Button variant="secondary" size="sm" @click="exportOptions[0].action">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Excel
                            </Button>
                            <Button variant="outline" size="sm" @click="exportOptions[0].actionCSV">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                CSV
                            </Button>
                        </div>
                    </div>
                </Card>
                <Card v-for="option in exportOptions.slice(1)" :key="option.title" class="hover:shadow-md transition-shadow cursor-pointer">
                    <div class="text-center py-4">
                        <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="option.icon === 'chart'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ option.title }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ option.description }}</p>
                        <Button variant="primary" size="sm" @click="option.action">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </Button>
                    </div>
                </Card>
            </div>

            <div class="mt-8 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Berhasil!</strong> Fitur export Excel sudah aktif menggunakan PhpSpreadsheet.
                            Anda dapat mengekspor data jama'ah dalam format CSV atau Excel.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
