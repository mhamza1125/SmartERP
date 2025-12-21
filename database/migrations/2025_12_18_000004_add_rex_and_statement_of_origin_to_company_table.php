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
            // Add REX number after existing NTN field
            if (!Schema::hasColumn('company', 'rex_no')) {
                $table->string('rex_no')->nullable()->after('ntn')->comment('REX Number for commercial invoices');
            }

            // Add Statement of Origin after existing footer_text
            if (!Schema::hasColumn('company', 'statement_of_origin')) {
                $table->text('statement_of_origin')->nullable()->after('footer_text')->comment('Statement of Origin for commercial invoices');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            if (Schema::hasColumn('company', 'rex_no')) {
                $table->dropColumn('rex_no');
            }

            if (Schema::hasColumn('company', 'statement_of_origin')) {
                $table->dropColumn('statement_of_origin');
            }
        });
    }
};


