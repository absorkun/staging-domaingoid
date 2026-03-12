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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('zone', 50);
            $table->boolean('dns_sec')->nullable()->default(null);
            $table->string('domain_name_server')->nullable()->default(null);
            $table->string('ip_address')->nullable()->default(null);
            $table->integer('status_code')->nullable()->default(null);

            $table->index(['name', 'zone']);
            $table->unique(['name', 'zone']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
