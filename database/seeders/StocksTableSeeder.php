<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StocksTableSeeder extends Seeder
{
    public function run(): void
    {
        // STOCK ID = 0
        DB::table('stocks')->where('stock_id', 0)->exists()
            ? DB::table('stocks')->where('stock_id', 0)->update([
                'issue_id' => null,
                'ptc_id' => null,
                'current_stage_id' => null,
                'next_stage_id' => null,
                'is_ptc_master' => 0,
                'issue_for' => 0,
                // ⛔ stock_no NOT updated
                'order_id' => 0,
                'machine_id' => null,
                'table_name' => 'mprocess',
                'employee_id' => 0,
                'stock_type' => 2,
                'stock_date' => '2024-05-09',
                'stock_status' => 0,
                'description' => null,
                'created_by' => 1,
                'updated_at' => '2024-05-09 08:39:30',
            ])
            : DB::table('stocks')->insert([
                'stock_id' => 0,
                'stock_no' => 'I24000000',
                'table_name' => 'mprocess',
                'stock_type' => 2,
                'stock_date' => '2024-05-09',
                'stock_status' => 0,
                'created_by' => 1,
                'created_at' => '2024-04-26 14:05:16',
                'updated_at' => '2024-05-09 08:39:30',
            ]);

        // STOCK ID = 1
        DB::table('stocks')->where('stock_id', 1)->exists()
            ? DB::table('stocks')->where('stock_id', 1)->update([
                'issue_id' => null,
                'ptc_id' => null,
                'current_stage_id' => null,
                'next_stage_id' => null,
                'is_ptc_master' => 0,
                'issue_for' => 0,
                // ⛔ stock_no NOT updated
                'order_id' => 0,
                'machine_id' => null,
                'table_name' => 'openingStock',
                'employee_id' => 0,
                'stock_type' => 1,
                'stock_date' => '2024-05-09',
                'stock_status' => 0,
                'description' => null,
                'created_by' => 1,
                'updated_at' => '2024-05-09 08:39:30',
            ])
            : DB::table('stocks')->insert([
                'stock_id' => 1,
                'stock_no' => 'I23000000',
                'table_name' => 'openingStock',
                'stock_type' => 1,
                'stock_date' => '2024-05-09',
                'stock_status' => 0,
                'created_by' => 1,
                'created_at' => '2024-04-26 14:05:16',
                'updated_at' => '2024-05-09 08:39:30',
            ]);
    }
}
