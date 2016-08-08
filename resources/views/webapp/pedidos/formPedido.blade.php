<div class="tituloSeccion">
    <strong>Pedido 2016/134 - TOYBAGS</strong>
</div>
<script>
    var app = WEBAPP.common;
    var formPedido = {
        viewForm: function() {

        }
    };

    app.currentPageData = formPedido;
    app.templateLoaded = function(args) {
        if(args['pageType'] == 'read') {
            formPedido.viewForm();
        }
    };

    app.unloadTemplate = function() {
        formPedido = {};
    };

    app.restoreTemplate = function() {
        formPedido = app.currentPageData;
    }
</script>