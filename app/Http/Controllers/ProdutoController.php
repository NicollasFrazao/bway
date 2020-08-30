<?php

namespace App\Http\Controllers;

use App\Produto;
use App\Usuario;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $produtos = Produto::get();

        return $produtos;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($nomeProdutoPesquisar, Request $request)
    {
        $produtosPesquisados = Produto::where('nm_produto', 'like', '%'.$nomeProdutoPesquisar.'%')
                        -> orderBy('nm_produto', 'asc') -> get();
        
        if ($request -> has('codigoUsuario'))
        {
            $codigoUsuario = $request -> codigoUsuario;            
            $listaCompras = Usuario::where('cd_usuario', $codigoUsuario) -> first() -> listaCompras() -> get();
            
            $produtosPesquisados = $produtosPesquisados -> whereNotIn
            (
                'cd_produto', 
                $listaCompras -> map
                (
                    function ($item, $key)
                    {
                        return $item -> cd_produto;
                    }
                ) -> all()
            ) -> values() -> toArray();
        }

        return $produtosPesquisados;
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
