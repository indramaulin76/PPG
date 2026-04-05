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
            <Card title="📋 Identitas Diri">
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

                    <!-- Gender & Status Pernikahan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="form.jenis_kelamin"
                            label="Jenis Kelamin"
                            :options="genderOptions"
                            placeholder="Pilih L/P"
                            :error="form.errors.jenis_kelamin"
                            required
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
            <Card title="🕌 Data Keagamaan">
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
            <Card title="🎓 Profesi & Pendidikan">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select
                            v-model="form.pendidikan_terakhir"
                            label="Pendidikan Terakhir"
                            :options="formatDropdownOptions(dropdowns.pendidikan)"
                            placeholder="SD / SMP / SMA / S1"
                            :error="form.errors.pendidikan_terakhir"
                        />
                        <Select
                            v-model="form.pekerjaan"
                            label="Pekerjaan"
                            :options="formatDropdownOptions(dropdowns.pekerjaan)"
                            placeholder="Pilih Pekerjaan"
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
                        label="Pendidikan/Aktivitas (Legacy)"
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
