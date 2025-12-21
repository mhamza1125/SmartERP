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
        Schema::table('deliveries', function (Blueprint $table) {
            // Keep fi_no on deliveries but drop rex_no / ntn / so_origin
            if (Schema::hasColumn('deliveries', 'rex_no')) {
                $table->dropColumn('rex_no');
            }

            if (Schema::hasColumn('deliveries', 'ntn')) {
                $table->dropColumn('ntn');
            }

            if (Schema::hasColumn('deliveries', 'so_origin')) {
                $table->dropColumn('so_origin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Restore the dropped columns with their original definitions
            if (!Schema::hasColumn('deliveries', 'rex_no')) {
                $table->string('rex_no')->nullable()->after('fi_no')->comment('REX Number for commercial invoice');
            }

            if (!Schema::hasColumn('deliveries', 'ntn')) {
                $table->string('ntn')->nullable()->after('rex_no')->comment('NTN for commercial invoice');
            }

            if (!Schema::hasColumn('deliveries', 'so_origin')) {
                $table->text('so_origin')->nullable()->after('ntn')->comment('Statement of Origin for commercial invoice');
            }
        });
    }
};


