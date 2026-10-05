<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 2)->default('FR');
            $table->string('registration_number', 40)->nullable();
            $table->string('vat_number', 40)->nullable();
            $table->string('iban', 34)->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(20);
            $table->unsignedSmallInteger('quote_validity_days')->default(30);
            $table->unsignedSmallInteger('invoice_due_days')->default(30);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 40)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('unit', 30)->default('unité');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('next_number')->default(1);
            $table->unique(['type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('company_profiles');
    }
};
