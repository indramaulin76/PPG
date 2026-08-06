<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Card from '@/Components/UI/Card.vue';

const props = defineProps({
    jamaah: Object,
    desas: Array,
    kelompoks: Array,
    dropdowns: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    kelompok_id: props.jamaah.kelompok_id || '',
    nama_lengkap: props.jamaah.nama_lengkap || '',
    tempat_lahir: props.jamaah.tempat_lahir || '',
    tgl_lahir: props.jamaah.tgl_lahir || '',
    jenis_kelamin: props.jamaah.jenis_kelamin || '',
    golongan_darah: props.jamaah.golongan_darah || '',
    kelas_generus: props.jamaah.kelas_generus || '',
    status_pernikahan: props.jamaah.status_pernikahan || '',
    kategori_sodaqoh: props.jamaah.kategori_sodaqoh || '',
    dapukan: props.jamaah.dapukan || '',
    pekerjaan: props.jamaah.pekerjaan || '',
    status_mubaligh: props.jamaah.status_mubaligh || '',
    pendidikan_terakhir: props.jamaah.pendidikan_terakhir || '',
    minat_kbm: props.jamaah.minat_kbm || '',
    pendidikan_aktivitas: props.jamaah.pendidikan_aktivitas || '',
    no_telepon: props.jamaah.no_telepon || '',
    role_dlm_keluarga: props.jamaah.role_dlm_keluarga || '',
});

// Find initial desa from kelompok
const findDesaId = () => {
    if (props.jamaah.kelompok_id) {
        const kelompok = props.kelompoks.find(k => k.id == props.jamaah.kelompok_id);
        return kelompok?.desa_id || '';
    }
    return '';
};

const selectedDesa = ref(findDesaId());

const filteredKelompoks = computed(() => {
    if (!selectedDesa.value) return [];
    return props.kelompoks.filter(k => k.desa_id == selectedDesa.value);
});

watch(selectedDesa, (newVal, oldVal) => {
    if (oldVal !== '') {
        form.kelompok_id = '';
    }
});

const submit = () => {
    form.put(route('jamaah.update', props.jamaah.id));
};

const genderOptions = [
    { value: 'L', label: 'Laki-laki' },
    { value: 'P', label: 'Perempuan' },
];

const roleOptions = [
    { value: 'KEPALA', label: 'Kepala Keluarga' },
    { value: 'ISTRI', label: 'Istri' },
    { value: 'ANAK', label: 'Anak' },
    { value: 'LAINNYA', label: 'Lainnya' },
];

const formatDropdownOptions = (items) => {
    if (!items) return [];
    return items.map(item => ({ value: item, label: item }));
};
</script>

<template>
    <AppLayout title="Edit Jamaah">
        <template #header>
            Edit Data Jamaah: {{ jamaah.nama_lengkap }}
        </template>

        <form @submit.prevent="submit" class="space-y-6 max-w-5xl">
            <!-- Section 1: Identitas Diri -->
            <Card>
                <template #header>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Identitas Diri</h3>
                    </div>
                </template>
                <div class="space-y-4">
                    <!-- Wilayah -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="selectedDesa"
                            label="Desa"
                            :options="desas"
                            option-value="id"
                            option-label="nama_desa"
                            placeholder="Pilih Desa"
                            required
                        />
                        <Select
                            v-model="form.kelompok_id"
                            label="Kelompok"
                            :options="filteredKelompoks"
                            option-value="id"
                            option-label="nama_kelompok"
                            placeholder="Pilih Kelompok"
                            :error="form.errors.kelompok_id"
                            required
                        />
                    </div>

                    <!-- Nama & Tempat Lahir -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input
                            v-model="form.nama_lengkap"
                            label="Nama Lengkap"
                            placeholder="Masukkan nama lengkap"
                            :error="form.errors.nama_lengkap"
                            required
                        />
                        <div class="grid grid-cols-2 gap-2">
                            <Input
                                v-model="form.tempat_lahir"
                                label="Tempat Lahir"
                                placeholder="Jakarta"
                                :error="form.errors.tempat_lahir"
                            />
                            <Input
                                v-model="form.tgl_lahir"
                                type="date"
                                label="Tgl Lahir"
                                :error="form.errors.tgl_lahir"
                            />
                        </div>
                    </div>

                    <!-- Gender, Golongan Darah & Status Pernikahan -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Select
                            v-model="form.jenis_kelamin"
                            label="Jenis Kelamin"
                            :options="genderOptions"
                            placeholder="Pilih L/P"
                            :error="form.errors.jenis_kelamin"
                            required
                        />
                        <Input
                            v-model="form.golongan_darah"
                            label="Golongan Darah"
                            placeholder="A / B / AB / O"
                            :error="form.errors.golongan_darah"
                        />
                        <Select
                            v-model="form.status_pernikahan"
                            label="Status Pernikahan"
                            :options="formatDropdownOptions(dropdowns.status_pernikahan)"
                            placeholder="Pilih Status"
                            :error="form.errors.status_pernikahan"
                        />
                    </div>

                    <!-- Role & Telepon -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="form.role_dlm_keluarga"
                            label="Peran dalam Keluarga"
                            :options="roleOptions"
                            placeholder="Pilih Peran"
                            :error="form.errors.role_dlm_keluarga"
                        />
                        <Input
                            v-model="form.no_telepon"
                            label="No. Telepon"
                            placeholder="08xxxxxxxxxx"
                            :error="form.errors.no_telepon"
                        />
                    </div>
                </div>
            </Card>

            <!-- Section 2: Data Keagamaan -->
            <Card>
                <template #header>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Data Keagamaan</h3>
                    </div>
                </template>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Select
                        v-model="form.kelas_generus"
                        label="Kelas Generus"
                        :options="formatDropdownOptions(dropdowns.kelas_generus)"
                        placeholder="Pilih Kelas"
                        :error="form.errors.kelas_generus"
                    />
                    <Select
                        v-model="form.status_mubaligh"
                        label="Dewan Guru"
                        :options="formatDropdownOptions(dropdowns.status_mubaligh)"
                        placeholder="MT / MS / Asisten"
                        :error="form.errors.status_mubaligh"
                    />
                    <Select
                        v-model="form.kategori_sodaqoh"
                        label="Kategori Sodaqoh"
                        :options="formatDropdownOptions(dropdowns.kategori_sodaqoh)"
                        placeholder="Pilih Kategori"
                        :error="form.errors.kategori_sodaqoh"
                    />
                </div>
            </Card>

            <!-- Section 3: Profesi & Pendidikan -->
            <Card>
                <template #header>
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Profesi & Pendidikan</h3>
                    </div>
                </template>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="form.pendidikan_terakhir"
                            label="Pendidikan Terakhir"
                            :options="formatDropdownOptions(dropdowns.pendidikan)"
                            placeholder="SD / SMP / SMA / S1"
                            :error="form.errors.pendidikan_terakhir"
                        />
                        <Input
                            v-model="form.pekerjaan"
                            label="Pekerjaan"
                            placeholder="Contoh: Wiraswasta, PNS, Guru"
                            :error="form.errors.pekerjaan"
                        />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="form.dapukan"
                            label="Dapukan"
                            :options="formatDropdownOptions(dropdowns.dapukan)"
                            placeholder="Pilih Dapukan"
                            :error="form.errors.dapukan"
                        />
                        <Select
                            v-model="form.minat_kbm"
                            label="KBM yang Diminati"
                            :options="formatDropdownOptions(dropdowns.minat_kbm)"
                            placeholder="Pilih KBM"
                            :error="form.errors.minat_kbm"
                        />
                    </div>
                    <Input
                        v-model="form.pendidikan_aktivitas"
                        label="Catatan"
                        placeholder="Untuk data lama"
                        :error="form.errors.pendidikan_aktivitas"
                    />
                </div>
            </Card>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pb-6">
                <Link :href="route('jamaah.index')">
                    <Button type="button" variant="secondary">Batal</Button>
                </Link>
                <Button type="submit" variant="primary" :loading="form.processing">
                    Simpan Perubahan
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
