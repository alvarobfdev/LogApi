@extends('base')
@section('content')


<form class="form-horizontal" method="get" id="formCamionesTiendas">

    <fieldset>
        <!-- Form Name -->
        <legend>ETIQUETAS EROSKI PRE-ALBARÁN</legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="ejercicio">Ejercicio</label>
            <div class="col-md-4">
                <input id="ejercicio" name="ejercicio" value="2016" type="text" placeholder="Inserte ejercicio" class="form-control input-md" required="">
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="numCliente">Núm. Cliente</label>
            <div class="col-md-4">
                <input id="numCliente" name="numCliente" type="text" placeholder="Inserte número cliente" class="form-control input-md" required="" value="176">

            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="pedido">Pedido Logival</label>
            <div class="col-md-4">
                <input id="pedido" name="pedido" type="text" placeholder="Inserte pedido" class="form-control input-md"  value="">
            </div>
        </div>


        <!-- Button -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="comenzar"></label>
            <div class="col-md-4">
                <button id="comenzar" name="comenzar" class="btn btn-primary">Comenzar</button>
            </div>
        </div>
    </fieldset>
</form>

@endsection
@section('scripts')
    <script>
        $(function() {
            $('#formCamionesTiendas').on("submit", function(e){
                e.preventDefault();
                document.location.href = '{{url("app/edi/eroski-labels-from-pedido")}}/'+$('#numCliente').val()+'/'+$('#ejercicio').val()+'/'+$('#pedido').val();
                return false;
            });


        })
    </script>
@endsection