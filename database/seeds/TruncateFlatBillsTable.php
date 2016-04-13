<?php

use Illuminate\Database\Seeder;
use ApartmentApi\Models\FlatBill;
use ApartmentApi\Models\FlatBillItem;

class TruncateFlatBillsTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FlatBill::truncate();
        FlatBillItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
