<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        
        <link rel="shortcut icon" href="dist/img/logo.png"/>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <title>b-Way - @yield('title')</title>

        <!-- Styles -->
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
                min-height: 90px;
                position: fixed;
            }

            .header, .footer
            {
                background-color: #383838;
                position: relative;
                z-index: 9999;
            }

            .header
            {
                top: 0;
                box-shadow: 0px 2px 10px 0px black;
            }

            .footer
            {
                bottom: 0;
                box-shadow: 0px -2px 10px 0px black;
            }

            img
            {
                width: 100%;
                height: auto;
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
                min-height: 100hv;                
            }
        </style>
    </head>
    <body>
        <div class="container-fluid full-height position-ref">
            <header>
                @section('header')
                @show
            </header>

            <section class="main">
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
