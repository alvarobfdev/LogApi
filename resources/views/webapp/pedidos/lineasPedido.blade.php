<div class="row seccionPedidos">
    <div class="col-md-12" id="contentPedidos">
        <div class="tituloSeccion">
            <strong>Líneas pedido</strong>
        </div>
        <table width="100%" id="lineasPedido" class="tablaPedidosGral table-striped">
            <thead>
            <tr>
                <th>Línea</th><th>Artículo</th><th>Descripción</th><th>Cant. Pedida</th>
            </tr>
            </thead>
            <tbody id="lineasPedidoBody">

            </tbody>
        </table>
    </div>
</div>
<script src="{{asset('js/webapp/pedidos/base.js')}}"></script>
<script>
    var app = WEBAPP.common;
    var lineasPedido = {

        activateSelector:function() {
            $('#lineasPedido').tableSelector({
                accessRowFunction: function(row) {
                    var id = row.attr('data-id');
                }
            });
        },
        getRow:function(linea) {
            var tr = '<tr data-id="'+linea._id+'">' +
                    '<td>'+linea.numlin+'</td>' +
                    '<td>'+linea.codart+'</td>' +
                    '<td>'+linea.descri+'</td>' +
                    '<td>'+linea.cantid+'</td>' +
                    '</tr>';
            return tr;
        },
        loadTable:function(lineas) {
            var table = $('#lineasPedidoBody');

            for(var i=0; i<lineas.length; i++) {
                var linea = lineas[i];
                var row = lineasPedido.getRow(linea);
                table.append(row);
            }
        }
    };
    app.templateLoaded = function(args) {
        lineasPedido.loadTable(args.lineas);
        lineasPedido.activateSelector();
    };

    app.unloadTemplate = function() {
        lineasPedido = {};
        $('#lineasPedido').tableSelector.destroy();
    };

    app.restoreTemplate = function() {
        lineasPedido = app.currentPageData;
    }

</script>