@extends('base')
@section('content')
    <h4>Esperando lectura código...</h4>
    <span style="color: red" id="error"></span>
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


        var audioOk = new Audio("{{asset('sounds/beep-ok.wav')}}");
        var audioError = new Audio("{{asset('sounds/beep-error.wav')}}");

        function poll() {

            $.ajax({
                url: "{{url('barcode-reader/listen-product-barcode')}}", success: function (result) {
                    $('#error').text('');
                    $('#productInfo').hide();
                    if(result.success && result.data.length > 0) {
                        audioOk.play();
                        showInfo(result.data[0]);
                    }
                    else {
                        audioError.play();
                        $('#error').text("El código de artículo '"+result.barcode+ "' no existe!");
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
