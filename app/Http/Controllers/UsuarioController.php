<?php

namespace App\Http\Controllers;

use App\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('usuario.cadastro');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function store(Request $request)
    {
        $nomeUsuario = $request -> nomeUsuario;
        $emailUsuario = $request -> emailUsuario;
        $senhaUsuario = $request -> senhaUsuario;

        $usuarios = Usuario::where('nm_email', $emailUsuario) -> get();

        if ($usuarios -> count() == 0)
        {
            $novoUsuario = new Usuario();
            
            $novoUsuario -> nm_usuario = $nomeUsuario;
            $novoUsuario -> nm_email = $emailUsuario;
            $novoUsuario -> cd_senha = $senhaUsuario;

            $novoUsuario -> save();

            return
            [
                'ic_sucesso' => true,
                'ds_mensagem' => 'Usuário criado com sucesso!\nObrigado por escolher o b-Way!'
                //'ds_usuario' => $novoUsuario
            ];
        }
        else
        {
            return 
            [
                'ic_sucesso' => false,
                'ds_mensagem' => 'O e-mail desejado já está sendo utilizado por outro usuário!\nInsira e outro e tente novamente.'
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
