<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 17/3/16
 * Time: 9:28
 */

namespace App\Http\Controllers;


use App\Artic;
use App\Ctsql;
use App\EdiCabped;
use App\ProductsEdiModel;
use App\RestApiModels\Cliente;
use App\RestApiModels\Pedido;
use App\RestApiModels\User;
use App\WoocommerceApi;

class AppController extends Controller
{

    public function getTestCtsql() {
        $query = "SELECT * FROM artic WHERE codcli=139 AND (codart='BH1257' OR codart='BH1259' OR codart='BH1258')";
        $exporter = new Ctsql();
        $resultArray = $exporter->ctsqlExport($query);
        if(is_array($resultArray) && count($resultArray) > 0) {
            $json = $resultArray[0];
            $result = json_decode($json);
            dd($result);
            return $json;
        }
        return "Ha habido algún problema!";
    }

    public function getAlbaranForEdi() {

        $in = \Request::all();
        $ejercicio = $in["ejercicio"];
        $cliente = $in["numCliente"];
        $numAlbaran = $in["numAlbaran"];
        $albaran = new \stdClass();
        $linAlbaran = new \stdClass();
        $products = new \stdClass();
        $result = [];

        if(!$this->getAlbaran($ejercicio, $cliente, $numAlbaran, $albaran)) {
            return json_encode($albaran);
        }

        if(!$this->getLinAlbaran($ejercicio, $cliente, $numAlbaran, $linAlbaran)) {
            return json_encode($linAlbaran);
        }

        $cods = [];
        foreach($linAlbaran as $lin) {
            $cods[] = $lin->codart;
        }

        if(!$this->getProducts($cliente, $cods, $products)) {
            return json_encode($products);
        }

        $bultos = $this->getBultos($cliente, $linAlbaran, $products);

        $result["success"] = true;
        $result["data"]["albaran"] = $albaran;
        $result["data"]["lin_albaran"] = $linAlbaran;
        $result["data"]["products"] = $products;
        $result["data"]["bultos"] = $bultos;


        return $result;
    }

    public function getDasanciProducts($codcli) {
        $productos = [];
        $query = "SELECT * FROM artic where (codcli = $codcli) and codemp = 1";
        $prods = Ctsql::ctsqlExport($query);
        $query = "SELECT * FROM ocupalmac where (codcli = $codcli) and codemp = 1 and coddel=1";
        $stock = Ctsql::ctsqlExport($query);
        $stock = json_decode($stock[0]);
        $prods = json_decode($prods[0]);
        foreach($prods->data as $prod) {
            $productos[$prod->codart]["cantidad"] = 0;
            $productos[$prod->codart]["descripcion"] = $prod->descri;
            foreach($stock->data as $prodStock) {
                if($prodStock->codart == $prod->codart && $prod->codcli == $prodStock->codcli) {
                    $productos[$prod->codart]["cantidad"] += $prodStock->udsart;
                }
            }
        }

        $webProducts = WoocommerceApi::getProducts(["filter[limit]"=>"200"]);


        foreach($productos as $index=>$producto) {
            $coincide=false;
            foreach($webProducts->products as $webProduct) {
                if($index == $webProduct->sku) {
                    $coincide=true;
                    echo "<span style='color:green;'>".$index ." -> ". $producto["descripcion"] ." -> ". $producto["cantidad"]." -- EXISTE EN WEB Y STOCK</span><br>";
                }
            }
            if(!$coincide) {
                echo "<span style='color:darkgray;'>".$index ." -> ". $producto["descripcion"] ." -> ". $producto["cantidad"]." -- NO EXISTE EN WEB</span><br>";
            }
        }

        foreach($webProducts->products as $webProduct) {
            $coincide=false;

            foreach($productos as $index=>$producto) {
                if($index == $webProduct->sku) {
                    $coincide = true;
                }
            }

            if(!$coincide) {
                echo "<span style='color:red;'>".$webProduct->sku ." -> ". $webProduct->title ." -> 0 -- NO EXISTE EN STOCK</span><br>";

            }
        }
    }

    public function getTestDasanciApi() {
        $products = WoocommerceApi::getProducts(["filter[limit]"=>"200"]);
        dd($products);
    }

    public function getIndex() {
        return view("admin.starter");
    }


    public function getToybagsStock() {
        $query = "SELECT * FROM artic WHERE codemp=1 AND codcli=176";
        $data = Ctsql::ctsqlExport($query);
        $data = json_decode($data[0]);
        dd($data->data);
    }
    public function getAddToybagsStock() {
        $query = "DELETE FROM artic WHERE codemp = 1 AND codcli=176";
        Ctsql::ctsqlImport($query);

        $dataToInsert = [
[            "TL960-688", "8436021460271", "MOCHIL TROLLEY RUEDAS ENSAMBLAR CON LUZ"],
[            "T960-688", "8436021460257", "MOCHIL TROLLEY RUEDAS ENSAMBLAR BOX DISP"],
[            "T433-700", "8436021461353", "MOCHILA"],
[            "T433-306", "8436021461360", "MOCHILA"],
[            "T433-696", "8436021460455", "MOCHILA TUZKI"],
[            "T424-015", "8436021460134", "MOCHILA SOY LUNA"],
[            "T424-282", "8436021460523", "MOCHILA MINNIE"],
[            "CR2000114", "5411217398548", "MOCHILA SPIDERMAN"],
[            "CR2020114", "5411217398661", "MOCHILA AVENGERS "],
[            "T100-024", "8436021461063", "PORTATODO FROZEN"],
[            "T100-282", "8436021461346", "PORTATODO MINNIE"],
[            "CR2000133", "5411217398562", "PORTATODO SPIDERMAN"],
[            "CR2020133", "5411217398715", "PORTATODO AVENGERS"],
[            "T960-015", "8436021460165", "MOCHIL TROLLEY RUEDAS ENSAMBLAR SOY LUNA"],
[            "T960-024", "8436021461124", "MOCHIL TROLLEY RUEDAS ENSAMBLAR FROZEN"],
[            "CR2000114T", "5411217623206", "MOCHILA CON TROLLEY SPIDERMAN"],
[            "CR2020114T", "5411217623480", "MOCHILA CON TROLLEY AVENGERS"],
[            "T960-282E", "8436021460547", "MOCHIL CARRO DESMONT GRAND RUEDAS MINNIE"],
[            "T424-282", "8436021460523", "MOCHILA DAYPACK  MINNIE CON BOLSILLO."],
[            "T100-024E", "8436021460424", "SURTID POTATOD (FROZEN, MINNIE Y MICKEY)"],
[            "T424-678E", "8436021460493", "SURTIDO MOCHILA (CHICAGO & TREAD)"],
[            "T960-696E", "8436021460462", "MOCHIL CARRO BOLSILL TUZKI (NEGRA Y ROSA"],
[            "T960-678E", "8436021460509", "MOCHIL CARRO BOLSILLO (CHICAGO & TREAD)"],
[            "T655-024E", "8436021460417", "SURTID MICKEY,MINNIE,FROZEN,FINDING DORY"],
[            "T424-696E", "8436021461339", "MOCHIL DAYPACK BOLSILLO TUZKI NEGRA,ROSA"],
[            "T328-024E", "8436021460400", "SURTID MOCHIL INFNT GUARDE MICKEY,MINNIE"],
[            "T800-024E", "8436021460394", "MOCHIL INFANT TRLLY GUARDE MICKEY,MINNIE"],
[            "SM511718", "5411217639153", "MOCHIL DAYPACK BOLSILLO SPIDERMAN ESCUDO"],
[            "AV510718", "5411217639078", "MOCHILA DAYPACK  AVENGERS CON ESCUDO"],
[            "SM511718T", "5411217623947", "MOCHILA TROLLEY SPIDERMAN CON CARETA"],
[            "AV510718T", "5411217623978", "MOCHILA TROLLEY AVENGERS CON ESCUDO"],
[            "PW464107", "5416233131513", "MOCHILA JR PAW PATROL"],
[            "PW464026", "5416233131759", "TROLLEY PAW PATROL INFANTIL"],
[            "T960-014L", "8436021460097", "MOCHILA TROLLEY CON LUZ FROZEN"],
[            "T424-014", "8436021460066", "MOCHILA FROZEN"],
[            "T323-014", "8436021461056", "MOCHILITA FROZEN"],
[            "T607-014", "8436021461049", "BANDOLERA FROZEN"],
[            "T632-014", "8436021460042", "SAQUITO FROZEN"],
[            "T103-014", "8436021460073", " PORTATODO FROZEN"],
[            "T157-014", "8436021460059", "TRIPLE PORTATODO FROZEN"],
[            "T154-014", "8436021461315", "FUNDA PARA FLAUTA FROZEN"],
[            "T810-008-CI", "8436021461322", "MOCHILIT TROLLEY  PORTATOD FINDING DORY"],
[            "CR2020029SET", "5411217826102", "MARVEL AVENGER CARRO FIJO+PORTATOD REGAL"],
            ["18705921", "5411217639153", "MOCHILA G.SURT SPIDERM/AVENG C/6"],
            ["18705657", "5411217623947", "CARRO G.SURT SPIDERM/AVENG C/4"],
            ["18705806", "8436021460523", "MOCHILA G. MINNIE C.BOLSILLO C/6"],
            ["18705707", "8436021460547", "CARRO G MINNIE+ PORTATODO C/6"],
            ["18705715", "8436021460394", "CARRO GUARDERIA SURT.DISNEY C/8"],
            ["18705798", "8436021460400", "MOCHILA GUARDERIA SURT. DISNEY C/12"],
            ["18705954", "8436021460417", "SAQUITO SURTIDO DISNEY C/24"],
            ["18705814", "5416233131513", "MOCHILA INF PAW PATROL C/6"],
            ["18705749", "5416233131759", "CARRO GUARDERIA PAW PATROL C/6"],
            ["18705962", "8436021460424", "PORTATODO DISNEY C/12"],
            ["18705939", "8436021461339", "MOCHILA G SURTIDA BOLS TUZKI C/6"],
            ["18705756", "8436021460462", "CARRO G BOLSILLO TUZKI C/4"],
            ["18705947", "8436021460493", "MOCHILA JUVENIL SURTIDA C/6"],
            ["18705772", "8436021460509", "CARRO G JUVENIL SURTIDO C/4"],
            ["964270", "8436021460257", "Surtido Mochilas CASUAL GRANDE C/16"],
            ["964272", "8436021460271", "Surtido Mochilas  CASUAL RUEDA LUZ C/6"],
            ["220351", "8436021460165", "SURTID TRLLEY LCENCIAS DISNEY MARVEL C/4"],
            ["220352", "8436021460455", "SURTIDO MOCHILA JUVENIL GENERICA C/3"],
            ["220350", "8436021460134", "SURTID MOCHILS LCENCIA DISNEY MARVEL C/4"],
            ["220349", "8436021461063", "SURTIDO PORTATODO LICENCIAS C/12"],


        ];

        foreach($dataToInsert as $data) {
            $query  = "INSERT INTO artic (codemp, codcli, codart, descri, kgsuni, facbas, basalm, precio, stkmin, baralm, caduci, tipzon, volume, codbar, ctlser, adecua, barcpe, basman, barman, basmen, barmen, basmsa, barmsa, envweb, fifoli, codkit, codcom)
VALUES
(1, 176, '{$data[0]}', '{$data[2]}', 1.000, 0.000, 'BUL', 0.00, 0.000, 'Z', 'N', '', 0.000, '{$data[1]}', 'N', '', '0', 'BUL', '0', 'BUL', '0', 'BUL', '0', 'N', '', '', 0)";
            
            $ctsql = Ctsql::ctsqlImport($query);
            var_dump($ctsql);
        }



    }

    public function getBackup() {

        $limit = 0;
        do {
            $query = "SELECT * FROM artic LIMIT $limit, 1000";
            $articBase = Ctsql::ctsqlExport($query);
            $articBase = json_decode($articBase[0]);
            foreach ($articBase->data as $articulo) {
                $artic = new Artic();
                foreach (get_object_vars($articulo) as $index => $value) {
                    $artic->$index = $value;
                }
                $artic->save();
            }
            $limit += 1000;

        } while (count($articBase->data) > 0);
    }

    public function getBombasToZero() {
        $query = "UPDATE artic set kgsuni=0 WHERE codcli=60 or codcli=90 or codcli=50";
        //$result = Ctsql::ctsqlImport($query);

    }

    public function getLoteDasanci() {
        $query = "UPDATE artic set caduci = 'L' WHERE codcli = '1580'";
        $ctsql = Ctsql::ctsqlImport($query);
        var_dump($ctsql);
    }

    public function getPedidos($codcli) {
        $query = "SELECT * FROM pedidos WHERE codcli=$codcli AND ejeped=2016 LIMIT 0,10";
        $data = Ctsql::ctsqlExport($query);
        $data = json_decode($data[0]);
        dd($data);
    }

    public function getTest() {
        return EdiCabped::all()->toJson();
    }


    public function getInsertEciToybags()
    {

        $dataToInsert = [
            ['T960L-014','25644320','CARRO MOCHILA FROZEN  42 CM  EXTRAIBLE,  CON LUZ EN LAS RUEDAS.','8436021460097','6'],
            ['T424-014','25474298','MOCHILA 42 CM FROZEN CON ADAPTADOR PARA CARRO.','8436021460066','6'],
            ['T323-014','25474306','MOCHILA FROZEN PEQUEÑA','8436021461056','6'],
            ['T607-014','25543100','BOLSO FROZEN','8436021461049','12'],
            ['T632-014','25543118','SACO FROZEN GRANDE.','8436021460042','12'],
            ['T103-014','21972006','PORTATODO NECESER FROZEN .','8436021460073','12'],
            ['T157-014','21972014','PORTATODO TRIPLE FROZEN .','8436021460059','12'],
            ['T154-014','25543126','FUNDA PARA FLAUTA FROZEN','8436021461315','24'],
            ['T810-008-CI','25644338','CARRO CON BOLSILLO + PORTATODO REGALO FINDING DORY.','8436021461322','6'],
            ['CR2020029SET','25644346','Marvel AVENGERS Carro fijo + Portatodo  Regalo.','5411217826102','6'],
        ];

        foreach($dataToInsert as $artic) {

            $query = "DELETE FROM artic WHERE codart = '{$artic[1]}' AND codcli = 176";
            $ctsql = Ctsql::ctsqlImport($query);


            $refEci = $artic[1];
            $descrp = substr($artic[2], 0, 34) . " C/".$artic[4];
            $ean = $artic[3];

            $query  = "INSERT INTO artic (codemp, codcli, codart, descri, kgsuni, facbas, basalm, precio, stkmin, baralm, caduci, tipzon, volume, codbar, ctlser, adecua, barcpe, basman, barman, basmen, barmen, basmsa, barmsa, envweb, fifoli, codkit, codcom)
VALUES
(1, 176, '{$refEci}', '{$descrp}', 1.000, 0.000, 'BUL', 0.00, 0.000, 'Z', 'N', '', 0.000, '{$ean}', 'N', '', '0', 'BUL', '0', 'BUL', '0', 'BUL', '0', 'N', '', '', 0)";

            $ctsql = Ctsql::ctsqlImport($query);
            var_dump($ctsql);

        }
    }


    public function getImportarPedidosToybags() {
        $result = Ctsql::ctsqlExport("SELECT * from pedidos WHERE codcli=176");
        $data = json_decode($result[0]);
        foreach($data->data as $pedidoCtsql) {
            $pedido = new Pedido();
            foreach(get_object_vars($pedidoCtsql) as $index=>$value) {


                if($index != "numped" || $pedido->numped == NULL) {

                    $pedido->$index = $value;

                }
                if($index == "refped" && $value!="") {
                    $pedido->numped = $value;
                }
            }
            echo $pedido->toJson();
            $pedido->save();


        }
    }



}