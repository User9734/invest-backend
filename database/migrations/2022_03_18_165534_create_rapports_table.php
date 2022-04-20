<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRapportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->integer('produits_vendus');
            $table->integer('cout')->default(0);
            $table->unsignedBigInteger('achat_id');
            $table->foreign('achat_id')->references('id')->on('achats');
            $table->unsignedBigInteger('vente_id');
            $table->foreign('vente_id')->references('id')->on('ventes');
            $table->softDeletes();
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
        Schema::dropIfExists('rapports');
    }
}
