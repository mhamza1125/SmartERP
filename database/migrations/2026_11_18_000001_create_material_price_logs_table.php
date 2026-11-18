<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Price changes of a material made outside purchases: the price entered when the
     * material is created (source 'opening') and later changes of price / vendor on the
     * Edit Material form (source 'edit'). Purchase and processing prices are read from
     * purchase_items, so they are not copied here.
     *
     * Backfill: a material with no purchase line still carries the price it was created
     * with, so it gets an 'opening' row. A material that was purchased has had that
     * price overwritten by the purchase and it cannot be recovered.
     */
    public function up(): void
    {
        Schema::create('material_price_logs', function (Blueprint $table) {
            $table->id('material_price_log_id');
            $table->unsignedBigInteger('material_id')->index();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('price', 15, 3)->default(0);
            $table->string('source', 20); // opening | edit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        $purchased = DB::table('purchase_items')->whereNotNull('material_id')->where('material_id', '!=', 0)
            ->distinct()->pluck('material_id')->all();

        DB::table('materials')->whereNotIn('material_id', $purchased)->orderBy('material_id')
            ->get(['material_id', 'vendor_id', 'cprice', 'created_by', 'created_at'])
            ->chunk(500)
            ->each(function ($chunk) {
                DB::table('material_price_logs')->insert($chunk->map(fn ($m) => [
                    'material_id' => $m->material_id,
                    'vendor_id' => $m->vendor_id,
                    'price' => $m->cprice ?? 0,
                    'source' => 'opening',
                    'created_by' => $m->created_by,
                    'created_at' => $m->created_at,
                    'updated_at' => $m->created_at,
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_price_logs');
    }
};
