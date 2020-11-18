<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource
(
    'usuario.carrinho', 
    'CarrinhoController',
    [
        'names' => 
        [
            'show' => 'usuario.carrinho'
        ]
    ]
)
-> parameters
(
    [
        'usuario' => 'usuario',
        'carrinho' => 'estabelecimento'
    ]
);

Route::resource
(
    'estabelecimentos', 
    'EstabelecimentoController',
    [
        'names' => 
        [
            'index' => 'estabelecimentos',
            'create' => 'estabelecimentos.cadastrar',
            'show' => 'estabelecimentos.exibir'
        ]
    ]
);

Route::resource
(
    'usuario.listaCompras', 
    'ListaComprasController',
    [
        'names' => 
        [
            'show' => 'usuario.listaCompras',
            'update' => 'listaCompras.alterar',
            'destroy' => 'listaCompras.limpar'
        ]
    ]
)
-> parameters
(
    [
        'listaCompras' => 'usuario'
    ]
);

Route::resource
(
    'usuario', 
    'UsuarioController',
    [
        'names' => 
        [
            'store' => 'usuario.cadastrar'
        ]
    ]
);

Route::resource
(
    'produtos', 
    'ProdutoController',
    [
        'names' => 
        [
            'index' => 'produtos',
            'show' => 'produtos.pesquisar'
        ]
    ]
);

Route::resource
(
    'login', 
    'LoginController',
    [
        'names' =>
        [
            'index' => 'login.index',
            'store' => 'login.efetuar'
        ]
    ]
);

Route::post
(
    'logout',
    'LoginController@destroy'
) -> name('logout');

//----------------------------------------

Route::get
(
    '/', 
    function () 
    {
        return view('index');
        //return redirect() -> route('login');
    }
) -> name('index');

Route::get
(
    'home', 
    function (Request $request) 
    {
        if ($request -> session() -> has('usuario'))
        {
            return view('home');
        }
        else
        {
            return redirect() -> route('login.index');
        }
    }
) -> name('home');

Route::get
(
    'admin/estabelecimentos', 
    function (Request $request) 
    {
        /*
        if ($request -> session() -> has('usuario'))
        {
            return view('home');
        }
        */
        
        return view('admin.estabelecimento.index');
    }
) -> name('estabelecimentos.admin');