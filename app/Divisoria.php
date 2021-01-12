<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Divisoria extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_divisoria';
    protected $primaryKey = 'cd_divisoria';

    public function estabelecimento()
    {
        return $this -> belongsTo(Estabelecimento::class);
    }
}
