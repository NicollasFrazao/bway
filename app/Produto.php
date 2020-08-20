<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $connection = 'bway';
    protected $table = 'tb_produto';
    protected $primaryKey = 'cd_produto';
    protected $fillable = 
    [
      'nm_produto'
    ];
}
