<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->bigInteger('id')->unique();
            $table->string('sha');
            $table->string('ref');
            $table->string('task');
            $table->text('payload')->nullable();
            $table->string('environment');
            $table->text('description')->nullable();
            $table->text('creator');
            $table->string('statuses_url', 400);
            $table->string('repository_url', 400);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deployments');
    }
}
