<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('number', 30)->unique();
            $table->string('status', 20)->default('draft');
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('quote_id')->nullable()->unique()->constrained('documents')->restrictOnDelete();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->char('currency', 3)->default('EUR');
            $table->json('client_snapshot');
            $table->json('company_snapshot');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'status', 'issue_date']);
            $table->index(['client_id', 'issue_date']);
        });

        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 255);
            $table->string('unit', 30);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
            $table->index(['document_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_lines');
        Schema::dropIfExists('documents');
    }
};
