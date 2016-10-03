@extends('base')
@section('content')
@endsection
@section('scripts')
    <script>
        var EDIEXPORTER = EDIEXPORTER || {};

        EDIEXPORTER = {
            palets:[],

            applyTienda:function(bultoPalet) {
                if(EDIEXPORTER.tiendaActivated) {
                    for (var i = 0; i < bultoPalet.lineas; i++) {
                        var linea = bultoPalet.lineas[i];
                        linea.tienda = EDIEXPORTER.data.tiendas[form.tiendaSelected];
                    }
                }
            },

            addBultosToPalet:function() {
                var bulto = EDIEXPORTER.data.bultos[bultoSelected];

                for(var i=0; i<form.numBultos; i++) {
                    var bultoPalet = {
                        lineas:bulto.lineas
                    };
                    EDIEXPORTER.applyTienda(bultoPalet);
                    EDIEXPORTER.palets[form.selectedPalet].bultos.push(bultoPalet);
                }
            }
        };




    </script>



@endsection