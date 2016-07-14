@extends('base')
@section('content')

    @if(!isset($numCamiones) && !isset($camiones))
        <form class="form-horizontal" method="get" id="formCamionesTiendas">

            <fieldset>
                <!-- Form Name -->
                <legend>CAMIONES/TIENDAS</legend>

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


                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="numCamiones">Num. Camiones</label>
                    <div class="col-md-4">
                        <input id="numCamiones" name="numCamiones" type="text" placeholder="Inserte nº camiones" class="form-control input-md"  value="">
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
    @elseif(!isset($camiones))
        <form class="form-horizontal" method="post">
            <fieldset>
                <!-- Form Name -->
                <legend>CAMIONES/TIENDAS</legend>
                <div class="row">
                    <div class="col-md-offset-1 col-xs-offset-1 col-md-11">
                        <table>
                            <tr>
                                @for($iCamion = 0; $iCamion<$numCamiones; $iCamion++)
                                    <th colspan="2">Camión {{$iCamion+1}}</th>
                                @endfor
                            </tr>
                            @foreach($tiendas as $loc=>$tienda)
                                <tr style="border-bottom: 1px solid black; padding: 5px;">
                                    @for($iCamion = 0; $iCamion<$numCamiones; $iCamion++)
                                    <td>
                                        <input type="checkbox" name="tiendas[{{$iCamion}}][]" value="{{$tienda["ean"]}}">
                                    </td>
                                    <td style="padding-left: 10px; cursor: pointer;" class="tiendaClick">
                                        <div class="tienda">
                                            <span>{!!$tienda["tienda"]!!}</span>
                                        </div>
                                    </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table><br>
                        <button type="submit" class="btn btn-success">Generar resumen</button>
                    </div>
                </div>


            </fieldset>
        </form>
    @else
        <fieldset>
            <!-- Form Name -->
            <legend>CAMIONES/TIENDAS</legend>
            <div class="row">
                <div class="col-md-offset-1 col-xs-offset-1 col-md-11">
                    @foreach($camiones as $iCamion=>$prods)
                        <table>
                            <tr>
                                <th>Camion {{$iCamion+1}}</th>
                            </tr>

                            @foreach($prods as $ref=>$prod)
                                <tr style="border-bottom: 1px solid black;">
                                    <td style="padding: 10px;">{{$ref}}</td>
                                    <td style="padding: 10px;">{{$prod["bultos"]}} bultos</td>
                                    <td style="padding: 10px;">{{$prod["cantidad"]}} uds.</td>
                                </tr>
                            @endforeach
                        </table><br><br>
                    @endforeach
                </div>
            </div>
        </fieldset>
    @endif
@endsection
@section('scripts')
    <script>
        $(function() {
            $('#formCamionesTiendas').on("submit", function(e){
                e.preventDefault();
                document.location.href = '{{url("app/edi/build-camiones-tiendas")}}/'+$('#numCliente').val()+'/'+$('#ejercicio').val()+'/'+$('#pedido').val()+'/'+$('#numCamiones').val();
                return false;
            });

            $('.tiendaClick').on("click", function(e) {
                var checkbox = $(this).prev().find(":checkbox");
                if(checkbox.is(":checked")) {
                    checkbox.prop("checked", false);
                }
                else checkbox.prop("checked", true);
            });
        })
    </script>
@endsection