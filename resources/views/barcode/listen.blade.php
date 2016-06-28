@extends('base')
@section('content')
    <h4>Esperando lectura código...</h4>
    <div class="row">
        <div class="col-md-12" id="productInfo" style="display: none;">
            <h5 id="title">123456789</h5>
            <h6 id="description">SURTIDO MOCHILAS CASUAL RUEDA LUZ C/6 - 1800 unidades</h6>
            <span>Ubicaciones</span>
            <ul id="listUbicaciones">

            </ul>
        </div>
    </div>
@section('scripts')
    <script>




        function poll() {
            $.ajax({
                url: "{{url('barcode-reader/listen-product-barcode')}}", success: function (result) {

                    if(result.success && result.data.length > 0) {
                        showInfo(result.data[0]);
                    }
                    poll();
                }, error:poll, dataType:'JSON'
            });
        }

        function showInfo(data) {
            console.log(data);
            $('#productInfo #title').text(data.codart);

            var totalUds = 0;
            var htmlUbcs = "";
            for(var i=0; i<data.ubics.length; i++) {
                var ubic = data.ubics[i];
                console.log(ubic);
                totalUds += ubic.udsart;
                htmlUbcs += '<li>'+ubic.horizo+'-'+ubic.vertic+' -> '+ubic.udsart+' uds.</li>';
            }
            $('#productInfo #description').text(data.descri + " - " + totalUds + "uds.");
            $('#listUbicaciones').html(htmlUbcs);
            $('#productInfo').show();

        }

        $(function() {
            poll();
        });
    </script>
@endsection
@endsection
