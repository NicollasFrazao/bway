<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbLoginTable extends Migration
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
            'tb_login', 
            function (Blueprint $table) 
            {
                $table -> increments('cd_login') -> index();
                $table -> string('ds_login') -> index() -> unique();
                $table -> integer('cd_usuario') -> unsigned();
                
                $table -> timestamps();

                $table -> foreign('cd_usuario') 
                -> references('cd_usuario') 
                    -> on('tb_usuario')
                -> constrained()
                -> onDelete('cascade') 
                -> onUpdate('cascade');
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
        Schema::dropIfExists('tb_login');
    }
}
