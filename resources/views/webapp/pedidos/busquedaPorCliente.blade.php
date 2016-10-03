<div class="tituloSeccion">
    <strong>Búsqueda Pedidos por Cliente</strong>
</div>

<div class="col-sm-1">
    <form class="form-inline" role="form" id="formPedidosCliente">
        <div class="form-group">
            <label for="inputCliente">Cliente:</label>
            <input type="text" class="form-control" id="inputCliente">
        </div>
    </form>
</div>
<div class="col-md-12" id="pedidosClienteData" style="display: none;">

    <span id="tituloCliente"></span>

    <table width="100%" id="pedidosCliente" class="tablaPedidosGral table-striped">
        <thead>
        <tr>
            <th>E/S</th><th>Cliente</th><th>Núm. Pedido</th><th>Fecha Ped.</th><th>Fecha ent.</th><th>Remitente</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<script src="{{asset('js/webapp/pedidos/base.js')}}"></script>
<script>

    var app = WEBAPP.common;
    var busquedaPorCliente = {

        pageType:"search",
        activateSelector:function() {
            $('#pedidosCliente').tableSelector({
                accessRowFunction: function(row) {
                    var id = row.attr('data-id');
                    basePedidos.loadPedido(id);
                }
            });
        },

        busquedaPedidoPorCliente: function () {
            app.loadPage('pedidos', 'busquedaPorCliente');
        },

        ultimosPedidos: function () {
            app.loadPage('pedidos', 'ultimosPedidos');
        },

        page:1,

        loadTable:function(idCliente, dataRestart, restart) {
            var table = $('#pedidosCliente tbody');

            $.getJSON("{{url('web-app/pedidos/by-client')}}/"+idCliente+"/"+busquedaPorCliente.page, function(data) {
                var pedidos = data.data;
                if(pedidos.length > 0) {
                    for (var i = 0; i < pedidos.length; i++) {
                        var pedido = pedidos[i];
                        var tipped = "Entrada &rarr;"
                        if (pedido.tipped == 'S') {
                            tipped = "&larr; Salida"
                        }
                        var tr = '<tr data-id="' + pedido._id + '">' +
                                '<td>' + tipped + '</td>' +
                                '<td>' + pedido.codcli + '</td>' +
                                '<td>' + pedido.numped + '</td>' +
                                '<td>' + pedido.fecped + '</td>' +
                                '<td>' + pedido.fecent + '</td>' +
                                '<td>' + pedido.nomtec + '</td>' +
                                '</tr>';

                        table.append(tr);
                    }
                    $('#pedidosClienteData').show();
                    $('#formPedidosCliente').hide();
                    busquedaPorCliente.activateSelector();

                    if(data.current_page < data.last_page) {
                        if($(document).height() == $(window).height()){
                            busquedaPorCliente.page++;
                            if(busquedaPorCliente != null)
                                busquedaPorCliente.loadTable(idCliente);
                        }

                        if(typeof dataRestart !== 'undefined') {
                            restart(dataRestart);
                        }

                    }
                    else {
                        $(window).scrollLoad('destroy');
                    }

                }
                else {
                    alert("No hay pedidos para este cliente");
                }
            });
        },

        obtenerPedidosCliente:function(idCliente) {

            app.savePage();
            busquedaPorCliente.pageType = "table";
            this.loadTable(idCliente);

            $.getJSON("{{url('web-app/clientes/cliente')}}/"+idCliente, function(data) {
                if(data.total > 0) {
                    var cliente = data.data[0];
                    var tituloCliente = $('#tituloCliente');
                    tituloCliente.text(cliente.codcli+" - "+cliente.nomcli);
                }
            });

            $(window).scrollLoad({
                loaderFunction:function(data, restart) {
                    busquedaPorCliente.page++;
                    busquedaPorCliente.loadTable(idCliente, data, restart);
                }
            });

        },
        sendForm:function(e) {
            var inputCliente = $('#inputCliente');
            e.preventDefault();
            busquedaPorCliente.obtenerPedidosCliente(inputCliente.val());
            inputCliente.autocomplete('disable');
        },

        activateAutocomplete:function() {
            var inputCliente = $('#inputCliente');
            inputCliente.autocomplete({
                serviceUrl: '{{url("/web-app/clientes/autocomplete")}}',
                onSelect: function (suggestion) {
                    inputCliente.val(suggestion.data);
                    inputCliente.prop('disabled', true);
                    busquedaPorCliente.obtenerPedidosCliente(suggestion.data);
                }
            });
        }
    };

    app.currentPageData = busquedaPorCliente;

    app.templateLoaded = function() {
        app.addKeyLog('p', busquedaPorCliente.ultimosPedidos, 'Últimos pedidos');
        app.addKeyLog('b', busquedaPorCliente.busquedaPedidoPorCliente, 'Búsqueda por cliente');

        var inputCliente = $('#inputCliente');
        inputCliente.prop('disabled', true);
        setTimeout(function() {
            inputCliente.prop('disabled', false);
            inputCliente.focus();
        }, 100);
        $('#formPedidosCliente').on('submit', busquedaPorCliente.sendForm);

        busquedaPorCliente.activateAutocomplete();

        $('body').on('click', '#pedidosCliente tr', function() {
            basePedidos.loadPedido($(this).attr('data-id'));
        })

    };

    app.unloadTemplate = function() {
        $('body').off('click', '#pedidosCliente tr');
        busquedaPorCliente = {};
        $('#pedidosCliente').tableSelector.destroy();

    };
    app.restoreTemplate = function() {
        var inputCliente = $('#inputCliente');
        busquedaPorCliente = app.currentPageData;
        console.log(busquedaPorCliente.pageType);
        if(busquedaPorCliente.pageType == "table") {
            busquedaPorCliente.activateSelector();
        }
        if(busquedaPorCliente.pageType == "search") {
            $('#formPedidosCliente').on('submit', busquedaPorCliente.sendForm);
            busquedaPorCliente.activateAutocomplete();
            setTimeout(function() {
                inputCliente.prop('disabled', false);
                inputCliente.focus();
            }, 100);
        }

    };

</script>