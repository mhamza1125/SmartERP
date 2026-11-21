<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The assets table holds both registers now: kind = 'asset' (Finance > Assets) or 'liability'
 * (Finance > Liabilities). An entry can move money: bank_id = the company account it went
 * through (0 = cash, NULL = no money moved) and transaction_id = the cash / bank transaction it
 * posted (shown in the cash book / bank ledger).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'kind')) {
                $table->string('kind', 10)->default('asset')->after('asset_id')->index();
            }
            if (! Schema::hasColumn('assets', 'bank_id')) {
                $table->unsignedBigInteger('bank_id')->nullable()->after('credit');
            }
            if (! Schema::hasColumn('assets', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('bank_id');
                $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('attachment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'transaction_id')) {
                $table->dropForeign(['transaction_id']);
                $table->dropColumn('transaction_id');
            }
            foreach (['bank_id', 'created_by'] as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('assets', 'kind')) {
                $table->dropIndex(['kind']);
                $table->dropColumn('kind');
            }
        });
    }
};
