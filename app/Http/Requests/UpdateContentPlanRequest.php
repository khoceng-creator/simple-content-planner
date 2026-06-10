<?php

namespace App\Http\Requests;

class UpdateContentPlanRequest extends StoreContentPlanRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('contentPlan')) ?? false;
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'retain_images' => ['nullable', 'array'],
            'retain_images.*' => ['integer'],
        ];
    }
}
