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
                background-color: #383838;
                color: #FFF;
                font-family: 'Century Gothic', sans-serif;
                font-weight: 200;
                font-size: 1em;
                height: 100vh;
                margin: 0;
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
        </style>
    </head>
    <body>
        <div class="position-ref full-height">
            <div class="content container-fluid">
                <header>
                    @section('header')
                    @show

                    <style>
                        .header
                        {
                            height: 100%;
                            max-height: 90px;
                        }

                        .logo 
                        {
                            width: 90px;
                        }
                    </style>

                    <div class="row header">
                        <div class="col-md-6">
                            <div class="logo">
                                <img src="dist/img/logo.png"/>teste
                            </div>
                        </div>
                    </div>
                </header>

                <section>
                    @section('main')
                    @show
                </section>
                
                <footer>
                    @section('header')
                    @show

                    <style>
                        .footer
                        {
                            height: 90px;
                        }
                    </style>

                    <div class="row footer">
                        <div class="col-md-6">
                            &nbsp;
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
