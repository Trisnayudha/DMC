<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsUserLogsTable extends Migration
{
    public function up()
    {
        Schema::create('cms_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Keep the audit trail when the CMS account is renamed or removed.
            $table->unsignedBigInteger('cms_user_id');
            $table->string('admin_name');
            $table->string('menu', 80);
            $table->string('action', 32);
            $table->string('route');
            $table->string('method', 12)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('outcome', 24)->nullable();
            $table->json('record_ids')->nullable();
            $table->json('fields')->nullable();
            $table->string('control', 80)->nullable();
            $table->uuid('page_key')->nullable()->unique();
            $table->unsignedBigInteger('page_log_id')->nullable();
            $table->dateTime('occurred_at');
            $table->index(['cms_user_id', 'menu', 'occurred_at']);
            $table->index(['menu', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cms_user_logs');
    }
}
