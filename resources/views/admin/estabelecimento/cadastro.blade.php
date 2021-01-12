@extends('templates.app')

@section('title', 'Cadastrar Estabelecimento')

@section('header')
    @include('templates.header', ['tituloHeader' => 'Cadastar Estabelecimento'])
@endsection

@section('onload')
    //showListaCompras();
@endsection

@section('main')
    <style>
        .tela
        {
            display: inline-block;
        }
    </style>
    <div class="content horizontal-center">
        <div class="row h-100">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 horizontal-center h-100">
                <div class="row tela h-100" ng-controller="Estabelecimento">
                    <div class="col-sm-12 h-100">
                        <div class="row h-100">
                            <div class="col-sm-12 horizontal-center h-100" style="padding: 0;">
                                <div class="row">
                                    <div class="col-sm-12 h-100"> 
                                        <div style="">
                                            <div class="row" style="text-align: center">
                                                <div class="col-12 col-sm-12" style="padding: 0;">
                                                    <br/>
                                                    <button type="button" class="btn b-Way" id="btn_cadastrarEstabelecimento" onclick="window.location.href = '{{ route('estabelecimentos.cadastrar') }}';">CADASTRAR ESTABELECIMENTO</button>
                                                </div>
                                            </div>
                                            <br/>
                                            <div class="row">
                                                <div class="col-12 col-sm-12" style="padding: 0">
                                                    <span>Estabelecimentos disponíveis (@{{ estabelecimentos.length }}): </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row estabelecimentos" ng-init="getEstabelecimentos()" style="">
                                    <div class="col-sm-12 horizontal-center h-100">
                                        <div class="row estabelecimento" ng-repeat="estabelecimento in estabelecimentos">
                                            <div class="col-sm-12">
                                                <div class="row h-100">
                                                    <div class="col-8 col-sm-8">
                                                        <div class="row h-100" style="align-items: center">
                                                            <div class="col-sm-12">
                                                                <span>@{{ estabelecimento.nm_estabelecimento }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4 col-sm-4">
                                                        <button type="button" class="btn">
                                                            <div class="img">
                                                                <img src="{{ asset('dist/img/excluirProdutoLista.png') }}"/>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        app.controller
                        (
                            "Estabelecimento", 
                            function($scope, $http, $timeout) 
                            {
                                $scope.estabelecimentos = [];

                                $scope.getEstabelecimentos = function()
                                {
                                    $http
                                    (
                                        {
                                            url: "{{ route('estabelecimentos') }}",
                                            method: 'GET'
                                        }
                                    )
                                    .then
                                    (
                                        function callbackSucesso(response)
                                        {
                                            $scope.estabelecimentos = response.data;
                                        },
                                        function callbackErro(response)
                                        {
                                        }
                                    );
                                };
                            }
                        );
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')

@endsection