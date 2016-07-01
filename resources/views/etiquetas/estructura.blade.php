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




        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }

            @page {
                size: A4;
            }

            html{margin:0px 0px}

            table {
                overflow: hidden;
                page-break-after: always;
                page-break-inside: avoid;

            }

            table, tr, td, th, tbody, thead, tfoot {
                page-break-inside: avoid !important;
            }
        }

        html{margin:0px 0px}

        table {
            border-spacing: 0;
            table-layout: fixed;
        }

        td{
            border: 1px solid black;
            padding: 0.5cm;
        }

        h4 {
            text-align: center;
        }

    </style>

    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<page size="A4">
    @foreach($estructura as $palet => $sscc)
        <h4>PALET {{$palet}}</h4>
        <table align="center">
            <tr>
                <th>SSCC</th><th>Cód. Proovedor</th>
            </tr>
            @foreach($sscc as $matricula => $codProovedor)
                <tr>
                    <td>{{$matricula}}</td><td>{{$codProovedor}}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
</page>
</body>
</html>
