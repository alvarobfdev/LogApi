<?php
    $pages = (count($lineas)-22)/29;
    if($pages < 0) {
        $pages = 0;
    }
    else {
        $pages = ceil($pages);
    }
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
            font-size: 11px;
        }
        body,page[size="landscape"] {
            background: white;
            height: 210mm;
            min-height: 212mm;
            width: 297mm;
            display: block;
            margin: 0 auto;
            padding-left: 2.23mm;

        }
        html{margin:0px 0px}

        @media print {
            body, page[size="landscape"] {
                background: white;
                height: 21mm;
                min-height: 210mm;
                width: 297mm;
                display: block;
                margin: 0 auto;
            }

            @page {
                size: landscape;
            }

            html{margin:0px 0px}
        }

        .page {
            padding-top: 5mm;
            width: 290mm;
            min-height: 205mm;
        }

        .cabecera {
            border-bottom: 2px solid black;
            overflow: hidden;
        }

        .cabecera>div {
            float: left;
            width: 33%;

        }

        .cabecera .logo {
            padding-top: 5mm;
        }

        .cabecera .title {
            padding-top: 6mm;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cabecera .data {
            text-align: right;
        }

        .cabecera .data tr>td:first-child {
            text-align: left;
        }
        .cabecera .data tr>td:last-child {
            text-align: right;
        }

        .albaran-data {
            overflow: hidden;
            margin-top: 1mm;
        }

        .albaran-data .title {
            font-weight: bold;
        }

        .albaran-data .origen {
            float: left;
            width: 45%;
        }

        .albaran-data .proveedor {
            float: right;
            width: 45%;

        }

        .albaran-data .box {
            border: 1px solid black;
        }

        .albaran-data>table{
            border-spacing: 0;
            margin-top: 1mm;
        }
        .albaran-data>table td{
            border: 1px solid black;
            padding: 1mm;
        }

        .albaran-lineas > table {
            border-spacing: 0;
            margin-top: 1mm;
        }

        .albaran-lineas > table th {
            text-transform: uppercase;
            border: 1px solid black;
            text-align: left;
        }
        .albaran-lineas > table td {
            border: 1px solid black;
            padding: 1mm;
        }




    </style>
    <meta charset="UTF-8">

</head>
<body>
    <page size="landscape">
        <div class="page">
            <div class="cabecera">
                <div class="logo">
                    LOGO
                </div>
                <div class="title"><div>Albarán de salida</div></div>
                <div class="data">
                    <table align="right">
                        <tr>
                            <td>Hoja:</td><td>1 de <?=$pages+1?></td>
                        </tr>
                        <tr>
                            <td>Fecha:</td><td><?=\Carbon\Carbon::now('Europe/Madrid')->format("d/m/Y H:i")?></td>
                        </tr>
                        <tr>
                            <td>Albarán:</td><td><?=$albaran->num_albaran?></td>
                        </tr>
                        <tr>
                            <td>Pedido:</td><td><?=$albaran->num_pedido_cliente?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="albaran-data">
                <div class="origen">
                    <span class="title">Origen</span>
                    <div class="box">
                        <table width="100%">
                            <tr>
                                <td>Logistica Valencia de Portes S.L</td><td>CIF: B96985510</td>
                            </tr>
                            <tr>
                                <td>PO: 8473098842005</td><td></td>
                            </tr>
                            <tr>
                                <td>CAMI FAITANAR, 3</td><td></td>
                            </tr>
                            <tr>
                                <td>46210 PICANYA</td><td>VALENCIA</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="proveedor">
                    <span class="title">Proveedor</span>
                    <div class="box">
                        <table width="100%">
                            <tr>
                                <td colspan="2"><?=$albaran->codcli_proveedor?> <?=$albaran->razon_proveedor?></td><td>PO: <?=$albaran->punto_operacional_proveedor?></td>
                            </tr>
                            <tr>
                                <td><?=$albaran->direccion_proveedor?></td><td></td>
                            </tr>
                            <tr>
                                <td><?=$albaran->cp_proveedor?> <?=$albaran->poblacion_proveedor?></td><td><?=$albaran->provincia_proveedor?></td>
                            </tr>
                            <tr>
                                <td>TFNO: <?=$albaran->telf_proveedor?></td><td>FAX: <?=$albaran->fax_proveedor?></td><td>EMAIL: <?=$albaran->email_proveedor?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div style="clear: both"></div>
                <table width="100%">
                    <thead>
                        <tr>
                            <td width="15%">Pedido</td>
                            <td width="15%">Fecha entrega</td>
                            <td width="5%">Total bultos</td>
                            <td width="5%">Total palets</td>
                            <td width="30%">Lugar de entrega</td>
                            <td width="30%">Sucursal destino</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$albaran->num_pedido_cliente?></td>
                            <td><?=\Carbon\Carbon::parse($albaran->fecha_entrega)->format("d/m/Y")?></td>
                            <td><?=$albaran->num_bultos?></td>
                            <td><?=$albaran->num_palets?></td>
                            <td>
                                <?=$albaran->codigo_entrega?> <?=$albaran->nombre_entrega?><br>
                                <?=$albaran->direccion_entrega?><br>
                                <?=$albaran->cp_entrega?> <?=$albaran->poblacion_entrega?> - <?=$albaran->provincia_entrega?>

                            </td>
                            <td>
                                <?=$albaran->codigo_pide?> <?=$albaran->nombre_pide?> (Dpto. <?=$albaran->depto_compra?>)<br>
                                <?=$albaran->direccion_pide?><br>
                                <?=$albaran->cp_pide?> <?=$albaran->poblacion_pide?> - <?=$albaran->provincia_pide?><br>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="albaran-lineas">
                <table width="100%">
                    <thead>
                        <tr>

                            <th>Ean</th>
                            <th>Ref.</th>
                            <th>Descrp.</th>
                            <th>Bultos</th>
                            <th>Uds./Bulto</th>
                            <th>Total Uds.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lineas as $index=>$linea)
                        <tr>
                            <td><?=$linea->ean?></td>
                            <td><?=$linea->referencia?></td>
                            <td><?=$linea->descripcion?></td>
                            <td><?=$linea->bultos?></td>
                            <td><?=$linea->uds_bulto?></td>
                            <td><?=$linea->uds_totales?></td>
                        </tr>
                        <?php unset($lineas[$index]) ?>
                        @if($index == 21)
                            <?php break; ?>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </page>
    @for($i = 0; $i<$pages; $i++)
        <page size="landscape">
            <div class="page">
                <div class="cabecera">
                    <div class="logo">
                        LOGO
                    </div>
                    <div class="title"><div>Albarán de salida</div></div>
                    <div class="data">
                        <table align="right">
                            <tr>
                                <td>Hoja:</td><td><?=$i+2?> de <?=$pages+1?></td>
                            </tr>
                            <tr>
                                <td>Fecha:</td><td><?=\Carbon\Carbon::now('Europe/Madrid')->format("d/m/Y H:i")?></td>
                            </tr>
                            <tr>
                                <td>Albarán:</td><td><?=$albaran->num_albaran?></td>
                            </tr>
                            <tr>
                                <td>Pedido:</td><td><?=$albaran->num_pedido_cliente?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="albaran-lineas">
                    <table width="100%">
                        <thead>
                        <tr>

                            <th>Ean</th>
                            <th>Ref.</th>
                            <th>Descrp.</th>
                            <th>Bultos</th>
                            <th>Uds./Bulto</th>
                            <th>Total Uds.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($lineas as $index=>$linea)
                            <tr>

                                <td><?=$linea->ean?></td>
                                <td><?=$linea->referencia?></td>
                                <td><?=$linea->descripcion?></td>
                                <td><?=$linea->bultos?></td>
                                <td><?=$linea->uds_bulto?></td>
                                <td><?=$linea->uds_totales?></td>
                            </tr>
                            <?php unset($lineas[$index]) ?>
                            @if($index-22 > 0 && ($index-22) % 28 == 0)
                                <?php break; ?>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </page>
    @endfor

</body>
</html>