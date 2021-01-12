<?php

namespace App\Http\Controllers;

use App\Usuario;
use App\Produto;
use Illuminate\Http\Request;

class ListaComprasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Usuario $usuario)
    {
        return $usuario -> listaCompras() -> orderBy('nm_produto') -> get();
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
    public function update(Usuario $usuario, Request $request)
    {
        if (!$request -> has('codigoProduto'))
        {
            return
            [
                'ic_sucesso' => false
            ];
        }

        $codigoProduto = $request -> codigoProduto;
        $indicadorProduto = $request -> indicadorProduto;

        $ic_sucesso = true;
        $ds_mensagem = '';

        if ($indicadorProduto == 1)
        {
            $usuario -> listaCompras() -> attach($codigoProduto);
            $ds_mensagem = 'Produto adicionado a sua lista de compras com sucesso!';
        }
        else if ($indicadorProduto == 0)
        {
            $usuario -> listaCompras() -> detach($codigoProduto);
            $ds_mensagem = 'Produto excluído da sua lista de compras com sucesso!';
        }
        else
        {
            $ic_sucesso = false;
            $ds_mensagem = 'Ocorreu um erro durante o processo! Erro: Indicador não definido.';
        }

        return
        [
            'ic_sucesso' => $ic_sucesso,            
            'ds_mensagem' => $ds_mensagem
        ];
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Usuario $usuario)
    {
        $codigoProdutos = $usuario -> listaCompras() -> get() -> map
        (
            function ($item, $key)
            {
                return $item -> cd_produto;
            }
        ) -> all();
        
        foreach ($codigoProdutos as $codigoProduto)
        {
            $usuario -> listaCompras() -> detach($codigoProduto);
        }

        return
        [
            'ic_sucesso' => true,
            'ds_mensagem' => 'Lista de compras limpa com sucesso!'
        ];
    }
}
