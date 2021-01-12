<?php

namespace App\Http\Controllers;

use App\Estabelecimento;
use Illuminate\Http\Request;

class EstabelecimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Estabelecimento::get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.estabelecimento.cadastro');
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
    public function show($codigoEstabelecimento)
    {
        $estabelecimento = Estabelecimento::where('cd_estabelecimento', $codigoEstabelecimento)
        -> with
        (
            [
                'setores' => function($query)
                {
                },
                'divisorias' => function($query)
                {
                }
            ]
        );

        if (count($estabelecimento -> get()) > 0)
        {
            $estabelecimento = $estabelecimento -> first();

            //$estabelecimento -> ds_mapeamento = $this -> getMapeamento($estabelecimento -> cd_estabelecimento);
        }
        else
        {
            $estabelecimento = $estabelecimento -> first();
        }

        return 
        [
            'estabelecimento' => $estabelecimento
        ];
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

    public function getMapeamento($codigoEstabelecimento)
    {
        $path = storage_path()."\\app\\public\\estabelecimentos\\mapeamentos\\".$codigoEstabelecimento.'.json';
        $ds_mapeamento = json_decode(file_get_contents($path), false) -> ds_mapeamento;

        return $ds_mapeamento;
    }
}
