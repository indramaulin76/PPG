<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import DataBadge from '@/Components/Data/DataBadge.vue';

const props = defineProps({
    jamaah: Object,
});

const infoItems = [
    { label: 'Nama Lengkap', key: 'nama_lengkap' },
    { label: 'Tempat Lahir', key: 'tempat_lahir' },
    { label: 'Tanggal Lahir', key: 'tgl_lahir' },
    { label: 'Umur', key: 'age', suffix: ' tahun' },
    { label: 'Jenis Kelamin', key: 'jenis_kelamin', badge: true },
    { label: 'Kategori Usia', key: 'kategori_usia', badge: true },
    { label: 'Status Pernikahan', key: 'status_pernikahan', badge: true },
    { label: 'Peran dalam Keluarga', key: 'role_dlm_keluarga' },
    { label: 'No. Telepon', key: 'no_telepon' },
    { label: 'Desa', key: 'desa' },
    { label: 'Kelompok', key: 'kelompok' },
    { label: '---', key: 'divider1', divider: true },
    { label: 'Kelas Generus', key: 'kelas_generus' },
    { label: 'Status Mubaligh', key: 'status_mubaligh' },
    { label: 'Kategori Ekonomi', key: 'kategori_sodaqoh' },
    { label: '---', key: 'divider2', divider: true },
    { label: 'Pendidikan Terakhir', key: 'pendidikan_terakhir' },
    { label: 'Pekerjaan', key: 'pekerjaan' },
    { label: 'Dapukan Organisasi', key: 'dapukan' },
    { label: 'Minat KBM', key: 'minat_kbm' },
    { label: 'Catatan', key: 'pendidikan_aktivitas' },
];
</script>

<template>
    <AppLayout title="Detail Jamaah">
        <template #header>
            Detail Jamaah
        </template>

        <div class="max-w-3xl">
            <Card>
                <template #header>
                    <div class="flex items-center">
                        <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-bold text-xl mr-4">
                            {{ jamaah.nama_lengkap.charAt(0) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ jamaah.nama_lengkap }}</h2>
                            <p class="text-sm text-gray-500">{{ jamaah.desa }} - {{ jamaah.kelompok }}</p>
                        </div>
                    </div>
                </template>

                <dl class="divide-y divide-gray-100">
                    <div v-for="item in infoItems" :key="item.key" class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ item.label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            <template v-if="item.badge && jamaah[item.key]">
                                <DataBadge :status="jamaah[item.key]" />
                            </template>
                            <template v-else>
                                {{ jamaah[item.key] || '-' }}{{ item.suffix || '' }}
                            </template>
                        </dd>
                    </div>
                </dl>

                <template #footer>
                    <div class="flex items-center justify-between">
                        <Link :href="route('jamaah.index')">
                            <Button variant="secondary">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Kembali
                            </Button>
                        </Link>
                        <Link :href="route('jamaah.edit', jamaah.id)">
                            <Button variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Data
                            </Button>
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
