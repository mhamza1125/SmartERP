<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - stock_items.rejected_stage_id: on Rejection-stage lines (stage 105), the work stage the
 *   pieces were rejected at (Cutting, Printing...). NULL = not recorded (older lines).
 * - return_materials.stage_id / rejected_stage_id: the stage a purchased product was returned
 *   from when it is no longer at the stage it was bought in (NULL = the purchased stage).
 * - Stock Adjustments permissions (StockAdjustmentPolicy); only the Admin role gets them here.
 *
 * Older PTC rejection receipts are tagged by `php artisan stock:backfill-rejected-stage`.
 */
return new class extends Migration
{
    private array $actions = ['access', 'show', 'create', 'delete'];

    private function names(): array
    {
        return array_map(fn ($action) => "stock_adjustments_{$action}", $this->actions);
    }

    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->unsignedBigInteger('rejected_stage_id')->nullable()->after('stage_id');
        });

        Schema::table('return_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('stage_id')->nullable()->after('quantity');
            $table->unsignedBigInteger('rejected_stage_id')->nullable()->after('stage_id');
        });

        $adminRoleId = DB::table('roles')->where('id', 1)->value('id');
        foreach ($this->names() as $name) {
            if (! DB::table('permissions')->where('name', $name)->exists()) {
                DB::table('permissions')->insert(['name' => $name, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');
            if ($adminRoleId && ! DB::table('permission_role')->where('role_id', $adminRoleId)->where('permission_id', $permissionId)->exists()) {
                DB::table('permission_role')->insert(['role_id' => $adminRoleId, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->names())->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        Schema::table('return_materials', function (Blueprint $table) {
            $table->dropColumn(['stage_id', 'rejected_stage_id']);
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('rejected_stage_id');
        });
    }
};
