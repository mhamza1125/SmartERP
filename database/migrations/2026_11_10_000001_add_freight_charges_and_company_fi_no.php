<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * deliveries.freight_charges: extra amount charged to the customer for a delivery, in the
     * order's currency (same as order item prices). Added to the delivery's ledger debit.
     * company.fi_no: default FI number for commercial invoices (a delivery's own FI No overrides it).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('deliveries', 'freight_charges')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->decimal('freight_charges', 15, 2)->default(0)->after('fi_no');
            });
        }

        if (! Schema::hasColumn('company', 'fi_no')) {
            Schema::table('company', function (Blueprint $table) {
                $table->string('fi_no')->nullable()->after('rex_no');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('deliveries', 'freight_charges')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->dropColumn('freight_charges');
            });
        }

        if (Schema::hasColumn('company', 'fi_no')) {
            Schema::table('company', function (Blueprint $table) {
                $table->dropColumn('fi_no');
            });
        }
    }
};
