@extends('templates.app')

@section('title', 'Login')

@section('header')
    @include('templates.header', ['tituloHeader' => 'Efetue seu login!'])
@endsection

@section('onload')
@endsection

@section('main')
    <div class="content horizontal-center">
        <div class="row">
            <div class="col-11 col-sm-8 col-md-6 col-lg-5 col-xl-4 horizontal-center">
                <div class="row">
                    <div class="col-sm-12">
                        <form>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label class>E-mail: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="email" class="form-control" placeholder="usuario@bway.anytech.com.br" required/>    
                                </div>
                            </div>   
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label>Senha: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="password" class="form-control" placeholder="************" required/>    
                                </div>
                            </div>
                            <br/>   
                            <div class="row form-group">
                                <div class="col-6 col-sm-6 horizontal-center">
                                    <button type="submit" class="form-control btn b-Way">ENTRAR</button>    
                                </div>
                            </div>                    
                        </form>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-sm-12 horizontal-center" style="text-align: center">
                        <a>
                            <span>Esqueceu a senha?</span><br/>
                            <span>Clique aqui para recupera-la.</span>
                        </a>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-sm-12 horizontal-center" style="text-align: center">
                        <a href="usuarios/create">
                            <span>Ainda não se registrou? </span><br/>
                            <span>Não perca tempo e clique aqui.</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
@endsection