<?php

namespace Modules\DatabaseManager\Database\Migrations;
 

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
        Schema::connection('database_manager')->create('seeders', function (Blueprint $table) {
            $table->id();
            $table->string('module', 255);
            $table->string('seeder_name', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('database_manager')->dropIfExists('seeders');
    }
};