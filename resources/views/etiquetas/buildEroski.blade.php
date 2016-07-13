@extends('base')
@section('content')
    @if(!isset($labels))
        <form class="form-horizontal" method="get" id="formPedido">

            <fieldset>
                <!-- Form Name -->
                <legend>ETIQUETAS EROSKI</legend>

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
    @else
    <form class="form-horizontal" method="post">
        <fieldset>
            <!-- Form Name -->
            <legend>ETIQUETAS EROSKI</legend>
            <div class="row">
                <div class="col-md-offset-1 col-xs-offset-1 col-md-11">
                    <input type="checkbox" id="selectAll" checked> <strong>Seleccionar todos</strong>
                    <table>
                        @foreach($labels as $codTienda => $label)
                            <tr style="border-bottom: 1px solid black; padding: 5px;">
                                <td>
                                    <input type="checkbox" checked name="tiendas[{{$codTienda}}]" value="{{$label["codTienda"]}}">
                                </td>
                                <td style="padding-left: 10px;">
                                    <div class="tienda">
                                        <span>{!!$label["tienda"]!!}</span>
                                    </div>
                                </td>

                                <td style="padding: 10px;">
                                    Máx. Bultos: <input type="number" name="numBultos[{{$codTienda}}]" value="{{$label["bultos"]}}" max="{{$label["bultos"]}}">
                                </td>
                            </tr>
                            <input type="hidden" name="nombresTiendas[{{$codTienda}}]" value="{{$label["tienda"]}}">
                        @endforeach
                    </table><br>
                    <button type="submit" class="btn btn-success">Generar etiquetas</button>
                </div>
            </div>


        </fieldset>
    </form>
    @endif
@endsection
@section('scripts')
    <script>
        $(function() {
            $('#formPedido').on("submit", function(e){
                e.preventDefault();
                document.location.href = '{{url("app/edi/build-eroski-labels")}}/'+$('#numCliente').val()+'/'+$('#ejercicio').val()+'/'+$('#pedido').val();
                return false;
            });

            $('#selectAll').on('change', function() {
                var checkboxes = $(this).closest('form').find(':checkbox');
                if($(this).is(':checked')) {
                    checkboxes.prop('checked', true);
                } else {
                    checkboxes.prop('checked', false);
                }
            });
        })
    </script>
@endsection