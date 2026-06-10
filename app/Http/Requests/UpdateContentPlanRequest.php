<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

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
            'retain_images.*' => [
                'integer',
                'distinct',
                Rule::exists('content_images', 'id')
                    ->where('content_plan_id', $this->route('contentPlan')->getKey()),
            ],
        ];
    }
}
