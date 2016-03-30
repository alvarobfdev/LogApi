@extends('base')
@section('content')
    <form class="form-horizontal">
    <fieldset>

        <!-- Form Name -->
        <legend>EXPORTAR ALBARAN EDI</legend>

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
                <input id="numCliente" name="numCliente" type="text" placeholder="Inserte número cliente" class="form-control input-md" required="">

            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="numAlbaran">Núm. Albaran</label>
            <div class="col-md-4">
                <input id="numAlbaran" name="numAlbaran" type="text" placeholder="Inserte número albarán" class="form-control input-md" required="">

            </div>
        </div>


        <!-- Button -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="comenzar"></label>
            <div class="col-md-4">
                <button id="comenzar" name="comenzar" class="btn btn-primary">Comenzar</button>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label">PALET 1:</label>
            <div class="col-md-4">
                <input id="ean" name="ean" type="text" placeholder="EAN Producto" class="form-control input-md">
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-4 control-label" for="selectBultos">Tipo de bulto</label>
            <div class="col-md-4">
                <select id="selectBultos" name="selectBultos" class="form-control" multiple="multiple">

                </select>
            </div>
        </div>

    </fieldset>
</form>
    @section('scripts')
    <script>
        var formType = 'start';
        var albaran = null;
        var lin_albaran = null;
        var products = null;
        var currentPalet = 0;

        $(function() {
            $('form').submit(function(e) {
                e.preventDefault();
                if(formType == 'start') {
                    startAlbaran($(this));
                }
                if(formType == 'readEan') {
                    readEan($(this));
                }
            });
        });

        function startAlbaran(form) {
            var datastring = form.serialize();
            $.getJSON('{{url('app/albaran-for-edi')}}', datastring, function(result) {
                var json = result;

                if(json.success == false) {
                    alert("Ha habido un fallo al obtener los datos!");
                    return;
                }
                if(json.data.albaran == null) {
                    alert("No existe ningún albarán de salida con estos datos!");
                    return;
                }

                if(json.data.albaran.totpal < 1) {
                    alert("No se han asignado palets a este albarán!");
                    return;
                }

                albaran = json.data.albaran;
                lin_albaran = json.data.lin_albaran;
                products = json.data.products;
                formType = 'readEan';


            }).fail(function( jqxhr, textStatus, error ) {
                var err = jqxhr.status + ", " + error;
                alert("Error: "+err+".\nComprobar conexión de las máquinas.");
            });
        }

        function readEan(form) {
            var ean = form.find('#ean').val();
            products.forEach(function(product) {
                var select = form.find('#selectBultos');

                if(product.codbar == ean) {
                    select.append($('<option>', {
                        value: 1,
                        text: product.descri
                    }));
                }
            });
        }

        function completarPalets() {

        }
    </script>
    @endsection
@endsection