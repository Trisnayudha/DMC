<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('event_questions', function (Blueprint $table) {
            $table->id();
            $table->string('event_slug');
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->text('question');
            $table->timestamps();
            $table->index(['event_slug', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_questions');
    }
};
