<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->date('posting_date');
            $table->time('posting_time')->nullable();
            $table->string('type', 30);
            $table->json('platforms');
            $table->string('headline');
            $table->longText('detail_html')->nullable();
            $table->longText('note_html')->nullable();
            $table->string('document_link', 2048)->nullable();
            $table->boolean('is_made')->default(false);
            $table->timestamps();

            $table->index('brand_id');
            $table->index('posting_date');
            $table->index(['brand_id', 'posting_date']);
            $table->index(['brand_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_plans');
    }
};
