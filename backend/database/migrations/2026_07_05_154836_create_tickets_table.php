<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_number', 12)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title', 150);
            $table->text('description');
            $table->string('status', 20)->default('pending');
            $table->string('priority', 10)->default('medium');
            $table->geography('location', subtype: 'point', srid: 4326);
            $table->string('location_address')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category_id');
            $table->index('assigned_to');
            $table->index('created_at');
            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
