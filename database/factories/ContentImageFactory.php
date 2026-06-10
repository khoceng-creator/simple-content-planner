<?php

namespace Database\Factories;

use App\Models\ContentImage;
use App\Models\ContentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentImage> */
class ContentImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_plan_id' => ContentPlan::factory(),
            'file_path' => 'brands/1/contents/1/'.fake()->uuid().'.jpg',
            'original_name' => 'content.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'sort_order' => 0,
        ];
    }
}
