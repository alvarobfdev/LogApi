@extends('base')
<fieldset>
    <!-- Form Name -->
    <legend>EXPORTAR ALBARAN EDI PARA PICKING</legend>
    <div class="col-sm-4">
        <div class="row">
            <button id="addBulto" type="button" class="btn btn-primary">Añadir Bulto</button>
        </div>
        <div class="row">
            <table class="table" style="font-size: 10px;">

                @foreach($lineasAlbaran as $index=>$linea)
                <tr>
                    <td><input type="checkbox" name="linea_albaran[{{$index}}]" data-id="{{$index}}"></td>
                    <td>{{$linea->codart}}</td>
                    <td><input style="width: 50%; text-align: right" type="text"  value="{{$linea->cantid}}" id="cantidad_bulto[{{$index}}]" name="cantidad_bulto[{{$index}}]"> / <span id="maxUds_{{$index}}">{{$linea->cantid}}</span></td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <table class="table" id="tablaBultos" style="font-size: 10px;">

            </table>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="row" id="capaVisorPalets">
            <table class="table" id="tablaPalets">

            </table>
            <button id="finishEdi" class="btn-primary btn">Exportar EDI</button>
        </div>
    </div>
</fieldset>
@section('content')
@endsection
@section('scripts')
    <script>

        var lineasAlbaran = JSON.parse('{!! $lineasAlbaranJson !!}');
        var bultos = [];
        var palets = [];

        function addBulto() {
            var lineas = generarLineas();
            var bulto = generarBulto(lineas);
            bultos.push(bulto);
        }

        function generarLineas() {
            var lineas = [];
            $('input:checked').each(function() {
                var numlin = $(this).attr('data-id');
                var cantid = parseInt($('input[name="cantidad_bulto['+numlin+']"]').val());
                var linea = copyObject(lineasAlbaran[numlin]);
                linea.cantid = cantid;
                lineas.push(linea);
            });
            return lineas;
        }

        function generarBulto(lineas) {
            var bulto = {
                lineas:lineas,
                cantidad:1
            };
            return bulto;
        }

        function copyObject(object) {
            return JSON.parse(JSON.stringify(object));
        }

        function generateHtmlLine(line, bultoId, lineaId) {
            return '<div>'+line.codart+' '+line.descri+' x '+line.cantid+'uds.'+' <a href="javascript:void(0);" id="deleteLineFromBulto" data-bulto-id="'+bultoId+'" data-linea-id="'+lineaId+'" >Borrar</a> ';
        }

        function generateNumBultosHtml(bulto, idBulto) {
            return '<div>Nº Bultos:<input type="text" name="cantidadBulto['+idBulto+']" value="'+bulto.cantidad+'"></div>'
        }

        function getNumPalets() {
            var numPalets = 0;
            for(var i=0; i<lineasAlbaran.length; i++) {
                numPalets += lineasAlbaran[i].palets;
            }
            return numPalets;
        }

        function generatePaletsSelector(idBulto) {
            var options = generatePaletsSelectorOptions();
            return '<select name="paletSelector['+idBulto+']">'+options+'</select> <button id="addToPalet" data-id="'+idBulto+'" class="btn btn-primary">Añadir al palet</button>';
        }

        function generatePaletsSelectorOptions() {
            var numPalets = getNumPalets();
            var html = '';
            for(var i=0; i<numPalets; i++) {
                html += '<option value="'+i+'">Palet '+(i+1)+'</option>';
            }
            return html;
        }

        function getBultoRow(bulto, idBulto) {
            var html = '<tr><td class="bulto">';
            for(var i=0; i<bulto.lineas.length; i++) {
                html += generateHtmlLine(bulto.lineas[i], idBulto, i);
            }
            html += generateNumBultosHtml(bulto, idBulto);
            html += generatePaletsSelector(idBulto);
            html += '</td></tr>';
            return html;

        }

        function drawTableBultos() {
            $('#tablaBultos').html('');
            for(var i=0; i<bultos.length; i++) {
                $('#tablaBultos').append(getBultoRow(bultos[i], i));
            }
        }

        function getUdsEnBultos(articulo) {
            var uds = 0;
            for(var i=0; i<bultos.length; i++) {
                var bulto = bultos[i];
                for(var j=0; j<bulto.lineas.length; j++) {
                    if(bulto.lineas[j].codart == articulo) {
                        uds += bulto.lineas[j].cantid * bulto.cantidad;
                    }
                }
            }
            return uds;
        }

        function getMaxUds(linea) {
            var udsEnBultos = getUdsEnBultos(linea.codart);
            var udsLinea = linea.cantid;
            return udsLinea-udsEnBultos;
        }

        function updateCantidadesLineas() {
            for(var i=0; i<lineasAlbaran.length; i++) {
                var maxUds = getMaxUds(lineasAlbaran[i]);
                $('#maxUds_'+i).text(maxUds);
                $('input[name="cantidad_bulto['+i+']"]').val(maxUds);
                $('input[name="linea_albaran['+i+']"]').prop('disabled', false);
                $('input[name="linea_albaran['+i+']"]').prop('checked', false);
                if(maxUds <= 0) {
                    disableCheckbox(i);
                }
            }
        }

        function disableCheckbox(id) {
            $('input[name="linea_albaran['+id+']"]').prop('disabled', true);
        }

        function addBultoToPalet(idBulto, idPalet, cantidad) {
            bultos[idBulto].cantidad = cantidad;
            palets[idPalet].bultos.push(bultos[idBulto]);
        }

        function initializePalets() {
            palets = new Array(getNumPalets());
            for(var i=0 ; i<getNumPalets(); i++) {
                palets[i]={
                    bultos:[]
                };
            }
        }

        function generateLineaBultoPaletHtml(linea) {
            return '<div>'+linea.codart+' -> '+linea.cantid+'uds.</div>';
        }

        function generateBultoPaletHtml(bulto) {
            var lineasHtml = '';
            for(var i=0; i<bulto.lineas.length; i++) {
                lineasHtml += generateLineaBultoPaletHtml(bulto.lineas[i]);
            }

            return '<div class="bulto"> '+bulto.cantidad+'x '+lineasHtml+'</div>';
        }

        function generateRowPalet(palet, idPalet) {
            var bultosHtml = '';
            for(var i=0; i<palet.bultos.length; i++) {
                bultosHtml += generateBultoPaletHtml(palet.bultos[i]);
            }

            return '<tr class="palet"><td> <strong>Palet '+idPalet+'</strong> '+bultosHtml+'</td></tr>';
        }

        function generatePaletsRowsHtml() {
            var paletsHtml = '';
            for(var i=0; i<palets.length; i++) {
                paletsHtml += generateRowPalet(palets[i], i+1);
            }
            return paletsHtml;
        }

        function drawPalets() {
            $('#tablaPalets').html(generatePaletsRowsHtml());
        }

        function finishEdi() {
            var paletsJson = JSON.stringify(palets);
            var parameters = {
                'data':paletsJson, 'ejerci':'{{$ejerci}}', 'codcli':'{{$codcli}}', 'codAlbaran':'{{$codAlbaran}}'
            };


            parameters.seralb='';
            @if(isset($seralb))
                parameters.seralb = '{{$seralb}}';
            @endif


            $('#finishEdi').prop('disabled', true);
            $('#finishEdi').text('Exportando...');

            $.post(
                    '{{url('app/edi/picking')}}', parameters,
                    function(result) {
                        alert("Exportado con éxito");
                        $('#finishEdi').text('Exportado!');
                        var ejerci = parameters.ejerci;
                        ejerci = ejerci.toString().slice(-2);
                        $('#finishEdi').after('<a target="_blank" href="{{url('app/edi/albaran-pdf')}}/' + parameters.codcli + '/' + parameters.ejerci + '/' + parameters.seralb + ejerci + parameters.codAlbaran + '" class="btn btn-primary">Albarán físico</a>');


                        $(document).on('click', '#finishEdi', function() {

                        });

                    }
            ).fail(function() {
                alert("Ha habido algún error. Consultar a Álvaro.");
            })
        }

        function deleteBulto(idBulto) {
            bultos.splice(idBulto, 1);
        }

        function deleteLineFromBulto(idBulto, idLinea) {
            bultos[idBulto].lineas.splice(idLinea, 1);
            if(bultos[idBulto].lineas.length == 0) {
                deleteBulto(idBulto);
            }

            updateCantidadesLineas();
            drawTableBultos();
        }


        $(function() {

            $(document).on('click','#deleteLineFromBulto', function() {
                var idBulto = $(this).attr('data-bulto-id');
                var lineaId = $(this).attr('data-linea-id');
                deleteLineFromBulto(idBulto, lineaId);
            });

            $('#addBulto').click(function() {
                addBulto();
                drawTableBultos();
                updateCantidadesLineas();
            });


            $(document).on('click', '#addToPalet', function() {
                var idBulto = $(this).attr('data-id');
                var idPalet = $('select[name="paletSelector['+idBulto+']"]').val();
                var numBultos = $('input[name="cantidadBulto['+idBulto+']"]').val();
                addBultoToPalet(idBulto, idPalet, numBultos);
                drawPalets();
                updateCantidadesLineas();

            });

            $(document).on('click', '#finishEdi', function() {
                finishEdi();
            });

            initializePalets();


        })
    </script>
@endsection