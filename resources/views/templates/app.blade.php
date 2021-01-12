<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" ng-app="b-Way">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        
        <link rel="shortcut icon" href="{{ asset('dist/img/logo.jpg')}}"/>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <title>b-Way - @yield('title')</title>

        <!-- Styles -->
        <link href="{{ asset('dist/css/bootstrap.css') }}" rel="stylesheet">

        <style>
            html, body
            {
                background-color: #FF6600;
                color: #FFF;
                font-family: 'Century Gothic', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
                padding: 0;
            }

            header, footer
            {
                width: 100%;
                height: auto;
                position: fixed;
                display: none;
                z-index: 1;
            }

            header
            {
                top: 0;
            }

            footer
            {
                bottom: 0;
            }

            .header, .footer
            {
                background-color: #383838;
                position: relative;
                z-index: 9999;
            }

            .header
            {
                box-shadow: 0px 2px 10px 0px black;
            }

            .footer
            {
                box-shadow: 0px -2px 10px 0px black;
            }

            a, a:hover, a:visited, a:active
            {
                text-decoration: none;
                color: #FFF;
            }

            img
            {
                width: 100%;
                height: 100%;
            }

            .full-height 
            {
                height: 100vh;
            }

            .flex-center 
            {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref 
            {
                position: relative;
            }

            .main
            {
                max-height: 100vh;                
                height: -webkit-fill-available;
                z-index: 1;
                display: flex;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            div.horizontal-center
            {
                margin-left: auto !important;
                margin-right: auto !important;
            }
            
            div.vertical-center
            {
                margin-top: auto !important;
                margin-bottom: auto !important;
            }

            .btn.b-Way
            {
                color: #FFF;
                background-color: #383838;
                min-height: 50px;
            }

            input.form-control
            {
                height: 40px;
            }
            
            .row
            {
                margin: 0;
            }

            .tela
            {
                display: none;
            }

            .tela > div
            {
                padding: 0;
            }

            .produtos, .estabelecimentos
            {
                margin-top: 10px;
                overflow: auto;
            }

            .minha.lista .produtos
            {
                height: calc(100% - 180px);
            }

            .pesquisa .produtos
            {
                height: calc(100% - 95px);
            }

            .produtos .produto, .estabelecimentos .estabelecimento
            {
                background-color: #383838;
                min-height: 50px;
                margin-bottom: 15px;
                padding: 0;
                padding-left: 10px;
                box-shadow: 3px 3px 5px 1px black;
            }

            .produto div, .estabelecimento div
            {
                padding: 0px;
            }

            .produto button, .estabelecimento button
            {
                float: right;
            }

            .pesquisa.lista.produtos
            {
                display: none;
            }

            .minha.lista, .produtos, .pesquisa.lista, .estabelecimentos
            {
                padding-top: 20px;
            }
        </style>
        
        <script src="{{ asset('dist/js/angular.js') }}"></script>
        <script src="{{ asset('dist/js/app.js') }}"></script>
        <script src="{{ asset('dist/js/jquery.min.js') }}"></script>
        <script src="{{ asset('dist/js/bootstrap.js') }}"></script>
    </head>
    <body>
        <div class="full-height position-ref">
            <header>
                @section('header')
                @show
            </header>

            <section class="main container-fluid">
                <style>
                    .content
                    {
                        width: 100%;
                        overflow: hidden;
                        padding-top: 20px;
                        /*
                        padding-left: 20px;
                        padding-right: 20px;
                        */
                    }
                </style>

                <script>
                    var app = angular.module("b-Way", []);
                    
                    var codigoEstabelecimentoAtual = 1;

                    window.onload = function()
                    {
                        var header = document.getElementsByClassName('header');
                        var footer = document.getElementsByClassName('footer');

                        if (header.length == 1)
                        {
                            var main = document.getElementsByClassName('main')[0];

                            main.style.paddingTop = document.getElementsByTagName('header')[0].clientHeight + 'px';
                        }

                        if (footer.length == 1)
                        {
                            var main = document.getElementsByClassName('main')[0];

                            main.style.paddingBottom = document.getElementsByTagName('footer')[0].clientHeight + 'px';
                        }

                        @section('onload')
                        @show
                    }
                </script>

                @section('main')
                @show
            </section>
            
            <footer>
                @section('footer')
                @show
            </footer>
        </div>
    </body>
</html>
