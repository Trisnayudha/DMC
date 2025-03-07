<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostmarkCallbacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postmark_callbacks', function (Blueprint $table) {
            $table->id();

            // Field kunci
            $table->string('record_type')->nullable();   // Dari "RecordType": Delivery, Bounce, SpamComplaint, dsb
            $table->string('message_id')->nullable()->index(); // ID unik per pesan

            // Beberapa field opsional (sering muncul di payload)
            $table->string('recipient')->nullable();
            $table->string('tag')->nullable();

            // Jika ingin menyimpan custom metadata
            $table->json('metadata')->nullable();

            // Simpan seluruh payload mentah dalam JSON untuk referensi
            $table->json('payload');

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
        Schema::dropIfExists('postmark_callbacks');
    }
}
