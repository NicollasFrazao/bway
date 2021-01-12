@extends('templates.app')

@section('title', $estabelecimento -> nm_estabelecimento)

@section('header')
    @include('templates.header', ['tituloHeader' => $estabelecimento -> nm_estabelecimento])
@endsection

@section('onload')
    show();
@endsection

@section('main')
    <style>
        .titulo-header
        {
            display: none;
        }

        .content
        {
            padding: 10px !important;
        }
    </style>

    <div class="content horizontal-center">
        <div class="row h-100">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 horizontal-center h-100" style="padding: 0;">
                <div class="row tela estabelecimento h-100" ng-controller="Estabelecimento">
                    
                    <div style="width: 100%; height: 100%;">
                        <img src="{{ asset('dist/img/Marcadinho UNISANTA.jpg') }}"/>
                    </div>

                    <!--
                    <div class="col-sm-12 h-100">
                        
                    </div>
                    -->

                    <script>
                        app.controller
                        (
                            "Estabelecimento", 
                            function($scope, $http, $timeout) 
                            {
                                $scope.carrinho = [];
                                $scope.produtosCarrinho = [];

                                $scope.getCarrinho = function()
                                {
                                    $http
                                    (
                                        {
                                            url: "{{ route('usuario.carrinho', [ 'usuario' => session() -> get('usuario') -> cd_usuario, 'estabelecimento' => 1 ]) }}",
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
            $('.tela').fadeOut(100, function() { $('.tela.estabelecimento').fadeIn(100); });
        }
    </script>
@endsection

@section('footer')
    @include('templates.footer')
@endsection