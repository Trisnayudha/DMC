<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandPeriodColumnInSponsorBenefitUsage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE sponsor_benefit_usage MODIFY COLUMN period VARCHAR(9) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE sponsor_benefit_usage MODIFY COLUMN period VARCHAR(7) NULL');
    }
}
