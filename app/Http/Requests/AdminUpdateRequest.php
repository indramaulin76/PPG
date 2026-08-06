<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'username' => ['sometimes', 'string', Rule::unique('users')->ignore($this->route('admin'))],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed', 'regex:'.User::PASSWORD_COMPLEXITY_REGEX],
            'is_active' => 'sometimes|boolean',
            'desa_id' => 'sometimes|nullable|exists:desas,id',
            'kelompok_id' => 'sometimes|nullable|exists:kelompoks,id',
        ];
    }
}
