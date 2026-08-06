<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminStoreRequest extends FormRequest
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
        $allowedRoles = $this->user()->allowedRolesToManage();

        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:'.User::PASSWORD_COMPLEXITY_REGEX],
            'role' => ['required', Rule::in($allowedRoles)],
            'desa_id' => 'required_if:role,'.User::ROLE_ADMIN_DESA.'|required_if:role,'.User::ROLE_ADMIN_KELOMPOK.'|nullable|exists:desas,id',
            'kelompok_id' => 'required_if:role,'.User::ROLE_ADMIN_KELOMPOK.'|nullable|exists:kelompoks,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
