@extends('templates.app')

@section('title', 'Carrinho - '.$estabelecimento -> nm_estabelecimento)

@section('header')
    @include('templates.header', ['tituloHeader' => 'Carrinho - '.$estabelecimento -> nm_estabelecimento])
@endsection

@section('onload')
    show();
@endsection

@section('main')
    <style>
        .carrinho .setores.produtos
        {
            height: calc(100% - 34px);
        }

        .row.setor .titulo
        {
            padding: 0;
        }

        .row.setor
        {
            margin-bottom: 15px;
        }
    </style>

    <div class="content horizontal-center">
        <div class="row h-100">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 horizontal-center h-100">
                <div class="row tela carrinho h-100" ng-controller="Carrinho">
                    <div class="col-sm-12 h-100">
                        
                        <div class="row minha lista h-100">
                            <div class="col-sm-12 horizontal-center h-100" style="padding: 0;">
                                <div class="row">
                                    <div class="col-sm-12 h-100"> 
                                        <div style="">
                                            <div class="row">
                                                <div class="col-12 col-sm-12" style="padding: 0">
                                                    <span>Itens no seu carrinho (@{{ produtosCarrinho.length }}): </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row setores produtos" ng-init="getCarrinho()" style="">
                                    <div class="col-sm-12 horizontal-center h-100">
                                        <div class="setor" ng-repeat="setor in carrinho">
                                            <div class="row setor">
                                                <div class="col-sm-12 titulo">
                                                    <span>@{{ setor.nm_setor }}</span>
                                                </div>
                                            </div>
                                            <div class="row produto" ng-repeat="produto in setor.produtos">
                                                <div class="col-sm-12">
                                                    <div class="row h-100">
                                                        <div class="col-8 col-sm-8">
                                                            <div class="row h-100" style="align-items: center">
                                                                <div class="col-sm-12">
                                                                    <span>@{{ produto.nm_produto }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 col-sm-4">
                                                            <button type="button" class="btn" ng-click="adquirirProduto(produto.cd_produto)">
                                                                <div class="img">
                                                                    <img src="{{ asset('dist/img/adquirirProduto.png') }}"/>
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
                    </div>

                    <script>
                        app.controller
                        (
                            "Carrinho", 
                            function($scope, $http, $timeout) 
                            {
                                $scope.carrinho = [];
                                $scope.produtosCarrinho = [];

                                $scope.getCarrinho = function()
                                {
                                    $http
                                    (
                                        {
                                            url: "{{ route('usuario.carrinho', [ 'usuario' => session() -> get('usuario') -> cd_usuario, 'estabelecimento' => $estabelecimento -> cd_estabelecimento ]) }}",
                                            method: 'GET'
                                        }
                                    )
                                    .then
                                    (
                                        function callbackSucesso(response)
                                        {
                                            $scope.carrinho = response.data;
                                            $scope.produtosCarrinho = [];

                                            for (cont = 0; cont <= $scope.carrinho.length - 1; cont = cont + 1)
                                            {
                                                $scope.produtosCarrinho = $scope.produtosCarrinho.concat($scope.carrinho[cont].produtos);
                                            }
                                        },
                                        function callbackErro(response)
                                        {
                                        }
                                    );
                                };

                                $scope.adquirirProduto = function(codigoProduto = false)
                                {
                                    if (codigoProduto)
                                    {
                                        if (confirm('Tem certeza que deseja confirmar que adquiriu o produto?'))
                                        {
                                            $http
                                            (
                                                {
                                                    url: "{{ route('carrinho.alterar', [ 'usuario' => session() -> get('usuario') -> cd_usuario, 'estabelecimento' => 1 ]) }}",
                                                    method: 'PUT',
                                                    data:
                                                    {
                                                        "codigoProduto": codigoProduto
                                                    }
                                                }
                                            )
                                            .then
                                            (
                                                function callbackSucesso(response)
                                                {
                                                    $scope.getCarrinho();
                                                },
                                                function callbackErro(response)
                                                {
                                                }
                                            );
                                        }
                                    }
                                };
                            }
                        );
                    </script>
                </div>
            </div>
        </div>
    </div>

    <script>
        function show()
        {
            $('.tela').fadeOut(100, function() { $('.tela.carrinho').fadeIn(100); });
        }
    </script>
@endsection

@section('footer')
    @include('templates.footer')
@endsection