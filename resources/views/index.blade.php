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
        
    </style>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <div class="logo">
                        <img src="../resources/img/logo.png"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="logo-titulo">
                        <span>b-Way</span>
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
