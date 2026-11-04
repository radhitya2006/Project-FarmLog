<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        return $this->user()?->role === UserRole::Admin
            && $target instanceof User
            && $target->role === UserRole::Farmer;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'name' => ['missing'],
            'email' => ['missing'],
            'password' => ['missing'],
            'password_confirmation' => ['missing'],
            'role' => ['missing'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.required' => 'Status akses wajib dipilih.',
            'is_active.boolean' => 'Status akses harus berupa aktif atau nonaktif.',
            'missing' => 'Identitas dan peran akun petani tidak dapat diubah melalui panel admin.',
        ];
    }
}
