<?php

namespace App\Http\Controllers;

use App\Usuario;
use App\Estabelecimento;
use App\Produto;
use App\Carrinho;
use App\Setor;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
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
    public function show(Usuario $usuario, Estabelecimento $estabelecimento)
    {
        $listaCompras = $usuario -> listaCompras() -> get();
        $codigosProdutoListaCompras = $listaCompras -> pluck('cd_produto') -> all();

        $setores = $estabelecimento -> setores() -> with('produtos') -> get();
        
        $possivelCarrinho = $setores -> map
        (
            function ($setor) use ($codigosProdutoListaCompras)
            {
                $produtos = $setor -> produtos 
                -> map
                (
                    function ($produto) use ($codigosProdutoListaCompras)
                    {
                        if (in_array($produto -> cd_produto, $codigosProdutoListaCompras))
                        {
                            return $produto;
                        }
                    }
                )
                -> filter
                (
                    function ($produto)
                    {
                        return $produto != null;
                    }
                ) -> values() -> sortBy('nm_produto') -> values();

                if (count($produtos) > 0)
                {
                    return 
                    [
                        'cd_setor' => $setor -> cd_setor,
                        'nm_setor' => $setor -> nm_setor,
                        'cd_estabelecimento' => $setor -> cd_estabelecimento, 
                        'produtos' => $produtos
                    ];
                }
            }
        )
        -> filter
        (
            function ($setor)
            {
                return $setor != null;
            }
        ) -> values();

        $possivelCarrinho = $possivelCarrinho -> sortBy('nm_setor') -> values();
        
        $carrinho = Carrinho::where('cd_usuario', $usuario -> cd_usuario)
        -> where('cd_estabelecimento', $estabelecimento -> cd_estabelecimento);

        if (count($carrinho -> get()) > 0)
        {
            $carrinho = $carrinho -> first();
        }
        else
        {
            $carrinho = new Carrinho();

            $carrinho -> cd_usuario = $usuario -> cd_usuario;
            $carrinho -> cd_estabelecimento = $estabelecimento -> cd_estabelecimento;

            $carrinho -> save();
        }

        $possivelCarrinho -> each
        (
            function ($setor) use ($carrinho, $codigosProdutoCarrinho)
            {
                $codigoSetor = $setor['cd_setor'];

                $setor['produtos'] -> each
                (
                    function ($produto) use ($codigoSetor, $carrinho, $codigosProdutoCarrinho)
                    {
                        $codigoProduto = $produto['cd_produto'];
                        
                        if (!in_array($codigoProduto, $carrinho -> produtos() -> get() -> pluck('pivot.cd_produto') -> all()))
                        {
                            $carrinho -> produtos() -> attach($codigoProduto, ['cd_setor' => $codigoSetor]);
                        }
                    }
                );
            }
        );

        $carrinho -> produtos() -> each
        (
            function ($produto) use ($carrinho, $codigosProdutoListaCompras)
            {
                $codigoProduto = $produto -> cd_produto;

                if (!in_array($codigoProduto, $codigosProdutoListaCompras))
                {
                    $carrinho -> produtos() -> detach($codigoProduto);
                }
            }
        );

        $carrinho = $carrinho -> produtos() -> get() -> sortBy('pivot.cd_setor');

        $retorno = collect
        (
            [
            ]
        );

        $carrinho -> each
        (
            function ($produto) use ($retorno)
            {
                $codigoSetor = $produto -> pivot -> cd_setor;

                if (!in_array($codigoSetor, $retorno -> pluck('cd_setor') -> all()))
                {
                    $retorno -> push
                    (
                        collect
                        (
                            [
                                'cd_setor' => $produto -> pivot -> cd_setor,
                                'nm_setor' => Setor::where('cd_setor', $codigoSetor) -> first() -> nm_setor,
                                'produtos' => collect([])
                            ]
                        )
                    );
                }

                $retorno -> each
                (
                    function ($setor, $index) use ($produto, $codigoSetor, $retorno)
                    {
                        if ($setor['cd_setor'] == $codigoSetor)
                        {
                            $retorno[$index]['produtos'] -> push
                            (
                                collect
                                (
                                    [
                                        'cd_produto' => $produto -> cd_produto,
                                        'nm_produto' => $produto -> nm_produto,
                                        'ic_adquirido' => $produto -> pivot -> ic_adquirido
                                    ]
                                )
                            );
                        }
                    }
                );
            }
        );

        return $retorno -> sortBy('nm_setor') -> values();
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
