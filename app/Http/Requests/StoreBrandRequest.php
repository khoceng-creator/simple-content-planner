<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama brand wajib diisi.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPG, PNG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
        ];
    }
}
