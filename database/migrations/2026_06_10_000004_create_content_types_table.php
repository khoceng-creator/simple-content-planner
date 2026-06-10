<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('slug', 30);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->timestamps();

            $table->unique(['brand_id', 'slug']);
            $table->index(['brand_id', 'sort_order']);
        });

        $defaults = [
            ['name' => 'Carousel', 'slug' => 'carousel', 'sort_order' => 10],
            ['name' => 'Reels', 'slug' => 'reels', 'sort_order' => 20],
            ['name' => 'Single post', 'slug' => 'single', 'sort_order' => 30],
        ];
        $now = now();

        DB::table('brands')->orderBy('id')->each(function (object $brand) use ($defaults, $now): void {
            foreach ($defaults as $type) {
                DB::table('content_types')->insert([
                    'brand_id' => $brand->id,
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'is_default' => true,
                    'sort_order' => $type['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('content_plans')
                ->where('brand_id', $brand->id)
                ->whereNotIn('type', array_column($defaults, 'slug'))
                ->distinct()
                ->pluck('type')
                ->each(function (string $slug) use ($brand, $now): void {
                    DB::table('content_types')->insertOrIgnore([
                        'brand_id' => $brand->id,
                        'name' => Str::headline($slug),
                        'slug' => $slug,
                        'is_default' => false,
                        'sort_order' => 100,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_types');
    }
};
