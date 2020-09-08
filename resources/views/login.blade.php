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
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4 horizontal-center">
                <div class="row">
                    <div class="col-sm-12">
                        <form id="Frm_Login">
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label class>E-mail: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="email" id="txt_emailUsuario" name="emailUsuario" class="form-control" placeholder="usuario@bway.anytech.com.br" required/>    
                                </div>
                            </div>   
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label>Senha: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="password" id="txt_senhaUsuario" name="senhaUsuario" class="form-control" placeholder="************" required/>    
                                </div>
                            </div>
                            <br/>   
                            <div class="row form-group">
                                <div class="col-6 col-sm-6 horizontal-center">
                                    <button type="submit" id="btn_entrar" class="form-control btn b-Way">ENTRAR</button>    
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
                        <a href="usuario/create">
                            <span>Ainda não se cadastrou? </span><br/>
                            <span>Não perca tempo e clique aqui.</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        Frm_Login.onsubmit = function()
        {
            if (txt_emailUsuario.value.trim() == '' || txt_emailUsuario.value.indexOf('@') == -1)
            {
                alert('Por favor preencher o campo "E-mail" corretamente!');
                txt_emailUsuario.focus();
            }
            else if (txt_senhaUsuario.value.trim() == '')
            {
                alert('Por favor preencher o campo "Senha" corretamente!');
                txt_senhaUsuario.focus();
            }
            else
            {
                $.ajax
                (
                    {
                        url: "{{ route('login.efetuar') }}",
                        method: 'post',
                        data: $(this).serialize(),
                        dataType: "json",
                        beforeSend: function()
                        {
                            btn_entrar.disabled = true;
                        },
                        success: function(response)
                        {
                            if ("ds_mensagem" in response)
                            {
                                while (response.ds_mensagem.indexOf('\\n') != -1)
                                {
                                    response.ds_mensagem = response.ds_mensagem.replace('\\n', '\n');
                                }

                                alert(response.ds_mensagem);
                            }

                            if ("ic_sucesso" in response && response.ic_sucesso)
                            {
                                window.location.href = "{{ route('home') }}";
                            }
                        },
                        error: function(error)
                        {
                            console.log(error);
                        },
                        complete: function()
                        {
                            btn_entrar.disabled = false;
                        }

                    }
                );
            }
            
            return false;
        }
    </script>
@endsection

@section('footer')
@endsection