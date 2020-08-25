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
            min-height: 60px;
            margin-bottom: 15px;
            padding: 10px; 
            box-shadow: 3px 3px 5px 1px black;
        }

        .produto div
        {
            padding: 0px;
        }

        .produto .img
        {
            float: right;
        }

        .pesquisa.lista.produtos
        {
            display: none;
        }
    </style>

    <div class="content horizontal-center">
        <div class="row h-100">
            <div class="col-12 col-sm-8 col-md-8 col-lg-8 col-xl-8 horizontal-center h-100">
                <div class="row tela lista-compras h-100" ng-controller="Produtos">
                    <div class="col-sm-12 h-100">
                        <form id="Frm_PesquisarProduto">
                            <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_nomeUsuario">Pesquisar produto: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input  type="text" id="txt_nomeProduto" name="nomeProduto" class="form-control" placeholder="Digite para adicionar um produto..." required/>    
                                </div>
                            </div>          
                        </form>

                        <script>
                            var indicadorPesquisarProduto = false;

                            txt_nomeProduto.onkeyup = function()
                            {
                                if (this.value.trim() == '')
                                {
                                    if (indicadorPesquisarProduto)
                                    {
                                        $('.lista.produtos').fadeOut(100);
                                        $('.minha.lista.produtos').fadeIn(100);
                                        
                                        indicadorPesquisarProduto = false;
                                    }
                                }
                                else
                                {
                                    if (!indicadorPesquisarProduto)
                                    {
                                        $('.lista.produtos').fadeOut(100);
                                        $('.pesquisa.lista.produtos').fadeIn(100);
                                        
                                        indicadorPesquisarProduto = true;
                                    }
                                    /*
                                    $.ajax
                                    (
                                        {
                                            url: " route
                                        }
                                    );
                                    */
                                }
                            }
                            
                            
                            app.controller
                            (
                                "Produtos", 
                                function($scope) 
                                {
                                    $scope.produtosPesquisar = [1,2,3,4,5,6];

                                    $scope.teste = function()
                                    {
                                        alert('oi');
                                        $scope.produtosPesquisar = [1,2];
                                    }
                                }
                            );
                        </script>

                        <div class="row minha lista produtos">
                            <div class="col-sm-12 horizontal-center h-100">
                                &nbsp;
                            </div>
                        </div>

                        <div class="row pesquisa lista produtos" ng-controller="Produtos">
                            <div class="col-sm-12 horizontal-center h-100">
                                <div class="row produto" ng-repeat="produtoPesquisar in produtosPesquisar">
                                    <div class="col-sm-12">
                                        <div class="row h-100">
                                            <div class="col-8 col-sm-8">
                                                <div class="row h-100" style="align-items: center">
                                                    <div class="col-sm-12">
                                                        <span>@{{ produtoPesquisar }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-4">
                                                <div class="img">
                                                    <img src="{{ asset('dist/img/logo.png') }}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

            $('.tela').fadeOut(100);
            $('.lista-compras').fadeIn(100);

            //$('.produtos')[0].style.setProperty('height', $('.produtos')[0].clientHeight - $('#Frm_PesquisarProduto')[0].clientHeight - 10 + 'px ', 'important');
        }
    </script>
@endsection

@section('footer')
    @include('templates.footer')
@endsection