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
        Schema::table('doctor_availability', function (Blueprint $table) {
            $table->date('date')->after('doctor_id');
            $table->index(['doctor_id', 'date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_availability', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'date', 'start_time']);
            $table->dropColumn('date');
        });
    }
};
