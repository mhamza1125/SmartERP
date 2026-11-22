<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * party_prices: the current price of one item for one vendor (buying) or customer (selling).
     * An item is a material (material_id) or a product size at a stage (product_type_id + stage_id);
     * unused keys are 0 so the unique index works. Set by hand on the material / product page
     * and updated by purchases / orders when they use another price.
     *
     * party_price_logs: every change of a party price (old -> new, what changed it).
     *
     * product_cost_logs: every change of a product wage (general or employee / contractor
     * specific), so a person's raises can be traced.
     *
     * Existing data is filled by `php artisan prices:backfill` (not here).
     */
    public function up(): void
    {
        Schema::create('party_prices', function (Blueprint $table) {
            $table->id('party_price_id');
            $table->string('party_type', 10); // vendor | customer
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('material_id')->default(0);
            $table->unsignedBigInteger('product_type_id')->default(0);
            $table->unsignedBigInteger('stage_id')->default(0);
            $table->decimal('price', 15, 3)->default(0);
            $table->string('source', 20); // manual | purchase | order | backfill
            $table->unsignedBigInteger('doc_id')->nullable(); // purchase_id / order_id
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['party_type', 'party_id', 'material_id', 'product_type_id', 'stage_id'], 'party_prices_item_unique');
            $table->index(['material_id']);
            $table->index(['product_type_id']);
        });

        Schema::create('party_price_logs', function (Blueprint $table) {
            $table->id('party_price_log_id');
            $table->string('party_type', 10);
            $table->unsignedBigInteger('party_id');
            $table->unsignedBigInteger('material_id')->default(0);
            $table->unsignedBigInteger('product_type_id')->default(0);
            $table->unsignedBigInteger('stage_id')->default(0);
            $table->decimal('old_price', 15, 3)->nullable(); // NULL = first price
            $table->decimal('price', 15, 3)->nullable(); // NULL = price removed
            $table->string('source', 20);
            $table->unsignedBigInteger('doc_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'party_id']);
            $table->index(['material_id']);
            $table->index(['product_type_id']);
        });

        Schema::create('product_cost_logs', function (Blueprint $table) {
            $table->id('product_cost_log_id');
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('head_id');
            // same collation as product_costs.table_name, which it is compared with
            $table->string('table_name', 20)->collation('utf8mb4_general_ci'); // general | employee | vendor
            $table->unsignedBigInteger('table_id')->default(0);
            $table->double('old_amount')->nullable(); // NULL = wage added
            $table->double('amount')->nullable(); // NULL = wage removed
            $table->string('action', 10); // added | changed | removed | opening
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'table_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cost_logs');
        Schema::dropIfExists('party_price_logs');
        Schema::dropIfExists('party_prices');
    }
};
