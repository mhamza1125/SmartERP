<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $names = [
        'packing_lists_access', 'packing_lists_show', 'packing_lists_create', 'packing_lists_edit', 'packing_lists_delete',
    ];

    /**
     * Packing lists are authorized with DeliveryPolicy (deliveries_* permissions), so the
     * separate packing_lists_* permissions (and their role grants) no longer control anything.
     */
    public function up(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->names)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }
    }

    /**
     * Restores the permissions only; the role grants they had are not restored.
     */
    public function down(): void
    {
        foreach ($this->names as $name) {
            DB::table('permissions')->updateOrInsert(['name' => $name], ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }
    }
};
