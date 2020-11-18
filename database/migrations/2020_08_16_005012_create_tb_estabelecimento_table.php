<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbEstabelecimentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create
        (
            'tb_estabelecimento', 
            function (Blueprint $table) 
            {
                $table -> increments('cd_estabelecimento') -> index();
                $table -> string('nm_estabelecimento', 100) -> index() -> nullable();
                $table -> string('ds_coordenadas', 100) -> nullable();
                $table -> float('vl_largura_metros', 8, 2) -> unsigned() -> nullable();
                $table -> float('vl_comprimento_metros', 8, 2) -> unsigned() -> nullable();
                $table -> integer('vl_largura_pixels') -> unsigned() -> nullable();
                $table -> integer('vl_comprimento_pixels') -> unsigned() -> nullable();
                $table -> json('ds_mapeamento') -> nullable();
                $table -> string('nm_email', 100) -> index() -> unique() -> nullable();
                $table -> string('cd_senha', 256) -> nullable();
                $table -> boolean('ic_confirmado') -> default(false) -> nullable();

                $table -> timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_estabelecimento');
    }
}
