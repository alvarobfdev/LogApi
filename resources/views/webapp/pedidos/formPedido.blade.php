<div class="tituloSeccion" id="tituloPedido">
    <strong></strong>
</div>
<div class="formPedido">
    <form id="formularioPedido">
        <div class="row">
            <div class="col-sm-4">
                <label for="cliente_nomcli">Nombre de Cliente:</label><br>
                <input type="text" id="cliente_nomcli" name="cliente_nomcli">
            </div>
            <div class="col-sm-2">
                <label for="fecped">Fecha de Pedido:</label><br>
                <input type="text" id="fecped" name="fecped">
            </div>
            <div class="col-sm-1">
                <label for="serped">Serie:</label><br>
                <input type="text" id="serped" name="serped">
            </div>
            <div class="col-sm-2">
                <label for="numped">Núm. Pedido:</label><br>
                <input type="text" id="numped" name="numped">
            </div>
            <div class="col-sm-2">
                <label for="fecent">Fecha de entrega:</label><br>
                <input type="text" id="fecent" name="fecent" data-validation="date" data-validation-format="dd/mm/yyyy" data-validation-optional="true">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <label for="nomfis">Nombre Fiscal Clte./Proveedor:</label><br>
                <input type="text" id="nomfis" name="nomfis" data-validation="length" data-validation-length="1-100">
            </div>
            <div class="col-sm-5">
                <label for="refped">Pedido de referencia:</label><br>
                <input type="text" id="refped" name="refped">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-offset-1">
                <label>REMITENTE/CONSIGNATARIO</label>
                <div class="row">
                    <div class="col-sm-2">
                        <label for="codtec">Código:</label>
                    </div>
                    <div class="col-sm-3">
                        <input type="text" id="codtec" name="codtec">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <label for="nomtec">Nombre:</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" id="nomtec" name="nomtec" data-validation="length" data-validation-length="1-100">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <label for="dirtec">Domicilio:</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" id="dirtec" name="dirtec" data-validation="length" data-validation-length="1-100">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <label for="pobtec">Población:</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" id="pobtec" name="pobtec" data-validation="length" data-validation-length="1-100">
                    </div>
                    <div class="col-sm-1 text-right">
                        <label for="cpotec">CP:</label>
                    </div>
                    <div class="col-sm-2">
                        <input type="text" id="cpotec" name="cpotec" data-validation="length" data-validation-length="1-100">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <label for="dirtec">País:</label>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" id="paistec" name="paistec" data-validation="length" data-validation-length="1-100" value="">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-11">
                <label for="observ">Observaciones:</label><br>
                <textarea name="observ" id="observ"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-1">
                <label for="transp">Transportista</label>
            </div>
            <div class="col-sm-4">
                <input type="text" name="transp" id="transp">
            </div>
            <div class="col-sm-1">
                <label for="indser">Ser. Parcial</label>
            </div>
            <div class="col-sm-2">
                <select name="indser" id="indser">
                    <option value="S">SI</option>
                    <option value="N">No</option>
                </select>
            </div>
            <div class="col-sm-1">
                <label for="reserv">Reservar</label>
            </div>
            <div class="col-sm-2">
                <select name="reserv" id="reserv">
                    <option value="S">SI</option>
                    <option value="N">No</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-1">
                <label for="pobdis">Pobl. distr.</label>
            </div>
            <div class="col-sm-3">
                <input type="text" name="pobdis" id="pobdis">
            </div>
            <div class="col-sm-1">
                <label for="cpodis">CP distr.</label>
            </div>
            <div class="col-sm-3">
                <input type="text" name="cpodis" id="cpodis">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-1">
                <label for="reembo">Reembolso</label><br>
                <input type="text" name="reembo" id="reembo">

            </div>
            <div class="col-sm-1">
                <label for="totbul">Bultos</label><br>
                <input type="text" name="totbul" id="totbul">

            </div>
            <div class="col-sm-1">
                <label for="totkil">Kilos</label><br>
                <input type="text" name="totkil" id="totkil">

            </div>
            <div class="col-sm-1">
                <label for="totvol">Volumen</label><br>
                <input type="text" name="totvol" id="totvol">

            </div>
            <div class="col-sm-1">
                <label for="imptot">Imp. Total</label><br>
                <input type="text" name="imptot" id="imptot">

            </div>
            <div class="col-sm-1">
                <label for="valora">Valorado</label><br>
                <select name="valora" id="valora">
                    <option value="S">SI</option>
                    <option value="N">No</option>
                </select>
            </div>
            <div class="col-sm-1">
                <label for="apliva">Aplic. IVA</label><br>
                <select name="apliva" id="apliva">
                    <option value="S">SI</option>
                    <option value="N">No</option>
                </select>

            </div>
            <div class="col-sm-1">
                <label for="tipiva">Tipo IVA</label><br>
                <input type="text" name="tipiva" id="tipiva">
            </div>
            <div class="col-sm-1">
                <label for="estado">Estado</label><br>
                <select name="estado" id="estado">
                    <option value=" ">Pendiente</option>
                    <option value="F">Finalizado</option>
                    <option value="A">Anulado</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="codcli">
    </form>
</div>
<script>
    var app = WEBAPP.common;
    var formPedido = {
        formHtml:$('#formularioPedido'),
        args:{},
        idPedido:'',
        lineasPedidoObject:{},
        disableInputs:function() {
            $('input').prop('disabled', true);
            $('textarea').prop('disabled', true);
            $('select').prop('disabled', true);
        },
        loadTitulo:function(data) {
            var tipoPedido = 'Entrada';
            if(data.tipped == 'S') {
                tipoPedido = 'Salida';
            }
            $('#tituloPedido').html('<strong>Pedido de '+tipoPedido+": "+data.ejeped+'/'+data.numped+'</strong>');
        },

        fillInput:function(data, prepend) {
            $.each(data, function(index, value) {
                $('input[name='+prepend+index+']').val(value);
                $('textarea[name='+prepend+index+']').val(value);
                $('select[name='+prepend+index+']').val(value);

            })
        },
        loadForm:function(data) {
            formPedido.fillInput(data, '');
            formPedido.fillInput(data.cliente, 'cliente_');
            app.addKeyLog('l', formPedido.lineasPedido, 'Líneas Pedido');
            formPedido.lineasPedidoObject = data.lineas_pedido;

        },
        getPedido:function(idPedido) {

            if(app.hasTempData("pedidoData-"+idPedido)) {
                var data = app.getTempData("pedidoData-" + idPedido);
                formPedido.loadTitulo(data);
                formPedido.loadForm(data);
            }
            else {
                $.getJSON("{{url('web-app/pedidos/by-id')}}/"+idPedido, function(data) {
                    if(data.total > 0) {
                        var data = data.data[0];
                        app.addTempData("pedidoData-" + idPedido, data);
                        formPedido.loadTitulo(data);
                        formPedido.loadForm(data);
                    }
                });
            }

        },

        modificarPedido:function() {
            $('input').prop('disabled', false);
            $('textarea').prop('disabled', false);
            $('select').prop('disabled', false);
            setTimeout(function() {
                $('input:eq(0)').focus();
            }, 100);
            app.addKeyLog('f1', formPedido.guardarPedido, 'Guardar');

        },

        guardarPedido:function() {
            if(!formPedido.formHtml.isValid(null, null, true)) {
                app.advice.send(app.advice.ERROR, "El formulario está incompleto o erróneo");
                return;
            }
            $.post("{{url('web-app/pedidos/save')}}/"+formPedido.idPedido, formPedido.formHtml.serialize(), function() {

            }).fail(function(data) {
                 app.manageAjaxError(data);
            });
        },

        borrarPedido:function() {
            if(confirm("¿Seguro que deseas borrar este pedido?")) {
                alert("Pedido Borrado");
                var e = $.Event('keydown');
                e.keyCode = app.getKeyCodeFromKey("esc");
                $('body').trigger(e);
            }
        },
        lineasPedido:function() {
            app.loadPage("pedidos", "lineasPedido", {lineas:formPedido.lineasPedidoObject});
        },
        viewForm: function(idPedido) {
            var pedido = formPedido.getPedido(idPedido);
            formPedido.disableInputs();
            app.addKeyLog('m', formPedido.modificarPedido, 'Modificar');
            app.addKeyLog('b', formPedido.borrarPedido, 'Borrar');
            app.addKeyLog('r', formPedido.resetPedido, 'Resetear');
        },

        resetPedido:function() {
            formPedido.getPedido(formPedido.idPedido);
        }
    };

    app.currentPageData = formPedido;

    app.templateLoaded = function(args) {
        $.validate({
            lang: 'es'
        });
        formPedido.args = args;
        if(args['pageType'] == 'read') {
            formPedido.idPedido = args['idPedido']
            formPedido.viewForm(formPedido.idPedido);
        }
    };

    app.unloadTemplate = function() {
        formPedido = {};
    };

    app.restoreTemplate = function(template) {
        formPedido = app.currentPageData;
        if (formPedido.args['pageType'] == 'read') {
            formPedido.viewForm(formPedido.args['idPedido']);
        }
    }
</script>