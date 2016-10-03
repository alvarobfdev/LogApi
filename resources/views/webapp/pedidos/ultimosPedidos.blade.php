<div class="row seccionPedidos">
    <div class="col-md-12" id="contentPedidos">
        <div class="tituloSeccion">
            <strong>Últimos pedidos</strong>
        </div>
        {{--<form class="form-inline" role="form">
            <div class="form-group">
                <label for="cliente">Cliente:</label>
                <input type="text" class="form-control" id="cliente">
            </div>
            <div class="form-group">
                <label for="cliente">Núm. Pedido:</label>
                <input type="text" class="form-control" id="cliente">
            </div>

        </form>--}}
        <table width="100%" id="ultimosPedidos" class="tablaPedidosGral table-striped">
            <thead>
            <tr>
                <th>E/S</th><th>Cliente</th><th>Núm. Pedido</th><th>Fecha Ped.</th><th>Fecha ent.</th><th>Remitente</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script src="{{asset('js/webapp/pedidos/base.js')}}"></script>
<script>

    var app = WEBAPP.common;
    var ultimosPedidos = {


        page:1,
        busquedaPedidoPorCliente: function () {
            app.loadPage('pedidos', 'busquedaPorCliente');
        },

        bindScroll:function() {

            if($(window).scrollTop() + $(window).height() > $(document).height() - 500) {
                $(window).off('scroll');
                ultimosPedidos.page++;
                ultimosPedidos.loadTable();
            }
        },

        loadTable:function(obj, reactivateScroll) {
            var table = $('#ultimosPedidos tbody');

            $.getJSON('{{url('web-app/pedidos/last')}}/'+ultimosPedidos.page, function (data) {
                var pedidos = data.data;
                for (var i = 0; i < pedidos.length; i++) {
                    var pedido = pedidos[i];
                    var tipped = "Entrada &rarr;";
                    if (pedido.tipped == 'S') {
                        tipped = "&larr; Salida"
                    }
                    var tr = '<tr data-id="'+pedido._id+'">' +
                            '<td>' + tipped + '</td>' +
                            '<td>' + pedido.codcli + '</td>' +
                            '<td>' + pedido.numped + '</td>' +
                            '<td>' + pedido.fecped + '</td>' +
                            '<td>' + pedido.fecent + '</td>' +
                            '<td>' + pedido.nomtec + '</td>' +
                            '</tr>';

                    table.append(tr);
                }

                if(data.current_page < data.last_page) {
                    if($(document).height() == $(window).height()){
                        ultimosPedidos.page++;
                        if(ultimosPedidos != null)
                            ultimosPedidos.loadTable();
                    }

                    if(typeof obj !== 'undefined') {
                        reactivateScroll(obj);
                    }

                }

                else {
                    $(window).scrollLoad('destroy');
                }

                $('#ultimosPedidos').tableSelector({
                    accessRowFunction:function(row) {
                        basePedidos.loadPedido(row.attr('data-id'));
                    }
                });


            });

        }

    };

    app.currentPageData = ultimosPedidos;

    app.templateLoaded = function() {
        app.pagesStack = new stack();
        ultimosPedidos.loadTable();
        app.addKeyLog('b', ultimosPedidos.busquedaPedidoPorCliente, 'Búsqueda por cliente');

        $(window).scrollLoad({
            loaderFunction: function(obj, reactivate) {
                ultimosPedidos.page++;
                ultimosPedidos.loadTable(obj, reactivate);
            }
        });

        $('body').on('click', '#ultimosPedidos tr', function() {
            basePedidos.loadPedido($(this).attr('data-id'));
        })
    };

    app.unloadTemplate = function() {
        $('body').off('click', '#ultimosPedidos tr');
        $(window).scrollLoad('destroy');
        ultimosPedidos = {};
        $('#ultimosPedidos').tableSelector.destroy();

    };
    app.restoreTemplate = function() {
        ultimosPedidos = app.currentPageData;
    }







</script>