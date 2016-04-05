<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 17/3/16
 * Time: 9:28
 */

namespace App\Http\Controllers;


use App\CtsqlExport;
use App\ProductsEdiModel;
use App\WoocommerceApi;

class AppController extends Controller
{

    public static $toybagsCod = 176;
    public function getTestCtsql() {
        $query = "SELECT * FROM artic WHERE codcli=139 AND (codart='BH1257' OR codart='BH1259' OR codart='BH1258')";
        $exporter = new CtsqlExport();
        $resultArray = $exporter->ctsqlExport($query);
        if(is_array($resultArray) && count($resultArray) > 0) {
            $json = $resultArray[0];
            $result = json_decode($json);
            dd($result);
            return $json;
        }
        return "Ha habido algún problema!";
    }

    public function getExportarEdi() {
        return view("albaran.exportar-edi");
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

    private function getBultos($codcli, $linAlbaran, $prods) {

        if($codcli == self::$toybagsCod) {
            $this->getBultosToyBags($linAlbaran, $prods);
        }


    }

    private function getBultosToyBags($linAlbaran, $prods) {
        $bultos = [];
        foreach($linAlbaran as $lin) {
            $codart = $lin->codart;
            $codart = strtoupper($codart);

            if($codart == 'T433-700' || $codart == 'T433-306' || $codart == 'T433-696') {
                $this->setBultoQuantity($bultos, $lin, "DIA-1", [$codart=>1,$codart=>1,$codart=>1]);
            }

            if($codart == 'T424-015' || $codart == 'T424-282' || $codart == 'CR2000114' || $codart == 'CR2020114') {
                $this->setBultoQuantity($bultos, $lin, "DIA-2", [$codart=>1, $codart=>1, $codart=1]);
            }

            if($codart == 'T100-024' || $codart == 'T100-282' || $codart == 'CR2000133' || $codart == 'CR2020133') {
                $this->setBultoQuantity($bultos, $lin, "DIA-3", [$codart=>3, $codart=>3, $codart=3, $codart=>3]);
            }

            if($codart == 'T960-015' || $codart == 'T960-024' || $codart == 'CR2000114T' || $codart == 'CR2020114T') {
                $this->setBultoQuantity($bultos, $lin, "DIA-4", [$codart=>1, $codart=>1, $codart=1, $codart=>1]);
            }

            if($codart == 'SM511718' || $codart == 'AV510718') {
                $this->setBultoQuantity($bultos, $lin, "EROSKI-1", [$codart=>3, $codart=>3]);
            }

            if($codart == 'SM511718T' || $codart == 'AV510718T') {
                $this->setBultoQuantity($bultos, $lin, "EROSKI-2", [$codart=>2, $codart=>2]);
            }

            if($codart == 'T960-688') {
                $this->setBultoQuantity($bultos, $lin, "1 BOX DISPLAY C/PALLET", [$codart=>16]);
            }

            $product = ProductsEdiModel::where("codart", $codart)->where("codcli", self::$toybagsCod)->first();


            $this->setBultoQuantity($bultos, $lin, $codart." (".$product->export_pack.")", [$codart=>$product->export_pack]);

        }
    }

    private function setBultoQuantity(&$bultos, $lin, $id, $interQuantity = array()) {
        if(!array_key_exists($id, $bultos) || $lin->cantidad < $bultos[$id]['export_quantity']) {
            $bultos[$id]['export_quantity'] = $lin->cantidad;
        }
        $bultos[$id]['inter_quantity'] = $interQuantity;
    }

    private function getProducts($cliente, $cods, &$products) {
        $queryCods = "";
        $i=0;
        foreach($cods as $cod) {
            if($i > 0) {
                $queryCods .=" OR ";
            }
            $queryCods .= "codart='$cod'";
            $i++;
        }

        $query = "SELECT * FROM artic WHERE codcli=$cliente AND ($queryCods)";

        $productsJson = CtsqlExport::ctsqlExport($query);
        $products = json_decode($productsJson[0]);
        if(!$products->success) {
            return false;
        }

        $products = $products->data;
        return true;
    }

    private function getAlbaran($ejercicio, $cliente, $numAlbaran, &$albaran) {

        $query = "SELECT * FROM albaran where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran'";


        $albaranJson = CtsqlExport::ctsqlExport($query);
        $albaran = json_decode($albaranJson[0]);

        if(!$albaran->success) {
            return false;
        }

        if(count($albaran->data) > 0)
            $albaran = $albaran->data[0];

        else $albaran = null;

        return true;
    }

    private function getLinAlbaran($ejercicio, $cliente, $numAlbaran, &$linAlbaran) {
        $query = "SELECT * FROM linalbar where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran'";
        $linAlbarJson = CtsqlExport::ctsqlExport($query);
        $linAlbaran = json_decode($linAlbarJson[0]);

        if(!$linAlbaran->success) {
            return false;
        }

        $linAlbaran = $linAlbaran->data;


        return true;
    }


    public function getDasanciProducts() {
        $productos = [];
        $query = "SELECT * FROM artic where codcli = 158 and codemp = 1";
        $prods = CtsqlExport::ctsqlExport($query);
        $query = "SELECT * FROM ocupalmac where codcli = 158 and codemp = 1 and coddel=1";
        $stock = CtsqlExport::ctsqlExport($query);
        $stock = json_decode($stock[0]);
        $prods = json_decode($prods[0]);
        foreach($prods->data as $prod) {
            foreach($stock->data as $prodStock) {
                if($prodStock->codart == $prod->codart) {
                    if(!isset($productos[$prod->codart]["cantidad"]))
                        $productos[$prod->codart]["cantidad"] = 0;
                    $productos[$prod->codart]["cantidad"] += $prodStock->udsart;
                    $productos[$prod->codart]["descripcion"] = $prod->descri;

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





}