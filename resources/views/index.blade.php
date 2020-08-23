@extends('templates.app')

@section('title', 'Início')

@section('main')
    <style>
        .logo-titulo
        {
            width: 100%;
            font-size: 1.5em;
            text-align: center;
        }

        .content
        {
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: #383838 !important;
        }        

        .main
        {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        div.row.h-100
        {
            margin: 0;
        }
    </style>

    <div class="content">
        <div class="row h-100">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-4 col-6 horizontal-center my-auto">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="logo">
                            <img src="dist/img/logo.png"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="logo-titulo">
                            <span>b-Way</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var login = false;

        window.onload = function()
        {
            setTimeout(() => {
                if (!login)
                {
                    window.location.href = 'login';
                }
            }, 2000);
        }
    </script>
@endsection
