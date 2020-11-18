<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_carrinho';
    protected $primaryKey = 'cd_carrinho';

    public function usuario()
    {
        return $this -> belongsTo(Usuario::class);
    }

    public function estabelecimento()
    {
        return $this -> belongsTo(Estabelecimento::class);
    }

    public function produtos()
    {
      return $this -> belongstoMany(Produto::class, 'carrinho_produto', 'cd_carrinho', 'cd_produto');
    }
}
