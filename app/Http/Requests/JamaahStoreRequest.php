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
            'tgl_lahir' => 'nullable|date|before:today|after:1900-01-01',
            'jenis_kelamin' => 'required|in:L,P',
            'status_pernikahan' => 'nullable|in:BELUM,MENIKAH,JANDA,DUDA',
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
            'pendidikan_aktivitas' => $this->pendidikan_aktivitas ? ucwords(strtolower(trim($this->pendidikan_aktivitas))) : null,
            'no_telepon' => $this->no_telepon ? preg_replace('/[^0-9\+\-\(\)]/', '', $this->no_telepon) : null,
            'jenis_kelamin' => $this->jenis_kelamin ? strtoupper(trim($this->jenis_kelamin)) : null,
            'status_pernikahan' => $this->status_pernikahan ? strtoupper(trim($this->status_pernikahan)) : null,
            'role_dlm_keluarga' => $this->role_dlm_keluarga ? strtoupper(trim($this->role_dlm_keluarga)) : null,
        ]);
    }
}