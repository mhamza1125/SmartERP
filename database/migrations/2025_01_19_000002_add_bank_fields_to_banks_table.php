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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('account')->comment('International Bank Account Number');
            $table->text('address')->nullable()->after('iban')->comment('Bank address');
            $table->string('branch_code')->nullable()->after('address')->comment('Bank branch code');
            $table->string('swift_code')->nullable()->after('branch_code')->comment('SWIFT/BIC code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['iban', 'address', 'branch_code', 'swift_code']);
        });
    }
};
