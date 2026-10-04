<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recipient_type');
            $table->uuid('recipient_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->string('status');
            $table->uuid('requested_by');
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
