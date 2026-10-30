<?php

namespace App\Http\Requests\Auth;

class RegisterRequest extends LoginRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [...parent::rules()['password'], 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 150 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
        ]);
    }
}
