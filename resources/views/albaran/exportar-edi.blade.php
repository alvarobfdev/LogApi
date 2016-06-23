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

            <div id="capaMontarPalets" style="display: none;" class="col-md-6 col-sm-6">

                <div class="form-group">
                    <label class="col-md-6 control-label">Línea:</label>
                    <div class="col-md-6">
                        <div class="col-md-10 col-sm-10 no-padding">
                            <span class="form-control" id="lineaAlbaran" style="height: inherit;"></span>
                        </div>
                        <div class="col-md-2 col-sm-2 no-padding">
                            <img id="lineUp" style="max-width: 50%; cursor:pointer; " src="{{url('/logival/img/arrow-up-2.png')}}"><br><br>
                            <img id="lineDown" style="max-width: 50%; cursor: pointer;" src="{{url('/logival/img/arrow-down-2.png')}}">
                        </div>
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

                <div class="form-group">
                    <label class="col-md-6 control-label">Bultos x Capa:</label>
                    <div class="col-md-6">
                        <input type="number" id="bultosCapa" min="1" class="form-control input-md">
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

            <div id="capaVisorPalets" style="display: none;" class="col-md-6 col-sm-6">

            </div>

        </fieldset>
    </form>
@section('scripts')
    <script>

        String.prototype.hashCode = function() {
            var hash = 0, i, chr, len;
            if (this.length === 0) return hash;
            for (i = 0, len = this.length; i < len; i++) {
                chr   = this.charCodeAt(i);
                hash  = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return hash;
        };

        var formType = 'start';
        var albaran = null;
        var linAlbaran = null;
        var products = null;
        var maxBultos = 0;
        var selectedLinea = 0;
        var palets = [];
        var bultosCapas = [];
        var tipoPalets = [];
        var tiendasList = [];
        var locs = [];
        var bultosTiendas = [];
        var tiendas = true;
        var modify = false;
        var local_storage = {};
        var num_albaran = null;
        var ejercicio = null;
        var cliente = null;
        var datastring;

        $(function() {
            //startAlbaran($('form'));

            $('body').on('change', '#numTienda', function(e) {

                manejarLinea(selectedLinea);

            });

            $('body').on('click', '#lineUp', function(e) {

                if(selectedLinea > 0) {
                    manejarLinea(selectedLinea-1);
                }
            });

            $('body').on('click', '#lineDown', function(e) {
                if(selectedLinea < linAlbaran.length-1) {
                    manejarLinea(selectedLinea+1);
                }
            });

            $('body').on('click', '#resetLocal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var response = confirm("¿Quieres borrar los datos locales?");

                if (response == true) {
                    localStorage.removeItem("temp_data");
                    window.location.href = '{{url("/app/edi/exportar-edi")}}';
                }
            });

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
                var bultos_restantes = linAlbaran[selectedLinea].bultos;
                if(tiendas) {
                    selectedTienda = Number($(this).attr('data-tienda'));
                    palets[numPalet - 1][selectedLinea][selectedTienda] = numBultos;
                    for(var i = 0; i<palets[numPalet - 1][selectedLinea].length; i++) {
                        bultos_restantes -= palets[numPalet - 1][selectedLinea][i];
                    }
                }
                else  {
                    palets[numPalet - 1][selectedLinea] = numBultos;
                    bultos_restantes -= numBultos;
                }

                linAlbaran[selectedLinea].bultos_restantes = bultos_restantes;

                if(bultos_restantes > 0)
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
            datastring = form.serialize();

            form.find("#comenzar").prop("disabled", true);
            form.find("#comenzar").text("Obteniendo albarán...");

            $.getJSON('{{url('app/edi/albaran-for-edi')}}', datastring, function(result) {

                ejercicio = $('#ejercicio').val();
                cliente = $('#numCliente').val();
                num_albaran = $('#numAlbaran').val();

                var json = result;

                if(json.success == false) {

                    if(json.hasOwnProperty('error')) {
                        alert(json.error);

                    }
                    else alert("Ha habido un fallo al obtener los datos!");

                    resetButton();
                    return;
                }
                if(json.data.albaran == null) {
                    alert("No existe ningún albarán de salida con estos datos!");
                    resetButton();
                    return;
                }
                //localStorage.removeItem("temp_data");
                var temp_data = localStorage.getItem("temp_data");
                if(temp_data !== undefined && temp_data != null) {

                    var hash = datastring.hashCode();

                    var tempParsed = $.parseJSON(temp_data);
                    console.log(tempParsed);
                    if(tempParsed.hash == hash) {
                        albaran = tempParsed.albaran;
                        linAlbaran = tempParsed.linAlbaran;
                        products = json.data.products;
                        locs = json.data.locs;
                        tipoPalets = tempParsed.tipoPalets;
                        tiendasList = tempParsed.tiendasList;


                        if (tiendasList.length == 0) {
                            tiendas = false;
                        }

                        construirPalets();
                        palets = tempParsed.palets;
                        bultosCapas = tempParsed.bultosCapas;
                        bultosTiendas = tempParsed.bultosTiendas;



                        $('#capaBusquedaAlbaran').hide();
                        $('#capaMontarPalets').show();
                        $('#capaVisorPalets').show();

                        manejarLinea(0);
                        reloadVisorPalets();
                        formType = 'addToPalet';

                        return;
                    }
                }

                if (json.modify) {
                    albaran = json.data.albaran;
                    linAlbaran = json.data.lin_albaran;
                    products = json.data.products;
                    maxBultos = 0;
                    selectedLinea = 0;
                    tipoPalets = json.data.tipoPalets;
                    tiendasList = json.data.tiendasList;


                    if (tiendasList.length == 0) {
                        tiendas = false;
                    }

                    modify = true;

                    construirPalets();
                    palets = json.data.palets;
                    bultosCapas = json.data.bultosCapas;


                    $('#capaBusquedaAlbaran').hide();
                    $('#capaMontarPalets').show();
                    $('#capaVisorPalets').show();

                    manejarLinea(0);
                    reloadVisorPalets();
                    $('#addToPalet').after(' <a target="_blank" href="{{url('app/edi/albaran-pdf')}}/'+cliente+'/'+ejercicio+'/'+num_albaran+'" class="btn btn-primary">Albarán físico</a>');
                    return;
                }

                if (json.data.albaran.totpal < 1) {
                    alert("No se han asignado palets a este albarán!");
                    resetButton();
                    return;
                }

                albaran = json.data.albaran;
                linAlbaran = json.data.lin_albaran;
                products = json.data.products;
                tiendasList = json.data.tiendasList;
                locs = json.data.locs;

                if (tiendasList.length == 0) {
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
            bultosCapas = new Array(albaran.totpal);
            bultosTiendas = new Array(tiendasList.length);

            if(tiendas) {
                for (var iTienda = 0; iTienda < tiendasList.length; iTienda++) {
                    $('#numTienda').append($('<option>', {
                        value: iTienda,
                        text: tiendasList[iTienda].cod_interno + " - " + tiendasList[iTienda].nombre
                    }));

                    bultosTiendas[iTienda] = new Array(linAlbaran.length);

                    for(var iLinea = 0; iLinea < linAlbaran.length; iLinea++) {
                        var product = getProductFromLinea(linAlbaran[iLinea]);
                        var loc = getCurrentLoc(product, tiendasList[iTienda]);
                        var cantidad = 0;
                        if(loc != null) {
                            var udsBulto = linAlbaran[iLinea].cantid / linAlbaran[iLinea].bultos;
                            var cantidad = loc.cantidad / udsBulto;
                        }

                        bultosTiendas[iTienda][iLinea] = cantidad;
                    }

                }

            }

            for(var i=0; i<albaran.totpal; i++) {
                $('#numPalet').append($('<option>', {
                    value: i + 1,
                    text: 'Palet ' + (i + 1)
                }));

                palets[i] = new Array(linAlbaran.length);
                bultosCapas[i] = new Array(linAlbaran.length);
                tipoPalets[i] = 201;
                for(var j=0; j<linAlbaran.length; j++) {
                    if(!modify)
                        linAlbaran[j].bultos_restantes = linAlbaran[j].bultos;
                    if(!tiendas)
                        palets[i][j] = 0;
                    else {
                        palets[i][j] = new Array(tiendasList.length);
                        for(var iTienda=0; iTienda < tiendasList.length; iTienda++) {
                            palets[i][j][iTienda] = 0;
                        }
                    }
                    bultosCapas[i][j] = 0;
                }
            }
        }

        function manejarLinea(numLinea) {

            if(numLinea < linAlbaran.length) {
                selectedLinea = numLinea;
                var linea = linAlbaran[numLinea];
                if(linea.bultos_restantes == 0) {
                    manejarLinea(numLinea+1);
                    return;
                }
                maxBultos = linea.bultos_restantes;

                $('#lineaAlbaran').text(linea.codart + " | "+linea.descri + " | "+linea.horizo + "-" + linea.vertic +
                        " (Quedan "+ (maxBultos) +" bultos)");

                $('#numBultos').prop("max", maxBultos);
                $('#numBultos').val(maxBultos);


                if(tiendas == true) {

                    var numTienda = Number($("#numTienda").val());
                    if(bultosTiendas[numTienda][numLinea] <= 0) {
                        var nextLine = getNextNotEmptyShopLine(bultosTiendas[numTienda]);
                        if(nextLine != -1) {
                            manejarLinea(nextLine);
                            return;
                        }
                        else {
                            $("#numTienda").val(numTienda+1);
                            return;
                        }
                    }

                    if(modify == false) {

                        /*var articulo = getCurrentArticle();
                         var idTienda = $('#numTienda').val();
                         var tienda = tiendasList[idTienda];
                         var loc = getCurrentLoc(articulo, tienda);
                         var cantidad = 0;
                         if (loc != null) {
                         var udsBulto = linAlbaran[selectedLinea].cantid / linAlbaran[selectedLinea].bultos;
                         cantidad = loc.cantidad / udsBulto;
                         }*/

                        var cantidad = bultosTiendas[numTienda][numLinea];

                        $('#numBultos').prop("max", cantidad);
                        $('#numBultos').val(cantidad);
                    }
                }
            }
            else {
                var nextNotEmptyLine = getNextNotEmptyLine();
                if(nextNotEmptyLine == -1) {
                    formType = "endEdi";
                    $('#addToPalet').text("Finalizar exportación");
                    $('#lineaAlbaran').text("");
                }
                else {
                    manejarLinea(nextNotEmptyLine);
                }
            }

        }

        function getNextNotEmptyLine() {
            for(var i=0; i<linAlbaran.length; i++) {
                if(linAlbaran[i].bultos_restantes > 0) {
                    return i;
                }
            }
            return -1;
        }

        function finalizarEdi() {

            /*
             $("button").prop("disabled", true);
             $("#addToPalet").text("Exportando...");
             $.post('{{url('app/edi/finish-export-edi')}}', {
             'albaran': JSON.stringify(albaran),
             'palets':JSON.stringify(palets),
             'tipoPalets':JSON.stringify(tipoPalets),
             'tiendasList':JSON.stringify(tiendasList),
             'lineas':JSON.stringify(linAlbaran),
             'bultosCapas':JSON.stringify(bultosCapas),
             'modify':modify,
             '_token':'{{ csrf_token() }}'

             }, function() {
             alert("Fichero exportado con éxito!");
             window.location.reload();
             }, "json").error(function() {
             alert("Fallo al exportar fichero. Consulte a un técnico");
             });
             */

            $('#addToPalet').text("Exportando EDI...")
            $('#addToPalet').addClass("disabled");

            var request = $.ajax({
                url: '{{url('app/edi/finish-export-edi')}}',
                type: 'post',
                data: {
                    'albaran': JSON.stringify(albaran),
                    'palets':JSON.stringify(palets),
                    'tipoPalets':JSON.stringify(tipoPalets),
                    'tiendasList':JSON.stringify(tiendasList),
                    'lineas':JSON.stringify(linAlbaran),
                    'bultosCapas':JSON.stringify(bultosCapas),
                    'modify':modify,
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',   //If your header name has spaces or any other char not appropriate
                },
                dataType: 'json'
            });

            request.done(function() {
                alert("Fichero exportado con éxito!");
                $('#addToPalet').text("Fichero exportado!");
                $('#addToPalet').after(' <a target="_blank" href="{{url('app/edi/albaran-pdf')}}/'+cliente+'/'+ejercicio+'/'+num_albaran+'" class="btn btn-primary">Albarán físico</a>');
                localStorage.removeItem("temp_data");
                //window.location.reload();
            });

            request.fail(function() {
                alert("Fallo al exportar fichero. Consulte a un técnico");
                $('#addToPalet').text("Finalizar exportación");
                $('#addToPalet').removeClass("disabled");

            });
        }

        function getProductFromLinea(linea) {
            for(var i=0; i<products.length; i++) {
                if(linea.codart == products[i].codart) {
                    return products[i];
                }
            }

            return null;
        }


        function getCurrentArticle() {
            for(var i=0; i < products.length; i++) {
                codart = linAlbaran[selectedLinea].codart;
                if(products[i].codart == codart) {
                    return products[i];
                }
            }
            return null;
        }

        function getCurrentLoc(articulo, tienda) {
            for(var i=0; i<locs.length; i++) {
                if(locs[i].prod == articulo.codbar && locs[i].lugar == tienda.ean) {
                    return locs[i];
                }
            }
            return null;
        }


        function addToPalet() {
            numPalet = $('#numPalet').val();
            numBultos = $('#numBultos').val();
            tipoPalet = $('#tipoPalet').val();
            numTienda = $('#numTienda').val();
            bultosXcapa = $('#bultosCapa').val();

            numTienda = Number(numTienda);

            tipoPalets[numPalet-1] = tipoPalet;

            bultosCapas[numPalet - 1][selectedLinea] = bultosXcapa;

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



            linAlbaran[selectedLinea].bultos_restantes = maxBultos - numBultos;

            if(modify == false)
                saveToLocal();

            if(tiendas) {
                bultosTiendas[numTienda][selectedLinea] -= Number(numBultos);
                if(bultosTiendas[numTienda][selectedLinea] <= 0) {
                    var nextNotEmptyShopLine = getNextNotEmptyShopLine(bultosTiendas[numTienda]);
                    if(nextNotEmptyShopLine != -1) {
                        manejarLinea(nextNotEmptyShopLine);
                        if(modify == false)
                            saveToLocal();
                        return;
                    }
                    else {

                        if(numTienda+1 <= tiendasList.length-1) {
                            $('#numTienda').val(numTienda+1);
                            manejarLinea(selectedLinea);
                            if(modify == false)
                                saveToLocal();
                            return;
                        }
                    }
                }
            }



            if(numBultos < maxBultos) {
                manejarLinea(selectedLinea);
            }

            else {
                manejarLinea(selectedLinea+1)
            }
        }

        function getNextNotEmptyShopLine(bultosTienda) {

            for(var i=0; i<bultosTienda.length; i++) {
                if(bultosTienda[i] > 0) {
                    return i;
                }
            }
            return -1;
        }

        function saveToLocal() {

            var hash = datastring.hashCode();

            local_storage.hash = hash;
            local_storage.albaran = albaran;
            local_storage.palets = palets;
            local_storage.tipoPalets = tipoPalets;
            local_storage.tiendasList = tiendasList;
            local_storage.linAlbaran = linAlbaran;
            local_storage.bultosCapas = bultosCapas;
            local_storage.bultosTiendas = bultosTiendas;
            localStorage.setItem("temp_data", JSON.stringify(local_storage));

        }


        function reloadVisorPalets() {
            $('#capaVisorPalets').html('');

            for(var i=0; i<palets.length; i++) {
                var html = '<div class="palet"><span><strong>Palet '+(i+1)+'</strong></span>';
                $.each(palets[i], function(index, value) {
                    var numBultosCapa = bultosCapas[i][index];

                    var numLinea = index;
                    var linea = linAlbaran[numLinea];

                    var htmlBultosCapa = '<div class="bultoCapa">\
                                    <span>Bultos/capa  x </span><input type="number" min="0" max="' + numBultosCapa + '" value="' + numBultosCapa + '">\
                                     <button data-palet="' + (i + 1) + '" data-linea="' + numLinea + '" class="btn btn-primary modificarBultosCapa">Modificar</button></div>';



                    if(!tiendas) {

                        if (value > 0) {

                            html += '<div class="bulto">\
                                    <span>' + linea.codart + ' | ' + linea.descri + '  x </span><input type="number" min="0" max="' + value + '" value="' + value + '">\
                                     <button data-palet="' + (i + 1) + '" data-linea="' + numLinea + '" class="btn btn-primary modificarCantidad">Modificar</button>';

                            html += htmlBultosCapa;


                            html += '</div>';
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


                            html += htmlBultosCapa;
                            html += htmlTiendas;

                            html += '</div>';
                        }
                    }
                });
                html += '</div>';

                $('#capaVisorPalets').append(html);
            }

            $('#capaVisorPalets').append('<br><button id="resetLocal" class="btn btn-danger">Resetear</button>');

        }


    </script>
@endsection
@endsection