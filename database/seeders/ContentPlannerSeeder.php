<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ContentPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ContentPlannerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => env('CONTENT_PLANNER_ADMIN_EMAIL') ?: 'admin@imm.local'],
            [
                'name' => env('CONTENT_PLANNER_ADMIN_NAME') ?: 'IMM Local Admin',
                'password' => Hash::make(env('CONTENT_PLANNER_ADMIN_PASSWORD') ?: 'password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $monthStart = now('Asia/Jakarta')->startOfMonth();
        $samples = [
            ['Invitationery', 'Product Launch Invitation', 'carousel', ['instagram' => true, 'tiktok' => false], 2, '09:00', true],
            ['Invitto', 'Behind the Scene Produksi', 'reels', ['instagram' => true, 'tiktok' => true], 5, '18:30', false],
            ['Printfy Id', 'Promo Payday', 'single', ['instagram' => false, 'tiktok' => true], 9, '12:30', false],
        ];

        foreach ($samples as [$brandName, $headline, $type, $platforms, $dayOffset, $time, $made]) {
            $brand = Brand::query()->firstOrCreate(
                ['user_id' => $user->id, 'slug' => Str::slug($brandName)],
                ['name' => $brandName],
            );

            ContentPlan::query()->firstOrCreate(
                ['brand_id' => $brand->id, 'headline' => $headline],
                [
                    'posting_date' => $monthStart->copy()->addDays($dayOffset),
                    'posting_time' => $time,
                    'type' => $type,
                    'platforms' => $platforms,
                    'detail_html' => '<p>Konsep utama untuk '.$headline.'.</p><ul><li>Hook</li><li>Value</li><li>Call to action</li></ul>',
                    'note_html' => '<p>Siapkan aset dan approval sebelum jadwal tayang.</p>',
                    'is_made' => $made,
                ],
            );
        }
    }
}
