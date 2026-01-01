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
        Schema::table('employees', function (Blueprint $table) {
            // Personal Information
            $table->enum('marital_status', ['married', 'single', 'divorced', 'widower'])->nullable()->after('description');
            $table->integer('siblings_count')->nullable()->after('marital_status');
            
            // JSON columns for array data
            $table->json('children_details')->nullable()->after('siblings_count');
            $table->json('education')->nullable()->after('children_details');
            $table->json('employment_history')->nullable()->after('education');
            
            // Skills
            $table->text('additional_skills')->nullable()->after('employment_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'marital_status',
                'siblings_count',
                'children_details',
                'education',
                'employment_history',
                'additional_skills',
            ]);
        });
    }
};

