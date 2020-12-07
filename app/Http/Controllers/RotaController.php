<?php

namespace App\Http\Controllers;

use App\Estabelecimento;
use App\Setor;
use Illuminate\Http\Request;

class RotaController extends Controller
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
    public static function show(Estabelecimento $estabelecimento, Setor $origem, Setor $destino)
    {
        $pathWindows = storage_path().'\\app\\public\\estabelecimentos\\rotas\\'.$estabelecimento -> cd_estabelecimento.'-'.$origem -> cd_setor.'-'.$destino -> cd_setor.'.json';
        $pathLinux = storage_path().'/app/public/estabelecimentos/rotas/'.$estabelecimento -> cd_estabelecimento.'-'.$origem -> cd_setor.'-'.$destino -> cd_setor.'.json';
        
        if (file_exists($pathWindows))
        {
            $rota = json_decode(file_get_contents($pathWindows), true); 
            return $rota;
        }
        else if (file_exists($pathLinux))
        {
            $rota = json_decode(file_get_contents($pathLinux), true); 
            return $rota;
        }
        else
        {
            return [];
        }
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
