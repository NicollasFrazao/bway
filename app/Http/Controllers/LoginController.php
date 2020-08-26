<?php

namespace App\Http\Controllers;

use App\Usuario;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $resquest)
    {
        if ($resquest -> session() -> has('codigoUsuario'))
        {
            return redirect() -> route('home');
        }
        else
        {
            return view('login');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //return view('login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $usuarioLogado = Usuario::where('nm_email', $request -> emailUsuario)
                                -> where ('cd_senha', $request -> senhaUsuario) -> get();

        if ($usuarioLogado -> count() == 1)
        {
            $usuarioLogado = $usuarioLogado[0];
            $request -> session() -> put('codigoUsuario', $usuarioLogado -> cd_usuario);
            
            return 
            [
                'ic_sucesso' => true,
                'ds_usuario' => $usuarioLogado
            ];
        }

        return
        [
            'ic_sucesso' => false,
            'ds_mensagem' => 'Não foi possível realizar o login! E-mail e/ou senha estão inválidos.'
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id = '')
    {
        session() -> forget('codigoUsuario');

        return redirect() -> route('login.index');
    }
}
