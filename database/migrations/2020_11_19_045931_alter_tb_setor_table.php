<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTbSetorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table
        (
            'tb_setor', 
            function (Blueprint $table) 
            {
                $table -> boolean('ic_entrada') -> default(false) -> after('cd_estabelecimento');
                $table -> string('ds_beacon', 50) -> nullable() -> after('ic_entrada');
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
        //
    }
}
