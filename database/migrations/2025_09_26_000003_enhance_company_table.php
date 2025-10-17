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
        Schema::table('company', function (Blueprint $table) {
            $table->string('ntn')->nullable()->after('ceo')->comment('National Tax Number');
            $table->text('address')->nullable()->after('ntn')->comment('Company address');
            $table->string('city')->nullable()->after('address')->comment('City');
            $table->string('country')->nullable()->after('city')->comment('Country');
            $table->string('logo_path')->nullable()->after('logo')->comment('Path to company logo file');
            $table->text('footer_text')->nullable()->after('logo_path')->comment('Footer text for documents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['ntn', 'address', 'city', 'country', 'logo_path', 'footer_text']);
        });
    }
};
