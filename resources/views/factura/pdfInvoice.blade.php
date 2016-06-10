<?
setlocale(LC_MONETARY, 'it_IT.UTF-8');
?>
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
            height: 30.2cm;
            display: block;
            margin: 0 auto;
            padding-left: 2.23mm;

        }
        html{margin:0px 0px}



        @media print {
            body, page[size="A4"] {
                background: white;
                width: 21cm;
                height: 29.7cm;
                display: block;
                margin: 0 auto;

            }

            html{margin:0px 0px}
        }

        .logo {
            float: left;
        }

        .cab-logo {
            float: right;
            max-width: 11.9cm;
        }

        .logo img {
            width: 6.94cm;


        }

        .cabecera {
            font-family: LogivalFontLight, Arial, Helvetica, sans-serif;
        }

        .content {
            margin-right: 0.5cm;
        }

        .cab-logo img{
            width: 11.90cm;

        }

        .datosEmpresa {
            width: 6.94cm;
        }

        .datosCliente {

            height: 100px;
            padding-left: 3.28cm;
            font-size: 0.35cm;
            margin-top: 0.2cm;
        }

        .datosCliente .linea {
            margin-bottom: 0.2cm;
        }

        .datosEmpresa .linea {
            font-size: 0.3cm;
            margin-top: 0.09cm;
        }

        .cabFactura .meta {
            font-family: 'Contrail One', cursive;
            font-size: 0.3cm;
            float: left;
            padding-top: 0.12cm;
            padding-bottom: 0.12cm;
            padding-left: 0.10cm;
            padding-right: 0.19cm;
            background-color: #003F54;
            color: #ffffff;
        }

        .cabFactura {
            margin-top: 0.31cm;
            padding-bottom: 0.35cm;
            border-bottom: 2px solid #003F54;
            position: relative;
        }
        .cabFactura .numPag {
            position: absolute;
            left: 8.7cm;
            top: 0.1cm;
        }
        .cabFactura .info {
            position: relative;
        }

        .info-numfac {
            float: left;
        }

        .info-fecha {
            float: right;
            margin-right: 5.7cm;
        }

        .cabFactura .valueNumFac {
            position: absolute;
            bottom: 0;
            width: 4.76cm;
            display: inline-block;
            padding-left: 0.1cm;
            float: left;
            border-bottom: 1px solid #003F54;
        }

        .cabFactura .valueFecha {
            position: absolute;
            bottom: 0;
            width: 5.64cm;
            display: inline-block;
            padding-left: 0.1cm;
            float: left;
            border-bottom: 1px solid #003F54;
        }

        .contentLineasFactura {
            margin-top: 0.06cm;
            margin-bottom: 0.06cm;
            width: 100%;
            height: 21.10cm;
            background-color: #F9FEFF;
        }

        .totales {
            margin-top: 0.06cm;
            border-top: 2px solid #003F54;
            border-bottom: 1px solid #003F54;
            padding-top: 0.55cm;
            font-size: 0.35cm;
            margin-bottom: 0.2cm;


        }

        .cajaTotal {
            float: left;
            margin-right: 0.11cm;
            background-color: #CCD8DE;
            padding-top: 0.20cm;
            padding-bottom: 0.20cm;
            color: #003F54;
            text-align: center;
            min-height: 0.4cm;;
        }

        .cajaValor {
            background:none !important;
        }

        .totales .totalBruto {
            width:3cm;
        }

        .totales .descuento {
            width: 1.86cm;
        }

        .totales .importeDescuento {
            width: 3.35cm;
        }

        .totales .baseImponible {
            width: 2.93cm;
        }

        .totales .porcIVA {
            width: 2.34cm;
        }

        .totales .cuotaIVA {
            width: 2.61cm;
        }

        .totales .totaFactura {
            width: 3.70cm;
            font-weight: bold;
            background-color: #003F54;
            color: #ffffff;
            margin-left: 0.1cm;
        }

        .cajaValor.totaFactura {
            color: #003F54;
        }

        .cajaFormaPago {
            margin-right: 0.11cm;
            background-color: #CCD8DE;
            padding-top: 0.20cm;
            padding-bottom: 0.20cm;
            color: #003F54;
            text-align: center;
            width:3cm;
            font-size: 0.35cm;
            float: left;
        }

        .formaTransferencia {
            padding-top: 0.20cm;
            padding-bottom: 0.20cm;
        }

        .footer {
            text-transform: uppercase;
            color: #003F54;
            font-size: 0.22cm;
            text-align: center;
            margin-top: 0.1cm;
        }





        th {
            text-align: left;
            font-size: 0.35cm;
            padding-bottom: 0.2cm;
        }

        td {
            font-size: 0.29cm;
        }

        table {
            border-spacing: 0;
            margin-left: 0.4cm;
        }



        html{margin:0px 0px}







    </style>

    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?php $anotherPage = true; $pagActual=0; $numPags = ceil(count($factura['lines'])/\App\Factura::LINES_PER_PAGE) ?>
@if($factura['reducida'])
    <?php $numPags=1; ?>
@endif
@while($anotherPage)
    <?php $anotherPage = false; $pagActual++; echo \App\Factura::LINES_PER_PAGE;   ?>
    <page size="A4">
        <div class="cabecera">
            <div class="logo">
                <img src="{{url("logival/img/logo-3.png")}}">
                <div class="datosEmpresa">
                    <div class="linea"><span>CIF. B 96985510</span> | <span><strong>www.logival.es</strong></span> </div>
                    <div class="linea">Camí Faitanar N.3 · CP. 46210 Picanya. Valencia</div>
                    <div class="linea">T.961 590 861 · F.961 590 466 | contacto@logival.es</div>
                </div>
            </div>
            <div class="cab-logo">
                <img src="{{url("logival/img/cab-logo-2.png")}}">
                <div class="datosCliente">
                    <div class="linea">Cód. Cliente: {{$factura['codcli']}}</div>
                    <div class="linea"><strong>{{$factura['nomcli']}}</strong><br>NIF: {{$factura['nif']}}</div>
                    <div class="linea">{{$factura['domici']}}<br>{{$factura['codpos']}} {{$factura['poblac']}} @if($factura['provin']), {{\App\Provincia::getProvincia($factura['provin'])}} @endif @if($factura['pais']) ({{\App\Pais::getPais($factura['pais'])}}) @endif</div>
                </div>

            </div>
            <div style="clear:both;"></div>

        </div>
        <div class="content">
            <div class="cabFactura">
                <div class="info info-numfac">
                    <div class="meta">Factura Nº</div>
                    <div class="valueNumFac">{{$factura['id']['serfac']}}/{{$factura['id']['ejefac']}}/{{$factura['id']['numfac']}}</div>
                </div>
                <div class="numPag">Pág. {{$pagActual}}/{{$numPags}}</div>
                <div class="info info-fecha">
                    <div class="meta">Fecha de factura</div>
                    <div class="valueFecha">{{\Carbon\Carbon::parse($factura['fecfac'])->format("d/m/Y")}}</div>
                </div>
                <div style="clear:both;"></div>
            </div>

            <div class="contentLineasFactura">
                <table width="100%">
                    <thead>
                    <tr>
                        <th>Fecha serv.</th>
                        <th>Descripción servicio</th>
                        <th>Unidades</th>
                        <th></th>
                        <th>Importe</th>
                        <th>Seguro</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; ?>

                    @if($factura['reducida'])

                        @foreach($groups as $aplica=>$group)
                            <tr>
                                <td width="20%"></td>
                                <td width="40%">{{$group['descripcion']}}</td>
                                <td width="10%"></td>
                                <td width="10%"></td>
                                <td width="10%">{{number_format($group['importe'], 2, ',', '.')}}</td>
                                <td width="10%"></td>
                            </tr>
                        @endforeach
                    @endif
                    @foreach ($factura['lines'] as $index => $line)
                        @if(!$factura['reducida'] || ($factura['reducida'] && !array_key_exists(
                            $line['aplica'],
                            \App\Http\Controllers\FacturaController::$GROUPS_DESCRIPTIONS)
                            )
                        )

                            <tr>
                                <td width="20%">{{\Carbon\Carbon::parse($line['feccal'])->format("d/m/Y")}}</td>
                                <td width="40%">{{$line['descri']}}</td>
                                <td width="10%">{{number_format($line['unitas'], 2, ',', '.')}}</td>
                                <td width="10%">{{$line['bastar']}}</td>
                                <td width="10%">{{number_format($line['import_'], 2, ',', '.')}}</td>
                                <td width="10%">{{number_format($line['seguro'], 2, ',', '.')}}</td>
                            </tr>
                            <?php $i++; unset($factura['lines'][$index]);?>
                            @if($i==\App\Factura::LINES_PER_PAGE)
                                @if(count($factura['lines'])>0)
                                    <?php $anotherPage = true; ?>
                                @endif
                                <?php break;?>
                            @endif
                        @endif
                    @endforeach

                    </tbody>
                </table>
            </div>

            <div class="totales">
                <div class="cajaTotal totalBruto">Total Bruto</div>
                <div class="cajaTotal descuento">Descuento</div>
                <div class="cajaTotal importeDescuento">Importe Descuento</div>
                <div class="cajaTotal baseImponible">Base Imponible</div>
                <div class="cajaTotal porcIVA">% I.V.A</div>
                <div class="cajaTotal cuotaIVA">Cuota I.V.A</div>
                <div class="cajaTotal totaFactura">Total Factura</div>
                <div style="clear:both;"></div>
                <div class="cajaTotal cajaValor totalBruto">@if(!$anotherPage){{number_format($factura['impbru'], 2, ',', '.')}}€@endif</div>
                <div class="cajaTotal cajaValor descuento">@if(!$anotherPage){{$factura['dtofac']}}%@endif</div>
                <div class="cajaTotal cajaValor importeDescuento">@if(!$anotherPage){{number_format($factura['impdfac'], 2, ',', '.')}}€@endif</div>
                <div class="cajaTotal cajaValor baseImponible">@if(!$anotherPage){{number_format($factura['basiva'], 2, ',', '.')}}€@endif</div>
                <div class="cajaTotal cajaValor porcIVA">@if(!$anotherPage){{$factura['tipiva']}}%@endif</div>
                <div class="cajaTotal cajaValor cuotaIVA">@if(!$anotherPage){{number_format($factura['impiva'], 2, ',', '.')}}€@endif</div>
                <div class="cajaTotal cajaValor totaFactura">@if(!$anotherPage){{number_format($factura['totfac'], 2, ',', '.')}}€@endif</div>
                <div style="clear:both;"></div>

            </div>
            <div class="cajaFormaPago">Forma de pago</div> <div class="formaTransferencia">{{\App\FormaPagos::getFormaPago($factura['forpag'])}}</div>
        </div>
        <div class="footer">Insc. en el Reg. Merc. de Valencia. Tomo 6635, Libro 3939, de la sección 8º de sociedades, folio31, hoja nº.V. 72021,
            Inscripción, 1º, de fecha 6 de junio de 2000
        </div>
    </page>
@endwhile
</body>
</html>
