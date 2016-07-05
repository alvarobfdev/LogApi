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

                <div id="capaTiendasRestantes" style="display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Tiendas con bultos restantes</h6>
                        </div>
                    </div>
                    <div id="tiendasRestantes">

                    </div>
                </div>
            </div>

            <div id="capaVisorPalets" style="display: none;" class="col-md-6 col-sm-6">

            </div>

        </fieldset>
    </form>
@section('scripts')
    <script>

        var datastring;
        var formType = 'start';

        var submitButton;
        var spanLine;
        var selectPalets;
        var selectTiendas;
        var inputNumBultos;
        var inputBultosCapas;
        var btnAddToPalet;
        var selectTipoPalets;

        var lineasAlbaran = [];
        var tipoPalets = [];
        var palets = [];
        var tiendas = [];
        var bultosTiendas = [];
        var locs = [];
        var bultosCapas = [];
        var selectedLine;
        var selectedTienda;
        var selectedPalet;
        var hasTiendas = false;
        var modify = false;

        var albaran;
        var hashLocalData;

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

        $('form').submit(function(e) {
            e.preventDefault();
            e.stopPropagation();

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

        function finalizarEdi() {

            btnAddToPalet.text("Exportando EDI...")
            btnAddToPalet.addClass("disabled");

            var request = $.ajax({
                url: '{{url('app/edi/finish-export-edi')}}',
                type: 'post',
                data: {
                    'albaran': JSON.stringify(albaran),
                    'palets':JSON.stringify(palets),
                    'tipoPalets':JSON.stringify(tipoPalets),
                    'tiendasList':JSON.stringify(tiendas),
                    'lineas':JSON.stringify(lineasAlbaran),
                    'bultosCapas':JSON.stringify(bultosCapas),
                    'locs':JSON.stringify(locs),
                    'modify':modify,
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',   //If your header name has spaces or any other char not appropriate
                },
                dataType: 'json'
            });

            request.done(function() {
                alert("Fichero exportado con éxito!");
                btnAddToPalet.text("Fichero exportado!");
                if(albaran.seralb == " ") {
                    albaran.seralb = "";
                }
                if(!modify) {
                    btnAddToPalet.after(' <a target="_blank" href="{{url('app/edi/albaran-pdf')}}/'+albaran.codcli+'/'+albaran.ejerci+'/'+albaran.seralb+albaran.ejerci+albaran.codcli+albaran.numalb+'" class="btn btn-primary">Albarán físico</a>');
                    btnAddToPalet.after(' <a target="_blank" href="{{url('app/edi/estructura-etiquetado-eci')}}/'+albaran.codcli+'/'+albaran.ejerci+'/'+albaran.seralb+albaran.ejerci+albaran.codcli+albaran.numalb+'" class="btn btn-primary">Matrículas etiquetas ECI</a>');

                }
                localStorage.removeItem("localData"+hashLocalData);
                //window.location.reload();
            });

            request.fail(function() {
                alert("Fallo al exportar fichero. Consulte a un técnico");
                btnAddToPalet.text("Finalizar exportación");
                btnAddToPalet.removeClass("disabled");

            });

        }

        function refreshTiendasRestantes() {
            var html = buildTiendasRestantes();
            $('#tiendasRestantes').html(html);
            $('#capaTiendasRestantes').show();
        }

        function buildTiendasRestantes() {
            var html = ""
            for(var i=0; i<lineasAlbaran.length; i++) {
                var infoBulto = false;
                var htmlTiendas = "";

                for(var j=0; j<tiendas.length; j++) {
                    if(bultosTiendas[j][i] > 0) {
                        infoBulto = true;
                        htmlTiendas += '<div class="tienda"><span>'+tiendas[j].cod_interno+' x '+bultosTiendas[j][i]+'</span></div>'
                    }
                }
                if(infoBulto) {
                    var linea = lineasAlbaran[i];
                    html += '<div class="bulto">\
                                        <span>' + linea.codart + ' | ' + linea.descri + '  x </span>\
                                        '+htmlTiendas+'\
                                 </div>';
                }
            }
            return html;
        }

        function refreshVisor() {
            var html = buildPaletsVisor();
            $('#capaVisorPalets').html(html);
        }

        function buildPaletsVisor() {
            var html = '';
            for(var i=0; i<palets.length; i++) {
                html += '<div class="palet"><span><strong>Palet '+(i+1)+'</strong></span>'+buildPaletsContent(i)+'</div>';
            }

            html += '<br><button id="resetLocal" class="btn btn-danger">Resetear</button>';

            return html;
        }

        function buildPaletsContent(paletIndex) {
            var html = '';
            for(var i=0; i<palets[paletIndex].length; i++) {
                var numBultosCapa = bultosCapas[paletIndex][i];
                if(numBultosCapa>0) {
                    html += '<div class="bultoCapa">\
                                    <span>Bultos/capa  x </span><input type="number" min="0" max="' + numBultosCapa + '" value="' + numBultosCapa + '">\
                                     <button data-palet="' + paletIndex + '" data-linea="' + i + '" class="btn btn-primary modificarBultosCapa">Modificar</button></div>';
                }
                if(hasTiendas) {
                    html += buildTiendasContent(paletIndex, i);
                }
                else {
                    html += buildBultosContent(paletIndex, i);

                }
            }
            return html;
        }

        function buildBultosContent(paletIndex, lineIndex) {

            var numBultos = palets[paletIndex][lineIndex];
            var html = '';

            if(numBultos > 0) {
                var linea = lineasAlbaran[lineIndex];
                var html = '<div class="bulto">\
                                        <span>' + linea.codart + ' | ' + linea.descri + '  x </span><input type="number" min="0" max="' + numBultos + '" value="' + numBultos + '">\
                                         <button data-palet="' + paletIndex + '" data-linea="' + lineIndex + '" class="btn btn-primary modificarCantidad">Modificar</button></div>';
            }

            return html;
        }


        function buildTiendasContent(paletIndex, lineIndex) {
            var html = '';
            var addArticleInfo = false;

            for(var i=0; i<palets[paletIndex][lineIndex].length; i++) {

                var numBultosTienda = palets[paletIndex][lineIndex][i];
                var tienda = tiendas[i];

                if(numBultosTienda > 0) {
                    var completed = "completed";
                    if(bultosTiendas[i][lineIndex] > 0) {
                        completed = "incomplete";
                    }
                    addArticleInfo = true;
                    html += '<div class="tienda '+completed+'">\
                    <span>' + tienda.cod_interno + '  x </span><input type="number" min="0" max="' + numBultosTienda + '" value="' + numBultosTienda + '">\
                    <button data-tienda="' + i + '" data-palet="' + paletIndex + '" data-linea="' + lineIndex + '" class="btn btn-primary modificarCantidad">Modificar</button></div>';
                }
            }

            if(addArticleInfo) {
                var linea = lineasAlbaran[lineIndex];
                html = '<div class="bulto"><span>' + linea.codart + ' | ' + linea.descri + '</span>' + html +'</div>';
            }



            return html;
        }

        function addToPalet() {
            var numBultos = Number(inputNumBultos.val());
            if(numBultos > 0) {
                lineasAlbaran[selectedLine].bultosRestantes -= numBultos;
                bultosCapas[selectedPalet][selectedLine] = Number(inputBultosCapas.val());
                tipoPalets[selectedPalet] = Number(selectTipoPalets.val());
                if (hasTiendas) {
                    addToPaletForTienda(numBultos);
                }
                else addToPaletDefault(numBultos);

                nextIteraction();

            }
        }

        function saveToLocal() {
            var local_object = {};
            local_object.lineasAlbaran = lineasAlbaran;
            local_object.tipoPalets = tipoPalets;
            local_object.palets = palets;
            local_object.bultosCapas = bultosCapas;
            local_object.tiendas = tiendas;
            local_object.locs = locs;
            local_object.hasTiendas = hasTiendas;
            local_object.bultosTiendas = bultosTiendas;
            local_object.selectedLine = selectedLine;
            local_object.selectedPalet = selectedPalet;
            local_object.selectedTienda = selectedTienda;
            localStorage.setItem("localData"+hashLocalData, JSON.stringify(local_object));
        }

        function nextIteraction() {
            refreshVisor();
            if(hasTiendas) {
                refreshTiendasRestantes();
                nextTiendaIteraction();
            }
            else {
                nextGeneralIteraction();
            }
            saveToLocal();
        }

        function nextGeneralIteraction() {
            if(!nextGeneralLine()) {
                finishForm();
            }
        }

        function nextGeneralLine() {
            var nextLine = nextGeneralLineIndex();

            if(nextLine != -1) {
                selectedLine = nextLine;
                loadLineHtml();
                setMaxBultosInput();
                return true;
            }
            return false;

        }

        function nextGeneralLineIndex() {

            for(var i=0; i < lineasAlbaran.length; i++) {
                if(lineasAlbaran[i].bultosRestantes > 0) {
                    return i;
                }
            }
            return -1;
        }

        function getAllBultosRestantes() {
            var bultosRestantes = 0;
            for(var i=0; i<lineasAlbaran.length; i++) {
                bultosRestantes += lineasAlbaran[i].bultosRestantes;
            }
            return bultosRestantes;
        }

        function nextTiendaIteraction() {

            if(getAllBultosRestantes() <= 0) {
                finishForm();
                return;
            }

            if(!nextLineIndex()) {
                if (!nextTiendaIndex()) {
                    finishForm();
                }
                else {
                    nextLineIndex();
                }
            }

        }

        function nextLineIndex() {
            var nextLine = nextTiendaLineIndex();

            if(nextLine != -1) {

                selectedLine = nextLine;
                loadLineHtml();
                setMaxBultosInput();
                return true;
            }
            return false;

        }

        function finishForm() {
            formType = "endEdi";
            btnAddToPalet.text("Finalizar exportación");
            spanLine.text("");
        }

        function resetForm() {
            formType = "addToPalet";
            btnAddToPalet.text("Añadir al Palet");
        }

        function nextTiendaIndex(tienda) {
            var nextTienda = nextTiendaIndexSearch();
            if(nextTienda != -1) {
                selectTiendas.val(nextTienda);
                selectedTienda = nextTienda;
                setMaxBultosInput();
                return true;
            }
            return false;

        }

        function nextTiendaIndexSearch() {
            for(var i=0; i < bultosTiendas.length; i++) {
                for(var j=0; j < bultosTiendas[i].length; j++) {
                    if(bultosTiendas[i][j] > 0) {
                        return i;
                    }
                }
            }
            return -1;
        }

        function nextTiendaLineIndex() {
            for(var i=0; i < bultosTiendas[selectedTienda].length; i++) {
                if(bultosTiendas[selectedTienda][i] > 0) {
                    return i;
                }
            }
            return -1;
        }

        function addToPaletDefault(numBultos) {

            palets[selectedPalet][selectedLine] += numBultos;

        }

        function addToPaletForTienda(numBultos) {

            palets[selectedPalet][selectedLine][selectedTienda] += numBultos;
            bultosTiendas[selectedTienda][selectedLine] -= numBultos;
        }

        function startAlbaran(form) {

            submitButton = $('#comenzar');
            spanLine = $('#lineaAlbaran');
            selectPalets = $('#numPalet');
            selectTiendas = $('#numTienda');
            inputNumBultos = $('#numBultos');
            inputBultosCapas = $('#bultosCapa');
            btnAddToPalet = $('#addToPalet');
            selectTipoPalets = $('#tipoPalet');

            submitButton.prop("disabled", true);
            submitButton.text("Obteniendo albarán...");
            datastring = form.serialize();

            $.getJSON('{{url('app/edi/albaran-for-edi')}}', datastring, function(result) {
                cargarExportador(result);
            });

        }

        function getHashLocalData() {
            var string = albaran.codcli+"-"+albaran.ejerci+"-"+albaran.numalb;
            return string.hashCode();
        }

        function cargarExportador(data) {
            if(showCargarExportadorErrors(data)) {
                resetSubmitButton();
                return;
            }
            albaran = data.data.albaran;
            hashLocalData = getHashLocalData();

            if(loadIfExported(data)) {
                return;
            }

            if(loadIfTempData()) {
                return;
            }

            loadDefault(data.data);
            loadHtml();
            nextIteraction();

        }

        function loadIfExported(data) {
            if(!data.modify)
                return false;
            else {
                if(albaran.seralb == " ") {
                    albaran.seralb = "";
                }
                modify = true;
                loadSavedData(data.data);
                loadHtml();
                btnAddToPalet.after(' <a target="_blank" href="{{url('app/edi/albaran-pdf')}}/'+albaran.codcli+'/'+albaran.ejerci+'/'+albaran.seralb+albaran.ejerci+albaran.codcli+albaran.numalb+'" class="btn btn-primary">Albarán físico</a>');
                btnAddToPalet.after(' <a target="_blank" href="{{url('app/edi/estructura-etiquetado-eci')}}/'+albaran.codcli+'/'+albaran.ejerci+'/'+albaran.seralb+albaran.ejerci+albaran.codcli+albaran.numalb+'" class="btn btn-primary">Matrículas etiquetas ECI</a>');

                refreshVisor();
                nextIteraction();
                return true;
            }
        }

        function loadSavedData(data) {
            lineasAlbaran = data.lin_albaran;
            tipoPalets = data.tipoPalets;
            palets = data.palets;
            bultosCapas = data.bultosCapas;
            tiendas = data.tiendasList;
            locs = data.locs;
            bultosTiendas = new Array(tiendas.length);

            for(var i=0; i<bultosTiendas.length; i++) {
                bultosTiendas[i] = new Array(lineasAlbaran.length);
            }

            if(tiendas.length > 0) {
                hasTiendas = true;
            }

            for(var i=0; i < locs.length; i++) {
                var tiendaIndex = getTiendaIndexFromEan(locs[i].lugar);
                var lineIndex = getLinAlbaranIndexFromCodart(locs[i].codart);
                var udsBulto = lineasAlbaran[lineIndex].udsbul;
                bultosTiendas[tiendaIndex][lineIndex] = 0;
            }

            selectedLine = 0;
            selectedPalet = 0;
            selectedTienda = 0;
        }

        function loadHtml() {
            if(hasTiendas)
                loadHtmlForTienda();
            else {
                loadDefaultHtml();
            }
            showExportador();
        }

        function loadDefault(data) {
            loadSharedVariables(data);
        }

        function showExportador() {
            $('#capaBusquedaAlbaran').hide();
            $('#capaMontarPalets').show();
            $('#capaVisorPalets').show();
            formType = 'addToPalet';
        }

        function setMaxBultosInput() {
            var maxBultos = lineasAlbaran[selectedLine].bultosRestantes;
            if(hasTiendas) {
                maxBultos = bultosTiendas[selectedTienda][selectedLine];
            }
            inputNumBultos.prop("max", maxBultos);
            inputNumBultos.val(maxBultos);
        }

        function loadDefaultHtml() {
            loadLineHtml();
            loadPaletHtml();
        }

        function loadHtmlForTienda() {
            loadDefaultHtml();
            loadTiendasHtml();
        }

        function loadTiendasHtml() {

            for(var i=0; i<tiendas.length; i++)
            {
                var params = {};
                params.value = i;
                params.text = tiendas[i].cod_interno + " - " + tiendas[i].nombre;
                if(selectedTienda == i) {
                    params.selected = 'selected';
                }

                selectTiendas.append($('<option>', params));
            }

        }

        function loadPaletHtml() {


            for(var i=0; i<palets.length; i++) {
                var params = {};
                params.value = i;
                params.text = 'Palet ' + (i + 1);

                if(selectedPalet == i) {
                    params.selected = 'selected';
                }

                selectPalets.append($('<option>', params));
            }
        }


        function loadLineHtml() {

            var linea = lineasAlbaran[selectedLine];

            spanLine.text(linea.codart + " | "+linea.descri + " | "+linea.horizo + "-" + linea.vertic +
                    " (Quedan "+ (linea.bultosRestantes) +" bultos)");
        }

        function loadLocalData(data) {
            var local_object = JSON.parse(data);
            console.log(local_object);

            lineasAlbaran = local_object.lineasAlbaran;
            tipoPalets = local_object.tipoPalets;
            palets = local_object.palets;
            bultosCapas = local_object.bultosCapas;
            tiendas = local_object.tiendas;
            locs = local_object.locs;
            hasTiendas = local_object.hasTiendas;
            bultosTiendas = local_object.bultosTiendas;
            selectedLine = local_object.selectedLine;
            selectedPalet = local_object.selectedPalet;
            selectedTienda = local_object.selectedTienda;
        }

        function loadIfTempData() {
            var tempData = localStorage.getItem("localData"+hashLocalData);
            if(tempData !== undefined && tempData != null) {
                loadLocalData(tempData);
                loadHtml();
                refreshVisor();
                nextIteraction();
                return true;
            }
            return false;
        }

        function loadSharedVariables(data) {
            lineasAlbaran = data.lin_albaran;
            tipoPalets = new Array(data.albaran.totpal);
            palets = new Array(data.albaran.totpal);
            bultosCapas = new Array(data.albaran.totpal);
            tiendas = data.tiendasList;
            locs = data.locs;

            if(tiendas.length > 0) {
                hasTiendas = true;
            }

            for(var i=0; i < palets.length; i++) {
                palets[i] = new Array(lineasAlbaran.length);
                bultosCapas[i] = new Array(lineasAlbaran.length);
            }

            initializeSharedVariables();

            if(hasTiendas) {
                loadDataForTiendas();
            }
        }

        function initializeSharedVariables() {
            for(var i=0; i < palets.length; i++) {
                for(var j=0; j < lineasAlbaran.length; j++) {
                    palets[i][j] = 0;
                    bultosCapas[i][j] = 0;
                    lineasAlbaran[j].bultosRestantes = lineasAlbaran[j].bultos;
                }
            }
            selectedLine = 0;
            selectedPalet = 0;
        }

        function initializeSharedVariablesForTienda() {
            for(var i=0; i < palets.length; i++) {
                for (var j = 0; j < palets[i].length; j++) {
                    for (var k = 0; k < palets[i][j].length; k++) {
                        palets[i][j][k] = 0;
                    }
                }
            }

            for(var i=0; i<tiendas.length; i++) {
                for(var j=0; j<lineasAlbaran.length; j++) {
                    bultosTiendas[i][j] = 0;
                }
            }

            for(var i=0; i < locs.length; i++) {
                var tiendaIndex = getTiendaIndexFromEan(locs[i].lugar);
                var lineIndex = getLinAlbaranIndexFromCodart(locs[i].codart);
                var udsBulto = lineasAlbaran[lineIndex].udsbul;
                bultosTiendas[tiendaIndex][lineIndex] = locs[i].cantidad / udsBulto;
            }
            selectedTienda = 0;
        }


        function loadDataForTiendas() {
            for(var i=0; i<palets.length; i++) {
                for(var j=0; j<palets[i].length; j++) {
                    palets[i][j] = new Array(tiendas.length);
                }
            }

            bultosTiendas = new Array(tiendas.length);

            for(var i=0; i< bultosTiendas.length; i++) {
                bultosTiendas[i] = new Array(lineasAlbaran.length);
            }

            initializeSharedVariablesForTienda();
        }

        function showCargarExportadorErrors(data) {
            if(data.success == false) {

                if(data.hasOwnProperty('error')) {
                    alert(data.error);
                }
                else alert("Ha habido un fallo al obtener los datos!");
                return true;
            }
            if(data.data.albaran == null) {
                alert("No existe ningún albarán de salida con estos datos!");
                return true;
            }

            return false;
        }

        function getLinAlbaranIndexFromCodart(codart) {
            for(var i=0; i<lineasAlbaran.length; i++) {
                if(lineasAlbaran[i].codart == codart) {
                    return i;
                }
            }
            return null;
        }

        function getTiendaIndexFromEan(ean) {
            for(var i=0; i<tiendas.length; i++) {
                if(tiendas[i].ean == ean) {
                    return i;
                }
            }
            return null;
        }

        function resetSubmitButton() {
            submitButton.prop("disabled", false);
            submitButton.text("Comenzar");
        }

        function changeLine(line) {
            var maxLine = lineasAlbaran.length - 1;

            if(line >= 0 && line <= maxLine) {
                selectedLine = line;
                loadLineHtml();
                setMaxBultosInput();
            }
        }

        function changeTienda(selectTiendas) {
            selectedTienda = Number(selectTiendas.val());
            setMaxBultosInput();
        }

        function modificarBultosTienda(palet, linea, tienda, value) {
            value = Number(value);
            var dif = palets[palet][linea][tienda] - value;
            palets[palet][linea][tienda] = value;
            bultosTiendas[tienda][linea] += dif;
            lineasAlbaran[linea].bultosRestantes += dif;
            resetForm();
            nextIteraction();
            refreshVisor();
        }

        function modificarBultos(palet, linea, value) {
            value = Number(value);
            var dif = palets[palet][linea] - value;
            palets[palet][linea] = value;
            lineasAlbaran[linea].bultosRestantes += dif;
            resetForm();
            nextIteraction();
        }

        $('body').on('click', '#lineUp',function() {
            changeLine(selectedLine-1);
        });

        $('body').on('click', '#lineDown',function() {
            changeLine(selectedLine+1);
        });

        $('body').on('change', '#numTienda',function() {
            changeTienda($(this));
        });

        $('body').on('change', '#numPalet',function() {
            selectedPalet = selectPalets.val();
        });

        $('body').on('click', '.modificarCantidad', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if(hasTiendas) {
                modificarBultosTienda($(this).attr('data-palet'), $(this).attr('data-linea'), $(this).attr('data-tienda'),  $(this).prev('input').val());
            }
            else {
                modificarBultos($(this).attr('data-palet'), $(this).attr('data-linea'), $(this).prev('input').val());
            }
        });

        $('body').on('click', '#resetLocal',function(e) {
            e.preventDefault();
            e.stopPropagation();

            if(confirm("¿Desea eliminar los cambios realizados?")) {
                localStorage.removeItem("localData"+hashLocalData);
                window.location.href = '{{url("/app/edi/exportar-edi")}}';
            }
        });



    </script>
@endsection
@endsection