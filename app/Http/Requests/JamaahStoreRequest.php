<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JamaahStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'kelompok_id' => 'required|exists:kelompoks,id',
            'keluarga_id' => 'nullable|exists:keluargas,id',
            'nama_lengkap' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\-\'\.]+$/',
            'tempat_lahir' => 'nullable|string|max:100',
            'tgl_lahir' => 'nullable|date|before:today|after:1900-01-01',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_generus' => 'nullable|string|max:50',
            'status_pernikahan' => 'nullable|in:BELUM,MENIKAH,JANDA,DUDA',
            'kategori_sodaqoh' => 'nullable|string|max:50',
            'dapukan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'status_mubaligh' => 'nullable|string|max:50',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'minat_kbm' => 'nullable|string|max:100',
            'pendidikan_aktivitas' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s\.\-]+$/',
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9\+\-\s\(\)]+$/',
            'role_dlm_keluarga' => 'nullable|in:KEPALA,ISTRI,ANAK,LAINNYA',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.regex' => 'Nama lengkap hanya boleh mengandung huruf, spasi, titik, dan tanda hubung.',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tgl_lahir.after' => 'Tanggal lahir harus setelah tahun 1900.',
            'pendidikan_aktivitas.regex' => 'Pendidikan/aktivitas hanya boleh mengandung huruf, angka, spasi, titik, dan tanda hubung.',
            'no_telepon.regex' => 'Nomor telepon hanya boleh mengandung angka dan karakter telepon valid.',
            'kelompok_id.exists' => 'Kelompok yang dipilih tidak valid.',
            'keluarga_id.exists' => 'Keluarga yang dipilih tidak valid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize input data
        $this->merge([
            'nama_lengkap' => $this->nama_lengkap ? ucwords(strtolower(trim($this->nama_lengkap))) : null,
            'tempat_lahir' => $this->tempat_lahir ? ucwords(strtolower(trim($this->tempat_lahir))) : null,
            'kelas_generus' => $this->kelas_generus ? strtoupper(trim($this->kelas_generus)) : null,
            'kategori_sodaqoh' => $this->kategori_sodaqoh ? strtoupper(trim($this->kategori_sodaqoh)) : null,
            'dapukan' => $this->dapukan ? ucwords(strtolower(trim($this->dapukan))) : null,
            'pekerjaan' => $this->pekerjaan ? ucwords(strtolower(trim($this->pekerjaan))) : null,
            'status_mubaligh' => $this->status_mubaligh ? strtoupper(trim($this->status_mubaligh)) : null,
            'pendidikan_terakhir' => $this->pendidikan_terakhir ? strtoupper(trim($this->pendidikan_terakhir)) : null,
            'minat_kbm' => $this->minat_kbm ? ucwords(strtolower(trim($this->minat_kbm))) : null,
            'pendidikan_aktivitas' => $this->pendidikan_aktivitas ? ucwords(strtolower(trim($this->pendidikan_aktivitas))) : null,
            'no_telepon' => $this->no_telepon ? preg_replace('/[^0-9\+\-\(\)]/', '', $this->no_telepon) : null,
            'jenis_kelamin' => $this->jenis_kelamin ? strtoupper(trim($this->jenis_kelamin)) : null,
            'status_pernikahan' => $this->status_pernikahan ? strtoupper(trim($this->status_pernikahan)) : null,
            'role_dlm_keluarga' => $this->role_dlm_keluarga ? strtoupper(trim($this->role_dlm_keluarga)) : null,
        ]);
    }
}