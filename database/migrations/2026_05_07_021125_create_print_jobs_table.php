<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('printer_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type'); // raw, pdf, label
            $table->longText('content');

            $table->enum('status', [
                'queued',
                'sent',
                'received',
                'processing',
                'printed',
                'acknowledged',
                'failed'
            ])->default('queued');

            $table->integer('attempts')->default(0);

            $table->string('idempotency_key')->nullable();

            $table->timestamp('received_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
