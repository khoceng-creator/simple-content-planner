<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('brand')) ?? false;
    }

    public function rules(): array
    {
        return (new StoreBrandRequest)->rules();
    }

    public function messages(): array
    {
        return (new StoreBrandRequest)->messages();
    }
}
