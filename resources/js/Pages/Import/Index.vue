<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const page = usePage();
const form = useForm({
    file: null,
});

const submit = () => {
    form.post(route('import.store'), {
        onSuccess: () => {
            form.reset('file');
        },
    });
};

const downloadTemplate = () => {
    window.location.href = route('import.template');
};
</script>

<template>
    <AppLayout title="Import Data Jamaah">
        <template #header>
            Import Data Jamaah
        </template>

        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Upload File Import</h2>
                    <p class="text-gray-600">Upload file CSV atau Excel (.xlsx) yang berisi data jamaah untuk diimport ke dalam sistem.</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-blue-900 mb-1">Format File</h3>
                            <p class="text-sm text-blue-700 mb-3">Download template di bawah untuk format yang benar. Sistem mendukung delimiter <code class="bg-blue-100 px-1 rounded">;</code> atau <code class="bg-blue-100 px-1 rounded">,</code> (auto-detect).</p>
                            <div class="text-sm text-blue-700 space-y-1">
                                <p class="font-medium">Kolom yang didukung:</p>
                                <code class="block bg-blue-100 text-blue-800 text-xs px-3 py-2 rounded font-mono leading-relaxed">
                                    DESA;KELOMPOK;NAMA LENGKAP;TEMPAT LAHIR;TANGGAL LAHIR;JENIS KELAMIN;PAKET;STATUS PERNIKAHAN;KATAGORI SODAQOH;DAPUKAN;PEKERJAAN;DEWAN GURU;PENDIDIKAN TERAKHIR;KBM YANG DIMINATI;NO TELEPON
                                </code>
                                <p class="text-xs text-blue-600 mt-1">* NO DESA dan NO KELOMPOK tidak diperlukan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File CSV / Excel</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".csv, .xlsx, .xls" @change="form.file = $event.target.files[0]" />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV or Excel files, up to 10MB</p>
                            </div>
                        </div>
                        <p v-if="form.file" class="mt-2 text-sm text-gray-600">File terpilih: <span class="font-medium">{{ form.file.name }}</span></p>
                        <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <Link :href="route('jamaah.index')" class="px-6 py-2.5 text-gray-700 hover:text-gray-900 font-medium">
                            Kembali
                        </Link>
                        <div class="flex items-center gap-3">
                            <div class="relative group">
                                <button type="button" class="px-4 py-2.5 bg-green-100 text-green-700 rounded-xl hover:bg-green-200 font-medium transition-colors inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Template
                                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                    <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-100">
                                        CSV Format
                                    </div>
                                    <a :href="route('import.template', { delimiter: 'semicolon' })" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        CSV (;)
                                    </a>
                                    <a :href="route('import.template', { delimiter: 'comma' })" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        CSV (,)
                                    </a>
                                    <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-t border-b border-gray-100">
                                        Excel Format
                                    </div>
                                    <a :href="route('import.template.excel')" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-b-xl">
                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Excel (.xlsx)
                                    </a>
                                </div>
                            </div>
                            <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="form.processing">Memproses...</span>
                                <span v-else>Import Data</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div v-if="$page.props.flash?.success" class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-green-900 font-medium">{{ $page.props.flash.success }}</p>
                    </div>
                    <div v-if="$page.props.result" class="mt-2 ml-7 text-sm text-green-800">
                        <p>• Total diproses: {{ $page.props.result.total_processed }}</p>
                        <p>• Berhasil: {{ $page.props.result.success_count }}</p>
                        <p v-if="$page.props.result.skipped_count > 0" class="text-yellow-600">• Dilewati (kosong): {{ $page.props.result.skipped_count }}</p>
                        <p v-if="$page.props.result.error_count > 0">• Gagal: {{ $page.props.result.error_count }}</p>
                        <div v-if="$page.props.result.errors && $page.props.result.errors.length > 0" class="mt-2 p-2 bg-red-100 rounded-lg">
                            <p class="font-medium text-red-700 mb-1">Detail Error:</p>
                            <ul class="list-disc list-inside text-red-600 text-xs max-h-32 overflow-y-auto">
                                <li v-for="(error, index) in $page.props.result.errors.slice(0, 10)" :key="index">{{ error }}</li>
                                <li v-if="$page.props.result.errors.length > 10">... dan {{ $page.props.result.errors.length - 10 }} error lainnya</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div v-if="$page.props.flash?.error" class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-red-900 font-medium">{{ $page.props.flash.error }}</p>
                    </div>
                    <div v-if="$page.props.result && $page.props.result.errors && $page.props.result.errors.length > 0" class="mt-2 ml-7 text-sm text-red-800">
                        <p class="font-medium mb-1">Detail Error:</p>
                        <ul class="list-disc list-inside text-red-600 text-xs max-h-32 overflow-y-auto">
                            <li v-for="(error, index) in $page.props.result.errors.slice(0, 10)" :key="index">{{ error }}</li>
                            <li v-if="$page.props.result.errors.length > 10">... dan {{ $page.props.result.errors.length - 10 }} error lainnya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>