<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_usuario';
    protected $primaryKey = 'cd_usuario';
    /*
    protected $fillable = 
    [
      'nm_usuario',
      'nm_email',
      'cd_senha'
    ];
    */

    public function listaCompras()
    {
      return $this -> belongstoMany(Produto::class, 'tb_lista_compras', 'cd_usuario', 'cd_produto');
    }
}
