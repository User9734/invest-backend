<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->integer('cout_acquisition')->default(0);
            $table->integer('cout_vente')->default(0);
            $table->integer('gain')->default(0);
            $table->integer('nb_products')->default(0);
            $table->integer('nb_jours')->default(0);
            $table->boolean('publie')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->softDeletes();
            $table->foreign('type_id')->references('id')->on('types');
            $table->string('libelle');
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
        Schema::dropIfExists('packages');
    }
}
