<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');

            $table->enum('type', ['image', 'video'])->default('image');

            $table->string('file_path')->nullable();  // /storage/programs/gallery/xxx.jpg OR /storage/programs/videos/xxx.mp4
            $table->string('video_url')->nullable();  // youtube/vimeo embed link

            $table->string('caption')->nullable();
            $table->unsignedInteger('sort')->default(1);

            $table->timestamps();

            $table->index(['program_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_media');
    }
}
