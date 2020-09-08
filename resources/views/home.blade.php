@extends('templates.app')

@section('title', 'Home')

@section('header')
    @include('templates.header', ['tituloHeader' => 'b-Way'])
@endsection

@section('onload')
    showListaCompras();
@endsection

@section('main')
    <style>
        .tela
        {
            display: none;
        }

        .tela > div
        {
            padding: 0;
        }

        .produtos
        {
            height: calc(100% - 75px);
            margin-top: 10px;
        }

        .produtos > div
        {
            overflow: auto;
        }

        .produtos .produto
        {
            background-color: #383838;
            min-height: 50px;
            margin-bottom: 15px;
            padding: 10px; 
            box-shadow: 3px 3px 5px 1px black;
        }

        .produto div
        {
            padding: 0px;
        }

        .produto button
        {
            float: right;
        }

        .pesquisa.lista.produtos
        {
            display: none;
        }

        .lista.produtos
        {
            padding-top: 20px;
        }
    </style>

    <div class="content horizontal-center">
        <div class="row h-100">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 horizontal-center h-100">
                <div class="row tela lista-compras h-100" ng-controller="ListaCompras">
                    <div class="col-sm-12 h-100">
                        <form id="Frm_PesquisarProduto">
                            <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_nomeUsuario">Pesquisar produto: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input ng-model="nomeProdutoPesquisar" ng-keyup="pesquisarProduto()" type="text" id="txt_nomeProduto" name="nomeProduto" class="form-control" placeholder="Digite para adicionar um produto..." required/>    
                                </div>
                            </div>
                        </form>

                        <div class="row minha lista produtos" ng-init="getListaCompras()">
                            <div class="col-sm-12 horizontal-center h-100">
                                <div style="">
                                    <div class="row" style="text-align: center">
                                        <div class="col-12 col-sm-12">
                                            <button type="button" class="btn b-Way" ng-click="limparListaCompras()" id="btn_limparListaCompras">LIMPAR LISTA DE COMPRAS</button>
                                        </div>
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="col-12 col-sm-12" style="padding: 0">
                                            <span>Itens na sua lista de compras (@{{ produtosLista.length }}): </button>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <div class="row produto" ng-repeat="produtoLista in produtosLista">
                                    <div class="col-sm-12">
                                        <div class="row h-100">
                                            <div class="col-8 col-sm-8">
                                                <div class="row h-100" style="align-items: center">
                                                    <div class="col-sm-12">
                                                        <span>@{{ produtoLista.nm_produto }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-4">
                                                <button type="button" class="btn" ng-click="excluirProduto(produtoLista.cd_produto)">
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

                        <div class="row pesquisa lista produtos">
                            <div class="col-sm-12 horizontal-center h-100">
                                <div style="">
                                    <div class="row">
                                        <div class="col-12 col-sm-12" style="padding: 0">
                                            <span>Resultados da pesquisa (@{{ produtosPesquisar.length }}): </button>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <div class="row produto" ng-repeat="produtoPesquisar in produtosPesquisar">
                                    <div class="col-sm-12">
                                        <div class="row h-100">
                                            <div class="col-8 col-sm-8">
                                                <div class="row h-100" style="align-items: center">
                                                    <div class="col-sm-12">
                                                        <span>@{{ produtoPesquisar.nm_produto }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-4">
                                                <button type="button" class="btn" ng-click="adicionarProduto(produtoPesquisar.cd_produto)">
                                                    <div class="img">
                                                        <img src="{{ asset('dist/img/adicionarProdutoLista.png') }}"/>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        var indicadorPesquisarProduto = false;
                        var timeout = false;
                        
                        app.controller
                        (
                            "ListaCompras", 
                            function($scope, $http, $timeout) 
                            {
                                $scope.produtosLista = [];
                                $scope.produtosPesquisar = [];
                                $scope.nomeProdutoPesquisar = '';

                                $scope.getListaCompras = function()
                                {
                                    $http
                                    (
                                        {
                                            url: "{{ route('usuario.listaCompras', session() -> get('codigoUsuario')) }}",
                                            method: 'GET'
                                        }
                                    )
                                    .then
                                    (
                                        function callbackSucesso(response)
                                        {
                                            $scope.produtosLista = response.data;
                                        },
                                        function callbackErro(response)
                                        {
                                        }
                                    );
                                };

                                $scope.limparListaCompras = function()
                                {
                                    $http
                                    (
                                        {
                                            url: "{{ route('listaCompras.limpar', session() -> get('codigoUsuario')) }}",
                                            method: 'DELETE'
                                        }
                                    )
                                    .then
                                    (
                                        function callbackSucesso(response)
                                        {
                                            if (response.data.ic_sucesso)
                                            {
                                                console.log('Lista de compras limpa com sucesso!');
                                                $scope.getListaCompras();
                                            }
                                        },
                                        function callbackErro(response)
                                        {
                                        }
                                    );
                                }

                                $scope.adicionarProduto = function(codigoProduto = false)
                                {
                                    if (codigoProduto)
                                    {
                                        $http
                                        (
                                            {
                                                url: "{{ route('listaCompras.alterar', session() -> get('codigoUsuario')) }}",
                                                method: 'PUT',
                                                data:
                                                {
                                                    "codigoProduto": codigoProduto,
                                                    "indicadorProduto": 1
                                                }
                                            }
                                        )
                                        .then
                                        (
                                            function callbackSucesso(response)
                                            {
                                                if (response.data.ic_sucesso)
                                                {
                                                    console.log('Produto adicionado a sua lista de compras com sucesso!');
                                                    $scope.pesquisarProduto();
                                                }
                                            },
                                            function callbackErro(response)
                                            {
                                            }
                                        );
                                    }
                                }

                                $scope.excluirProduto = function(codigoProduto = false)
                                {
                                    if (codigoProduto)
                                    {
                                        $http
                                        (
                                            {
                                                url: "{{ route('listaCompras.alterar', session() -> get('codigoUsuario')) }}",
                                                method: 'PUT',
                                                data:
                                                {
                                                    "codigoProduto": codigoProduto,
                                                    "indicadorProduto": 0
                                                }
                                            }
                                        )
                                        .then
                                        (
                                            function callbackSucesso(response)
                                            {
                                                if (response.data.ic_sucesso)
                                                {
                                                    console.log('Produto excluído a sua lista de compras com sucesso!');
                                                    $scope.getListaCompras();
                                                }
                                            },
                                            function callbackErro(response)
                                            {
                                            }
                                        );
                                    }

                                    
                                }

                                $scope.pesquisarProduto = function()
                                {
                                    $timeout.cancel(timeout);
                                    
                                    timeout = $timeout
                                    (
                                        function ()
                                        {
                                            if (!$scope.nomeProdutoPesquisar)
                                            {
                                                if (indicadorPesquisarProduto)
                                                {
                                                    indicadorPesquisarProduto = false;
                                                    $('.lista.produtos').fadeOut(100, function () { $('.minha.lista.produtos').fadeIn(100); });                                        
                                                }

                                                $scope.getListaCompras();
                                            }
                                            else
                                            {
                                                if (!indicadorPesquisarProduto)
                                                {
                                                    indicadorPesquisarProduto = true;
                                                    $('.lista.produtos').fadeOut(100, function () { $('.pesquisa.lista.produtos').fadeIn(100); });
                                                }

                                                var urlRequest = "{{ route('produtos.pesquisar', ':nomeProdutoPesquisar') }}";
                                                urlRequest = urlRequest.replace(':nomeProdutoPesquisar', $scope.nomeProdutoPesquisar);

                                                $http
                                                (
                                                    {
                                                        url: urlRequest,
                                                        method: 'GET',
                                                        params:
                                                        {
                                                            "codigoUsuario": {{ session() -> get('codigoUsuario') }}
                                                        }
                                                    }
                                                )
                                                .then
                                                (
                                                    function callbackSucesso(response)
                                                    {
                                                        $scope.produtosPesquisar = response.data;
                                                        
                                                        if ($scope.nomeProdutoPesquisar)
                                                        {
                                                            $('.pesquisa.lista.produtos').fadeIn(100);
                                                        }
                                                    },
                                                    function callbackErro(response)
                                                    {
                                                    }
                                                );
                                            }
                                        },
                                        100
                                    );
                                }
                            }
                        );
                    </script>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>

                <div class="row tela">
                    <div class="col-sm-12">
                        teste
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showListaCompras()
        {
            lbl_tituloHeader.textContent = 'Lista de Compras';

            $('.tela').fadeOut(100, function() { $('.lista-compras').fadeIn(100); });            

            //$('.produtos')[0].style.setProperty('height', $('.produtos')[0].clientHeight - $('#Frm_PesquisarProduto')[0].clientHeight - 10 + 'px ', 'important');
        }
    </script>
@endsection

@section('footer')
    @include('templates.footer')
@endsection