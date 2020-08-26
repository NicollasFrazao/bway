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
    'produtos', 
    'ProdutoController',
    [
        'names' => 
        [
            'index' => 'produtos'
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
            'store' => 'login.efetuar',
            'destroy' => 'login.logout'
        ]
    ]
);

Route::get
(
    'logout',
    'LoginController@destroy'
) -> name('logout');

Route::resource
(
    'usuarios', 
    'UsuarioController',
    [
        'names' => 
        [
            'store' => 'usuarios.cadastrar'
        ]
    ]
);

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
        if ($request -> session() -> has('codigoUsuario'))
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
    'sessao',
    function(Request $request)
    {
        return $request -> session() -> all();
    }
);
