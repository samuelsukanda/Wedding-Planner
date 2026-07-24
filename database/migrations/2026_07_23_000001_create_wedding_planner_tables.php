<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Weddings
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('bride_name');
            $table->string('groom_name');
            $table->date('wedding_date');
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Checklists
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('category');
            $table->date('deadline')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->enum('status', ['Todo', 'Progress', 'Done'])->default('Todo');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        // 3. Checklist Attachments
        Schema::create('checklist_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        // 4. Budget Categories
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // 5. Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('item_name')->nullable();
            $table->decimal('planned_budget', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->string('invoice')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Vendor Categories
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 8. Vendors
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('category');
            $table->string('contact')->nullable();
            $table->text('address')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->string('package')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->text('review')->nullable();
            $table->string('photo')->nullable();
            $table->enum('booking_status', ['Not Contacted', 'Negotiating', 'Booked', 'Cancelled', 'Completed'])->default('Not Contacted');
            $table->timestamps();
        });

        // 9. Vendor Contracts
        Schema::create('vendor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('contract_number');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('dp_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['Draft', 'Waiting', 'Signed', 'Completed', 'Cancelled'])->default('Draft');
            $table->string('contract_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Vendor Payments
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('status', ['Belum Bayar', 'DP', 'Cicilan', 'Lunas'])->default('Belum Bayar');
            $table->string('invoice_file')->nullable();
            $table->date('reminder_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. Guest Categories
        Schema::create('guest_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 12. Guests
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->default('Friend');
            $table->enum('attendance_status', ['Pending', 'Attend', 'Decline'])->default('Pending');
            $table->integer('guest_count')->default(1);
            $table->timestamps();
        });

        // 13. Moodboards
        Schema::create('moodboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('link_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 14. Rundown Events
        Schema::create('rundown_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('time');
            $table->string('activity');
            $table->string('pic');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 15. Gifts
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('giver_name');
            $table->enum('gift_type', ['Cash', 'Barang'])->default('Cash');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_thank_you_sent')->default(false);
            $table->timestamps();
        });

        // 16. Reports
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('report_type');
            $table->timestamp('generated_at');
            $table->json('summary_data')->nullable();
            $table->timestamps();
        });

        // 17. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->boolean('is_read')->default(false);
            $table->date('reminder_date')->nullable();
            $table->timestamps();
        });

        // 18. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('gifts');
        Schema::dropIfExists('rundown_events');
        Schema::dropIfExists('moodboards');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('guest_categories');
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('vendor_contracts');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('vendor_categories');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('budget_categories');
        Schema::dropIfExists('checklist_attachments');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('weddings');
    }
};
