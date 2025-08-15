<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SchoolAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin and superintendent can add schools
        return Auth::check() && (Auth::user()->hasRole('ROLE_ADMIN') || Auth::user()->hasRole('ROLE_SUPERINTENDENT'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:schools,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'subscription_id' => ['nullable', 'uuid', 'exists:subscription_users,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama sekolah wajib diisi.',
            'name.string' => 'Nama sekolah harus berupa teks.',
            'name.max' => 'Nama sekolah tidak boleh lebih dari 255 karakter.',
            'code.required' => 'Kode sekolah wajib diisi.',
            'code.string' => 'Kode sekolah harus berupa teks.',
            'code.max' => 'Kode sekolah tidak boleh lebih dari 255 karakter.',
            'code.unique' => 'Kode sekolah sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'website.url' => 'Format website tidak valid.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Format logo yang diizinkan: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
            'subscription_id.uuid' => 'ID langganan harus berupa UUID yang valid.',
            'subscription_id.exists' => 'ID langganan tidak ditemukan.',
        ];
    }
}
