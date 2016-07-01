<!DOCTYPE html>
<html lang="en">
<head>
    <link href='https://fonts.googleapis.com/css?family=Contrail+One' rel='stylesheet' type='text/css'>
    <style>
        @font-face {
            font-family: LogivalFontLight;
            src: url("{{url('/logival/fonts/MyriadPro-Light.otf')}}");
        }
        html, body {
            height:100%;
        }
        body {
            background: rgb(204,204,204);
            font-family:  Helvetica, sans-serif;
            color: #003F54;
        }
        body,page[size="A4"] {
            background: white;
            width: 21cm;
            height: 29.7cm;
            display: block;
            margin: 0 auto;
        }

        @page {
            size: A4;
        }

        html{margin:0px 0px}

        td.a6 {
            width: 10.5cm;
            height: 14.8cm;
            min-width: 10.5cm;
            min-height: 14.8cm;
        }



        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }

            @page {
                size: A4;
            }

            html{margin:0px 0px}

            .page-a6 {
                overflow: hidden;
                page-break-after: always;
                page-break-inside: avoid;

            }
        }

        html{margin:0px 0px}

        table {
            border-spacing: 0;
            table-layout: fixed;
        }
        .page-a6 {
            border: none;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;

        }

        .etiqueta td{
            border: 1px solid black;
            padding: 0.5cm;
        }


    </style>

    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
    <page size="A4">
        <table class="page-a6">
            <tr>
                <?php $numLabels = 0; ?>
                @foreach($labels as $label)
                    @for($i=0; $i<$label["bultos"]; $i++)
                        <?php $numLabels++ ?>
                        <td class="a6">
                            <table style="height: 90%;" width="90%" align="center" class="etiqueta">
                                <tr>
                                    <td height="23%">PROVEEDOR:</td><td>{!! $proveedor !!}</td>
                                </tr>
                                <tr>
                                    <td height="23%">TIENDA:</td><td>{!! $label["tienda"] !!}</td>
                                </tr>
                                <tr>
                                    <td height="23%">BULTO TIENDA:</td><td>{{$i+1}}/{{$label["bultos"]}}</td>
                                </tr>
                                <tr>
                                    <td height="23%">Nº PEDIDO</td><td>{{$pedido}}</td>
                                </tr>
                            </table>
                        </td>
                        @if($numLabels%2==0)
                            </tr><tr>
                        @endif
                        @if($numLabels%4==0)
                            </tr></table></page><page size="A4"><table class="page-a6"><tr>
                        @endif
                    @endfor
                @endforeach
            </tr>
        </table>
    </page>
</body>
</html>
