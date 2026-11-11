<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detailed payroll:
     *   payrolls        one per month: the period settings the salaries were calculated with
     *   payroll_items   one per employee per payroll: the full salary breakdown and the
     *                   transactions it posted (salary payment, advance deductions)
     *   transactions.advance_type   'temporary' | 'fixed' on employee Give / Receive Loan entries
     *   company         payroll settings (overtime / late multipliers, working hours per day)
     */
    public function up(): void
    {
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id('payroll_id');
                $table->string('month', 7)->unique(); // Y-m
                $table->decimal('total_working_days', 5, 2);
                $table->decimal('hours_per_day', 5, 2);
                $table->decimal('overtime_multiplier', 5, 2);
                $table->decimal('late_multiplier', 5, 2);
                $table->date('payment_date');
                $table->unsignedBigInteger('bank_id')->default(0); // 0 = cash
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payroll_items')) {
            Schema::create('payroll_items', function (Blueprint $table) {
                $table->id('payroll_item_id');
                $table->unsignedBigInteger('payroll_id');
                $table->unsignedBigInteger('employee_id');
                $table->string('designation')->nullable();
                $table->decimal('gross_salary', 15, 2)->default(0);
                $table->decimal('total_working_days', 5, 2)->default(0);
                $table->decimal('actual_working_days', 5, 2)->default(0);
                $table->decimal('salary_for_days', 15, 2)->default(0);
                $table->decimal('overtime_hours', 8, 2)->default(0);
                $table->decimal('overtime_rate', 15, 2)->default(0);
                $table->decimal('overtime_amount', 15, 2)->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->decimal('late_deduction', 15, 2)->default(0);
                $table->decimal('temp_advance', 15, 2)->default(0);
                $table->decimal('fixed_advance', 15, 2)->default(0);
                $table->decimal('fixed_advance_deduction', 15, 2)->default(0);
                $table->decimal('remaining_fixed_advance', 15, 2)->default(0);
                $table->decimal('net_salary', 15, 2)->default(0);
                $table->unsignedBigInteger('salary_transaction_id')->nullable();
                $table->unsignedBigInteger('temp_deduction_transaction_id')->nullable();
                $table->unsignedBigInteger('fixed_deduction_transaction_id')->nullable();
                $table->timestamps();

                $table->foreign('payroll_id')->references('payroll_id')->on('payrolls')->cascadeOnDelete();
                $table->unique(['payroll_id', 'employee_id']);
            });
        }

        if (! Schema::hasColumn('transactions', 'advance_type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('advance_type', 10)->nullable()->after('transaction_type');
            });
        }

        Schema::table('company', function (Blueprint $table) {
            if (! Schema::hasColumn('company', 'overtime_multiplier')) {
                $table->decimal('overtime_multiplier', 5, 2)->default(2)->after('statement_of_origin');
            }
            if (! Schema::hasColumn('company', 'late_multiplier')) {
                $table->decimal('late_multiplier', 5, 2)->default(1)->after('overtime_multiplier');
            }
            if (! Schema::hasColumn('company', 'work_hours_per_day')) {
                $table->decimal('work_hours_per_day', 5, 2)->default(8)->after('late_multiplier');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');

        if (Schema::hasColumn('transactions', 'advance_type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('advance_type');
            });
        }

        Schema::table('company', function (Blueprint $table) {
            foreach (['overtime_multiplier', 'late_multiplier', 'work_hours_per_day'] as $column) {
                if (Schema::hasColumn('company', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
