<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorFollowupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Jejak follow-up renewal per sponsor — murni tabel baru, tidak menyentuh
        // tabel sponsors/sponsor_renewals yang sudah berisi data production.
        Schema::create('sponsor_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id')->index();
            $table->unsignedSmallInteger('renewal_year')->index();
            $table->date('followed_up_at');
            $table->string('channel', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_path');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsor_followups');
    }
}
