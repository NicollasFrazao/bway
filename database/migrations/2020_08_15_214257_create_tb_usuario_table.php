<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Schema::create('dev', function (Blueprint $table) {
            $table -> increments('id') -> index();
            $table -> string('name', 100) -> index();
            $table -> string('email', 100) -> index() -> unique();
            $table -> integer('lang_id') -> unsigned();
          
            $table -> timestamps();
          
            $table -> foreign('lang_id') -> references('id') -> on('lang');
        });
        */

        Schema::create
        (
            'tb_usuario', 
            function (Blueprint $table) 
            {
                $table -> increments('cd_usuario') -> index();
                $table -> string('nm_usuario', 100) -> index();
                $table -> string('nm_email', 100) -> index() -> unique();
                $table -> string('cd_senha', 256);
                $table -> boolean('ic_confirmado') -> default(false);

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
        Schema::dropIfExists('tb_usuario');
    }
}
