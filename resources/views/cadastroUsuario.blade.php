@extends('templates.app')

@section('title', 'Cadastro de Usuário')

@section('header')
    @include('templates.header', ['tituloHeader' => 'Cadastra-se!'])
@endsection

@section('onload')
@endsection

@section('main')
    <div class="content horizontal-center">
        <div class="row">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4 horizontal-center">
                <div class="row">
                    <div class="col-sm-12">
                        <form id="Frm_CadastroUsuario">
                            <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_nomeUsuario">Nome: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="text" id="txt_nomeUsuario" name="nomeUsuario" class="form-control" placeholder="José Silva de Oliveira" required/>    
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_emailUsuario">E-mail: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="email" id="txt_emailUsuario" name="emailUsuario" class="form-control" placeholder="usuario@bway.anytech.com.br" required/>    
                                </div>
                            </div>  
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_senhaUsuario">Senha: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="password" id="txt_senhaUsuario" name="senhaUsuario" class="form-control" placeholder="************" required/>    
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-12 horizontal-center">
                                    <label for="txt_confirmarSenhaUsuario">Confirmar senha: </label>
                                </div>
                                <div class="col-sm-12 horizontal-center">
                                    <input type="password" id="txt_confirmarSenhaUsuario" class="form-control" placeholder="************" required/>    
                                </div>
                            </div>
                            <br/>
                            <div class="row form-group">
                                <div class="col-6 col-sm-6 horizontal-center">
                                    <button type="button" id="btn_voltar" class="form-control btn b-Way" onclick="window.location.href = '{{ route("login.index") }}'">VOLTAR</button>    
                                </div>
                                <div class="col-6 col-sm-6 horizontal-center">
                                    <button type="submit" id="btn_cadastrar" class="form-control btn b-Way">CADASTRAR</button>    
                                </div>
                            </div>                  
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        Frm_CadastroUsuario.onsubmit = function()
        {
            if (txt_nomeUsuario.value.trim() == '')
            {
                alert('Por favor preencher o campo "Nome" corretamente!');
                txt_nomeUsuario.focus();
            }
            else if (txt_emailUsuario.value.trim() == '' || txt_emailUsuario.value.indexOf('@') == -1)
            {
                alert('Por favor preencher o campo "E-mail" corretamente!');
                txt_emailUsuario.focus();
            }
            else if (txt_senhaUsuario.value.trim() == '' || txt_senhaUsuario.value.trim() != txt_confirmarSenhaUsuario.value.trim())
            {
                alert('Por favor preencher o campo "Senha" corretamente! As senhas não coincidem.');
                txt_senhaUsuario.focus();
            }
            else
            {
                $.ajax
                (
                    {
                        url: "{{ route('usuarios.cadastrar') }}",
                        method: "post",
                        data: $(this).serialize(),
                        dataType: "json",
                        beforeSend: function()
                        {
                            btn_cadastrar.disabled = true;
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
                                window.location.href = "{{ route('login.index') }}";
                            }
                        },
                        error: function(error)
                        {
                            console.log(error);
                        },
                        complete: function()
                        {
                            btn_cadastrar.disabled = false;
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