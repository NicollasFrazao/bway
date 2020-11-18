<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_setor';
    protected $primaryKey = 'cd_setor';

    public function estabelecimento()
    {
        return $this -> belongsTo(Estabelecimento::class);
    }

    public function produtos()
    {
      return $this -> belongstoMany(Produto::class, 'setor_produto', 'cd_setor', 'cd_produto');
    }
}
