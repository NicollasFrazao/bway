<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estabelecimento extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_estabelecimento';
    protected $primaryKey = 'cd_estabelecimento';

    public function setores()
    {
        return $this -> hasMany(Setor::class, 'cd_estabelecimento');
    }

    public function divisorias()
    {
        return $this -> hasMany(Divisoria::class, 'cd_estabelecimento');
    }
}
