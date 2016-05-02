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
                <label class="col-md-4 control-label" for="numAlbaran">Núm. Albaran</label>
                <div class="col-md-4">
                    <input id="numAlbaran" name="numAlbaran" type="text" placeholder="Inserte número albarán" class="form-control input-md" required="" value="1">

                </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-4 control-label" for="numCamiones">Nº de camiones</label>
                <div class="col-md-4">
                    <input id="numCamiones" name="numCamiones" type="text" placeholder="Inserte cantidad de camiones" class="form-control input-md" required="" value="1">
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

        <div id="capaMontarPalets" style="display: none;" class="col-md-6">

            <div class="form-group">
                <label class="col-md-6 control-label">Línea:</label>
                <div class="col-md-6">
                    <span class="form-control" id="lineaAlbaran" style="height: inherit;"></span>
                </div>
            </div>


            <div class="form-group">
                <label class="col-md-6 control-label">Palet:</label>
                <div class="col-md-6">
                    <select id="numPalet" class="form-control input-md">

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-6 control-label">Tipo de palet:</label>
                <div class="col-md-6">
                    <select id="tipoPalet" class="form-control input-md">
                        <option value="201">Palet Europeo (80 x 120 cm)</option>
                        <option value="200">Diplay Palet (80 x 60 cm)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-6 control-label">Tienda:</label>
                <div class="col-md-6">
                    <select id="numTienda" class="form-control input-md">

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-6 control-label">Número bultos:</label>
                <div class="col-md-6">
                    <input type="number" id="numBultos" value="5" max="10" min="1" class="form-control input-md">
                </div>
            </div>



            <!-- Button -->
            <div class="form-group">
                <label class="col-md-6 control-label" for="addToPalet"></label>
                <div class="col-md-6">
                    <button id="addToPalet" name="addToPalet" class="btn btn-primary">Añadir al palet</button>
                </div>
            </div>
        </div>

        <div id="capaVisorPalets" style="display: none;" class="col-md-6">

        </div>

    </fieldset>
</form>
    @section('scripts')
    <script>
        var formType = 'start';
        var albaran = null;
        var lin_albaran = null;
        var products = null;
        var maxBultos = 0;
        var selectedLinea = 0;
        var palets = [];
        var tipoPalets = [];
        var tiendasList = []
        var tiendas = true;

        $(function() {
            //startAlbaran($('form'));

            $('body').on('click', '.modificarCantidad', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if(formType == 'endEdi') {
                    formType = 'addToPalet';
                    $('#addToPalet').text("Añadir al palet");

                }

                var numBultos = Number($(this).prev('input').val());
                var numPalet = Number($(this).attr('data-palet'));
                selectedLinea = Number($(this).attr('data-linea'));
                var selectedTienda = "";
                var bultosRestantes = lin_albaran[selectedLinea].bultos;
                if(tiendas) {
                    selectedTienda = Number($(this).attr('data-tienda'));
                    palets[numPalet - 1][selectedLinea][selectedTienda] = numBultos;
                    for(var i = 0; i<palets[numPalet - 1][selectedLinea].length; i++) {
                        bultosRestantes -= palets[numPalet - 1][selectedLinea][i];
                    }
                }
                else  {
                    palets[numPalet - 1][selectedLinea] = numBultos;
                    bultosRestantes -= numBultos;
                }

                lin_albaran[selectedLinea].bultosRestantes = bultosRestantes;

                if(bultosRestantes > 0)
                    manejarLinea(selectedLinea);
                else manejarLinea(selectedLinea+1);
            });

            $('form').submit(function(e) {
                e.preventDefault();
                if(formType == 'start') {
                    startAlbaran($(this));
                }
                else if(formType == 'addToPalet') {
                    addToPalet();
                }

                else if(formType == 'endEdi') {

                    finalizarEdi();
                }
            });
        });

        function resetButton() {
            $("#comenzar").prop("disabled", false);
            $("#comenzar").text("Comenzar");
        }
        function startAlbaran(form) {
            var datastring = form.serialize();
            form.find("#comenzar").prop("disabled", true);
            form.find("#comenzar").text("Obteniendo albarán...");

            $.getJSON('{{url('app/edi/albaran-for-edi')}}', datastring, function(result) {
                var json = result;

                if(json.success == false) {
                    alert("Ha habido un fallo al obtener los datos!");
                    resetButton();
                    return;
                }
                if(json.data.albaran == null) {
                    alert("No existe ningún albarán de salida con estos datos!");
                    resetButton();
                    return;
                }

                if(json.data.albaran.totpal < 1) {
                    alert("No se han asignado palets a este albarán!");
                    resetButton();
                    return;
                }

                albaran = json.data.albaran;
                lin_albaran = json.data.lin_albaran;
                products = json.data.products;
                tiendasList = json.data.tiendasList;

                if(tiendasList.length == 0) {
                    tiendas = false;
                }

                buildFormPalet(form);

                formType = 'addToPalet';


            }).fail(function( jqxhr, textStatus, error ) {
                var err = jqxhr.status + ", " + error;
                alert("Error: "+err+".\nComprobar conexión de las máquinas.");
            });
        }

        function buildFormPalet(form) {
            $('#capaBusquedaAlbaran').hide();
            $('#capaMontarPalets').show();
            $('#capaVisorPalets').show();


            construirPalets();
            manejarLinea(0, 0);
        }

        function construirPalets() {
            palets = new Array(albaran.totpal);

            if(tiendas)
                for(var iTienda=0; iTienda < tiendasList.length; iTienda++) {
                    $('#numTienda').append($('<option>', {
                        value: iTienda,
                        text: tiendasList[iTienda].cod_interno + " - "+ tiendasList[iTienda].nombre
                    }));
                }

            for(var i=0; i<albaran.totpal; i++) {
                $('#numPalet').append($('<option>', {
                    value: i + 1,
                    text: 'Palet ' + (i + 1)
                }));

                palets[i] = new Array(lin_albaran.length);
                tipoPalets[i] = 201;
                for(var j=0; j<lin_albaran.length; j++) {
                    lin_albaran[j].bultosRestantes = lin_albaran[j].bultos;
                    if(!tiendas)
                        palets[i][j] = 0;
                    else {
                        palets[i][j] = new Array(tiendasList.length);
                        for(var iTienda=0; iTienda < tiendasList.length; iTienda++) {
                            palets[i][j][iTienda] = 0;
                        }
                    }
                }
            }
        }

        function manejarLinea(numLinea) {

            if(numLinea < lin_albaran.length) {
                selectedLinea = numLinea;
                var linea = lin_albaran[numLinea];
                if(linea.bultosRestantes == 0) {
                    manejarLinea(numLinea+1);
                    return;
                }
                maxBultos = linea.bultosRestantes;

                $('#lineaAlbaran').text(linea.codart + " | "+linea.descri + " | "+linea.horizo + "-" + linea.vertic +
                        " (Quedan "+ (maxBultos) +" bultos)");

                $('#numBultos').prop("max", maxBultos);
                $('#numBultos').val(maxBultos);
            }
            else {
                formType = "endEdi";
                $('#addToPalet').text("Finalizar exportación");
                $('#lineaAlbaran').text("");
            }
        }

        function finalizarEdi() {
            $("button").prop("disabled", true);
            $("#addToPalet").text("Exportando...");
            $.getJSON('{{url('app/edi/finish-export-edi')}}', {
                'albaran': JSON.stringify(albaran),
                'palets':JSON.stringify(palets),
                'tipoPalets':JSON.stringify(tipoPalets),
                'tiendasList':JSON.stringify(tiendasList),
                'lineas':JSON.stringify(lin_albaran)
            }, function() {
                alert("Fichero exportado con éxito!");
                window.location.reload();
            }).error(function() {
                alert("Fallo al exportar fichero. Consulte a un técnico");
            });
        }




        function addToPalet() {
            numPalet = $('#numPalet').val();
            numBultos = $('#numBultos').val();
            tipoPalet = $('#tipoPalet').val();
            numTienda = $('#numTienda').val();

            tipoPalets[numPalet-1] = tipoPalet;

            if($.isNumeric(numBultos) && numBultos <= maxBultos) {
                if(!tiendas) {
                    palets[numPalet - 1][selectedLinea] += Number(numBultos);
                }
                else {
                    palets[numPalet-1][selectedLinea][numTienda] += Number(numBultos);
                }
                reloadVisorPalets();
            }

            else {
                alert("El número de bultos es incorrecto");
                return;
            }

            lin_albaran[selectedLinea].bultosRestantes = maxBultos - numBultos;

            if(numBultos < maxBultos) {
                manejarLinea(selectedLinea);
            }

            else {
                manejarLinea(selectedLinea+1)
            }
        }

        function reloadVisorPalets() {
            $('#capaVisorPalets').html('');

            for(var i=0; i<palets.length; i++) {
                var html = '<div class="palet"><span><strong>Palet '+(i+1)+'</strong></span>';
                $.each(palets[i], function(index, value) {

                    var numLinea = index;
                    var linea = lin_albaran[numLinea];
                    if(!tiendas) {

                        if (value > 0) {

                            html += '<div class="bulto">\
                                    <span>' + linea.codart + ' | ' + linea.descri + '  x </span><input type="number" min="0" max="' + value + '" value="' + value + '">\
                                     <button data-palet="' + (i + 1) + '" data-linea="' + numLinea + '" class="btn btn-primary modificarCantidad">Modificar</button></div>';
                        }
                    }
                    else {

                        var htmlTiendas = "";
                        var addBulto = false;
                        $.each(value, function(iTienda, bultos) {

                            var cod_interno = tiendasList[iTienda].cod_interno;

                            if(bultos > 0) {
                                htmlTiendas += '<div class="tienda">\
                                        <span>' + cod_interno + '  x </span><input type="number" min="0" max="' + bultos + '" value="' + bultos + '">\
                                         <button data-tienda="' + iTienda + '" data-palet="' + (i + 1) + '" data-linea="' + numLinea + '" class="btn btn-primary modificarCantidad">Modificar</button></div>';
                                addBulto = true;
                            }
                        });

                        if(addBulto) {
                            html += '<div class="bulto"><span>' + linea.codart + ' | ' + linea.descri + '</span>';
                            html += htmlTiendas;
                            html += '</div>';
                        }
                    }
                });
                html += '</div>';
                $('#capaVisorPalets').append(html);
            }
        }


    </script>
    @endsection
@endsection