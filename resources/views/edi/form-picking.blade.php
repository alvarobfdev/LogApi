@extends('base')
@section('content')
    <form class="form-horizontal">
        <fieldset>


            <!-- Form Name -->
            <legend>EXPORTAR ALBARAN EDI</legend>


            <div id="capaBusquedaAlbaran" >

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
                    <label class="col-md-4 control-label" for="numSerie">Núm. Serie</label>
                    <div class="col-md-4">
                        <input id="numSerie" name="numSerie" type="text" placeholder="Inserte número serie" class="form-control input-md"  value="">

                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="numAlbaran">Núm. Albaran</label>
                    <div class="col-md-4">
                        <input id="numAlbaran" name="numAlbaran" type="text" placeholder="Inserte número albarán" class="form-control input-md" required="" value="1">

                    </div>
                </div>



                <!-- Button -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="comenzar"></label>
                    <div class="col-md-4">
                        <button id="comenzar" name="comenzar" class="btn btn-primary">Comenzar</button>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>



@endsection
@section('scripts')
    <script>
        $(function() {
            $('form').submit(function(e) {
                e.preventDefault();
                var year = $('#ejercicio').val();
                var numCliente = $('#numCliente').val();
                var numSerie = $('#numSerie').val();
                if(numSerie != "") {
                    numSerie = "/"+numSerie;
                }
                var numAlbaran = $('#numAlbaran').val();

                window.location.href = "{{url('app/edi/picking')}}/"+year+"/"+numCliente+"/"+numAlbaran+numSerie;

            });

        });
    </script>
@endsection

