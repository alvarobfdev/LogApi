/**
 * Created by alvarobanofos on 8/8/16.
 */


var basePedidos = {

    loadPedido:function(idPedido) {
        var args = {};
        args['pageType'] = 'read';
        args['idPedido'] = idPedido;
        app.loadPage('pedidos', 'formPedido', args);
    }
};