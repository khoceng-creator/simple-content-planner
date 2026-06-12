<?php

namespace App\Http\Requests;

use App\Models\Brand;
use App\Models\ContentPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreContentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [ContentPlan::class, $this->route('brand')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'posting_date' => ['required', 'date'],
            'posting_time' => ['nullable', 'date_format:H:i'],
            'type' => ['required', 'string', 'max:30'],
            'new_type' => ['nullable', 'required_if:type,__new', 'string', 'max:60'],
            'platforms' => ['required', 'array'],
            'platforms.instagram' => ['required', 'boolean'],
            'platforms.tiktok' => ['required', 'boolean'],
            'headline' => ['required', 'string', 'max:255'],
            'detail_html' => ['nullable', 'string'],
            'note_html' => ['nullable', 'string'],
            'document_link' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->boolean('platforms.instagram') && ! $this->boolean('platforms.tiktok')) {
                $validator->errors()->add('platforms', 'Pilih setidaknya satu platform.');
            }

            $type = (string) $this->input('type');
            $brand = $this->contentBrand();
            if ($type !== '__new' && $brand && ! $brand->contentTypes()->where('slug', $type)->exists()) {
                $validator->errors()->add('type', 'Tipe konten tidak tersedia untuk brand ini.');
            }

            $totalImages = count($this->input('retain_images', []))
                + count($this->file('images', []));
            if ($totalImages > 12) {
                $validator->errors()->add('images', 'Total media lama dan baru maksimal 12 gambar.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'posting_date.required' => 'Tanggal posting wajib diisi.',
            'posting_time.date_format' => 'Jam posting harus menggunakan format HH:MM.',
            'new_type.required_if' => 'Nama tipe konten baru wajib diisi.',
            'new_type.max' => 'Nama tipe konten maksimal 60 karakter.',
            'headline.required' => 'Headline wajib diisi.',
            'document_link.url' => 'Link dokumen harus berupa URL yang valid.',
            'images.max' => 'Maksimal 12 gambar per konten.',
            'images.*.image' => 'Setiap lampiran harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 5 MB.',
            'images.*.dimensions' => 'Dimensi setiap gambar maksimal 6000 × 6000 piksel.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $platforms = $this->input('platforms', []);
        $this->merge([
            'type' => trim((string) $this->input('type')),
            'new_type' => trim((string) $this->input('new_type')),
            'platforms' => [
                'instagram' => filter_var($platforms['instagram'] ?? false, FILTER_VALIDATE_BOOL),
                'tiktok' => filter_var($platforms['tiktok'] ?? false, FILTER_VALIDATE_BOOL),
            ],
        ]);
    }

    private function contentBrand(): ?Brand
    {
        $brand = $this->route('brand');
        if ($brand instanceof Brand) {
            return $brand;
        }

        $contentPlan = $this->route('contentPlan');

        return $contentPlan instanceof ContentPlan ? $contentPlan->brand : null;
    }
}
