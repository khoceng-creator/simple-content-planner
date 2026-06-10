<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\ContentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentPlan> */
class ContentPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'posting_date' => fake()->dateTimeBetween('first day of this month', 'last day of this month'),
            'posting_time' => fake()->randomElement(['09:00', '12:30', '18:30']),
            'type' => fake()->randomElement(ContentPlan::TYPES),
            'platforms' => ['instagram' => true, 'tiktok' => fake()->boolean()],
            'headline' => fake()->sentence(5),
            'detail_html' => '<p>'.fake()->sentence(12).'</p>',
            'note_html' => '<p>'.fake()->sentence(6).'</p>',
            'document_link' => null,
            'is_made' => fake()->boolean(),
        ];
    }
}
