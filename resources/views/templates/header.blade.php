<style>
    header
    {
        display: block !important;
    }

    .logo 
    {
        width: 90px;
        height: 90px;
        cursor: pointer;
    }
    
    .titulo-header
    {
        min-width: 256px;
        max-width: 80%;
        min-height: 30px;
        
        width: fit-content;
        padding: 5px;
        padding-right: 10px;
        padding-left: 10px;

        display: flex;
        z-index: 9998;

        background-color: #FFFFFF;
        color: #383838;        
        
        text-align: center;
        justify-content: center;
        align-items: center;
        
        border-radius: 0px 0px 5px 5px;
        box-shadow: 0px 5px 20px 0px black
    }
</style>

<div class="row header">
    <div class="col-sm-12">
        <div class="logo horizontal-center" onclick="history.replaceState(null, 'b-Way - Lista de Compras', '{{ route('home') }}'); window.location.reload();">
            <img src="{{ asset('dist/img/logo.png') }}"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6 horizontal-center">
        <div class="titulo-header horizontal-center">
            <span id="lbl_tituloHeader">{{$tituloHeader ?? 'b-Way'}}</span>
        </div>
    </div>
</div>