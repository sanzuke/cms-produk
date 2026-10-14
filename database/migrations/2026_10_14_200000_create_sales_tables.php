<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->nullable()->unique();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_company')->nullable();
            $table->string('client_email');
            $table->string('client_phone', 30)->nullable();
            $table->string('title');
            // [{description, qty, unit, unit_price}]
            $table->json('items')->nullable();
            $table->unsignedBigInteger('mobilization')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->nullable()->unique();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('client_company')->nullable();
            $table->string('client_email');
            $table->string('client_phone', 30)->nullable();
            $table->string('title');
            $table->string('site')->nullable();
            $table->date('scheduled_start')->nullable()->index();
            $table->date('scheduled_end')->nullable();
            $table->string('status', 20)->default('awaiting_dp')->index();
            // [{label, done}]
            $table->json('checklist')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('total')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->nullable()->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // dp | final | full
            $table->unsignedBigInteger('amount');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('unpaid')->index(); // unpaid | proof_submitted | paid | cancelled
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_ref')->nullable();
            $table->string('proof_path')->nullable(); // disk local (privat)
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('access_token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            // [{path, name, size}] pada disk local (privat)
            $table->json('files')->nullable();
            // daftar path yang ditandai favorit oleh klien
            $table->json('favorites')->nullable();
            $table->boolean('require_full_payment')->default(true);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });

        Schema::create('order_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('author', 10); // client | staff
            $table->string('author_name');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_comments');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('quotations');
    }
};
