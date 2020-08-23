<?php

use Illuminate\Support\Facades\Route;

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

Route::resource('produtos', 'ProdutoController');
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
    '/home', 
    function () 
    {
        return view('home');
    }
) -> name('home');
