<style>
    footer
    {
        display: block !important;
        overflow: auto;
    }

    .img 
    {
        width: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .footer .botao
    {
        cursor: pointer;
        font-size: 0.8em;
        text-align: center;
        word-break: break-word;
        padding: 5px;
    }

    .footer div
    {
        padding: 0;
    }
</style>

<div class="row footer h-100">
    <div class="col-12">
        <div class="row">
            <div class="col-3 botao">
                <div class="row">
                    <div class="col-12">
                        <div class="img">
                            <img src="{{ asset('dist/img/carrinho.png') }}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span>Carrinho</span>
                    </div>
                </div>
            </div>

            <div class="col-3 botao">
                <div class="row">
                    <div class="col-12">
                        <div class="img">
                            <img src="{{ asset('dist/img/mapa.png') }}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span>Mapa</span>
                    </div>
                </div>
            </div>

            <div class="col-3 botao" onclick="showListaCompras()">
                <div class="row">
                    <div class="col-12">
                        <div class="img">
                            <img src="{{ asset('dist/img/interesses.png') }}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span>Lista de Compras</span>
                    </div>
                </div>
            </div>

            <div class="col-3 botao" onclick="efetuarLogout()">
                <div class="row">
                    <div class="col-12">
                        <div class="img">
                            <img src="{{ asset('dist/img/logout.png') }}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span>Sair</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function efetuarLogout()
    {
        $.ajax
        (
            {
                url: "{{ route('logout') }}",
                method: "get",
                success: function(response)
                {
                    window.location.reload();
                }
            }
        ); 
    }
</script>