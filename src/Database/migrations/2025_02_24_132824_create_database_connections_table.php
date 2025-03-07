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
        Schema::connection('database_manager')->create('database_connections', function (Blueprint $table) {
            $table->id();
            $table->string('module_name')->unique();
            $table->string('connection_name');
            $table->string('host');
            $table->string('database');
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('database_manager')->dropIfExists('database_connections');
    }
};
