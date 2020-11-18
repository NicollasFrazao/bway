<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_produto';
    protected $primaryKey = 'cd_produto';

    public function setores()
    {
      return $this -> belongstoMany(Produto::class, 'setor_produto', 'cd_setor', 'cd_produto');
    }
}
