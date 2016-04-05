@extends('admin.starter')


    <!-- Content Header (Page header) -->
    @section('content')

    <section class="content-header">
      <h1>
        Monitor de clientes
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Forms</a></li>
        <li class="active"></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">

        <div class="col-md-6">
          <!-- Horizontal Form -->

          <!-- general form elements disabled -->
          <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Consultar cliente</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

              <div class="row show-grid">
                <div class="col-xs-3">
                  <input type="text" class="form-control" id="codCliente" placeholder="Cod. Cliente">
                </div>
                <div class="col-xs-9">
                  <input type="text" class="form-control" id="nomCli" placeholder="Nombre cliente">
                </div>
              </div>

                <div class="row show-grid" style="display: none;">
                    <div class="col-xs-12">
                        <h4>Pedidos</h4>
                        <table class="table table-bordered" id="tablePedidos">
                            <thead>
                            <tr>
                                <th style="width: 10px">E/S</th>
                                <th style="width: 100px">Nº Pedido</th>
                                <th>Fecha Pedido</th>
                                <th>Fecha entrada</th>
                                <th style="width: 20px">C/P</th>
                                <th style="width: 20px">Rsv.</th>
                                <th>Clte./Prov.</th>
                            </tr>
                            </thead>
                            <tbody></tbody>


                        </table>
                    </div>
                </div>

              <div class="row show-grid" style="display: none;">
                <div class="col-xs-12">
                    <h4>Albaranes</h4>
                    <table class="table table-bordered" id="tableAlbaranes">
                        <thead>
                            <tr>
                                <th style="width: 10px">E/S</th>
                                <th>Nº Albarán</th>
                                <th>Fecha Albarán</th>
                                <th style="width: 40px">Bultos</th>
                                <th style="width: 40px">Kilos</th>
                                <th style="width: 80px">Nº Pedido</th>
                            </tr>
                        </thead>
                        <tbody></tbody>


                    </table>
                </div>
              </div>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
    @endsection

    @section('scripts')
      <script>
        var limitAlbaran = 10;
        $(function() {
          $(document).keypress(function(k) {

            switch(k.keyCode){
              // user presses the "ENTER"
              case 13:
                  var cod_cliente = $('#codCliente').val();
                  $('input').prop("disabled", true);
                  $.getJSON("{{url("app/clientes/obtener")}}", {cod_cliente:cod_cliente, limit_albaran:limitAlbaran}, function(result) {
                    console.log(result);
                    var cliente = result.cliente;
                    var albaranes = result.albaranes;
                    var pedidos = result.pedidos;
                    if(!cliente.success) {
                        alert("Error al obtener cliente!");
                        return;
                    }
                    if(cliente.data.length<1) {
                        alert("No existe ningún cliente con estos datos!");
                        $('input').prop("disabled", false);
                        return;
                    }

                    cliente = cliente.data[0];
                    $('#nomCli').val(cliente.nomcli);

                    if(!albaranes.success) {
                        alert("Error al obtener albaranes!");
                        $('input').prop("disabled", false);
                        return;
                    }

                    albaranes = albaranes.data;
                    pedidos = pedidos.data;

                      $('#tableAlbaranes > tbody').html('');
                      $('#tablePedidos > tbody').html('');


                      $.each(albaranes, function(index, albaran) {
                        var numAlbaran = "";
                        if(albaran.seralb!="" && albaran.seralb!=" ") {
                            numAlbaran=albaran.seralb+"-";
                        }
                        var row = "<tr>";
                        row+="<td>"+albaran.tipalb+"</td>";
                        row+="<td>"+numAlbaran+albaran.ejerci+"-"+albaran.numalb+"</td>";
                        row+="<td>"+albaran.fecalb+"</td>";
                        row+="<td>"+albaran.totbul+"</td>";
                        row+="<td>"+albaran.totkil+"</td>";
                        row+="<td>"+albaran.numped+"</td>";
                        row+="</tr>";
                        $('#tableAlbaranes > tbody').append(row);
                        $('#tableAlbaranes').closest('.row').show();
                    });

                      $.each(pedidos, function(index, pedido) {
                          var numPedido = "";
                          if(pedido.serped != "" && pedido.serped != " ") {
                              numPedido = pedido.serped+"-";
                          }
                          var row = "<tr>";
                          row+="<td>"+pedido.tipped+"</td>";
                          row+="<td>"+numPedido+pedido.ejeped+"-"+pedido.numped+"</td>";
                          row+="<td>"+pedido.fecped+"</td>";
                          row+="<td>"+pedido.fecent+"</td>";
                          row+="<td>"+pedido.indser+"</td>";
                          row+="<td>"+pedido.reserv+"</td>";
                          row+="<td>"+pedido.nomtec+"</td>";
                          row+="</tr>";
                          $('#tablePedidos > tbody').append(row);
                          $('#tablePedidos').closest('.row').show();
                      });

                      $('input').prop("disabled", false);




                  });


                break;


            }
          });
        })
      </script>
    @endsection

