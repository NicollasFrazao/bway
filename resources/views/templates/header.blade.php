<style>
    .logo 
    {
        width: 90px;
        height: 90px;
        margin-left: auto;
        margin-right: auto;
        cursor: pointer;
    }
    
    .titulo-header
    {
        margin-left: auto;
        margin-right: auto;

        min-width: 256px;
        min-height: 30px;
        width: 1px;
        z-index: 9998;

        background-color: #FFFFFF;
        color: #383838;
        
        display: flex;
        justify-content: center;
        align-items: center;
        
        border-radius: 0px 0px 5px 5px;
        box-shadow: 0px 5px 20px 0px black
    }
</style>

<div class="row header">
    <div class="col-md-6">
        <div class="logo">
            <img src="dist/img/logo.png"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="titulo-header">
            <span>{{$tituloHeader ?? 'b-Way'}}</span>
        </div>
    </div>
</div>