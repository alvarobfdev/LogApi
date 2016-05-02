<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 14/4/16
 * Time: 9:26
 */

namespace App\Http\Controllers;


use App\AlbaranEdi;
use App\AlbaranEdiCajas;
use App\AlbaranEdiLineas;
use App\AlbaranEdiLocalizaciones;
use App\AlbaranEdiPalets;
use App\Ctsql;
use App\EdiCabped;
use App\EdiClientes;
use App\EdiLinped;
use App\EdiLoclped;
use App\ProductsEdiModel;
use Carbon\Carbon;
use Faker\Provider\File;

class EdiController extends Controller
{

    public static $toybagsCod = 176;

    public function getExportarEdi() {
        return view("albaran.exportar-edi");
    }

    public function getCheckNewOrders() {
        $files = \File::files("/ASPEDI/PRODUCCION/ENTRADA");
        //$files = \File::files(storage_path("app/tmp"));
        foreach($files as $file) {
            if($this->isXml($file)) {
                $pedido = $this->getOrderObject($file);
                $this->savePedido($pedido);
                $fileName = basename($file);
                \File::move($file, "/ASPEDI/PRODUCCION/ENTRADA/COPIAS/".$fileName);
            }
        }
    }

    public function getFinishExportEdi() {


        $palets = \Request::get("palets");
        $albaran = \Request::get("albaran");
        $tipoPalets = \Request::get("tipoPalets");
        $tiendasList = \Request::get("tiendasList");
        $lineas = \Request::get("lineas");

        $palets = json_decode($palets);
        $albaran = json_decode($albaran);
        $tipoPalets = json_decode($tipoPalets);
        $tiendasList = json_decode($tiendasList);
        $lineas = json_decode($lineas);
        $pedido = new \StdClass();

        $this->getPedido($albaran->ejeped, $albaran->codcli, $albaran->numped, $pedido);


        $albaranEdi = new AlbaranEdi();
        $albaranEdi->num_expedicion = $albaran->ejerci.$albaran->codcli.$albaran->numalb;

        $pedidoEdi = $this->getPedidoEdi($albaran->ejeped, $albaran->codcli, $albaran->numped);

        if($pedidoEdi->nodo == "YB1") {
            $albaranEdi->tipo = "YA5";
        }
        else {
            $albaranEdi->tipo = "351";
        }


        $albaranEdi->fecha_expedicion = Carbon::parse($albaran->fecalb)->format("YmdHi");
        $albaranEdi->fecha_entrega = Carbon::parse($pedido->fecent)->format("YmdHi");
        $albaranEdi->num_albaran = $albaran->numalb;
        $albaranEdi->num_pedido = $albaran->numped;
        $albaranEdi->origen = "8473098842005";
        $albaranEdi->destino = $pedidoEdi->comprador;
        $albaranEdi->proveedor = $pedidoEdi->vendedor;
        $albaranEdi->comprador = $pedidoEdi->comprador;
        $albaranEdi->departamento = $pedidoEdi->dpto;
        $albaranEdi->receptor = $pedidoEdi->comprador;
        $albaranEdi->totqty = 0;

        $paletsArr = array();
        $cajas = array();
        $lineasArr = array();
        $localizaciones = array();
        $numPalets = count($palets);

        for($i=0; $i<$numPalets; $i++) {
            $albaranEdiPalets = new AlbaranEdiPalets();
            $albaranEdiPalets->codemp = 1;
            $albaranEdiPalets->coddel = 1;
            $albaranEdiPalets->codcli = $albaran->codcli;
            $albaranEdiPalets->tipalb = 'S';
            $albaranEdiPalets->seralb = $albaran->seralb;
            $albaranEdiPalets->ejerci = $albaran->ejerci;
            $albaranEdiPalets->numalb = $albaran->numalb;
            $albaranEdiPalets->idpalet = $i+1;
            $albaranEdiPalets->tipoEmb = $tipoPalets[$i];
            $albaranEdiPalets->save();
            $paletsArr[] = $albaranEdiPalets;

            for($j=0; $j<count($palets[$i]); $j++) {
                if(is_array($palets[$i][$j]))
                    $numBultos = array_sum($palets[$i][$j]);
                else $numBultos = $palets[$i][$j];
                if($numBultos>0) {
                    $albaranEdiCajas = new AlbaranEdiCajas();
                    $albaranEdiCajas->codemp = 1;
                    $albaranEdiCajas->coddel = 1;
                    $albaranEdiCajas->codcli = $albaran->codcli;
                    $albaranEdiCajas->tipalb = 'S';
                    $albaranEdiCajas->seralb = $albaran->seralb;
                    $albaranEdiCajas->ejerci = $albaran->ejerci;
                    $albaranEdiCajas->numalb = $albaran->numalb;
                    $albaranEdiCajas->idpalet = $i + 1;
                    $albaranEdiCajas->idcaja = $j + 1;
                    $albaranEdiCajas->sscc = $this->getNextSscc($albaran->codcli);
                    $albaranEdiCajas->cantidad = $numBultos;
                    $albaranEdiCajas->save();
                    $cajas[] = $albaranEdiCajas;

                    $query = "SELECT descri, codbar FROM artic WHERE codemp=1 and codcli = {$lineas[$j]->codcli} and codart = '{$lineas[$j]->codart}'";
                    $result = Ctsql::ctsqlExport($query);
                    $result = json_decode($result[0]);
                    $articulo = $result->data[0];

                    $udsBulto = $lineas[$j]->cantid/$lineas[$j]->bultos;

                    $albaranEdiLineas = new AlbaranEdiLineas();
                    $albaranEdiLineas->codemp = 1;
                    $albaranEdiLineas->coddel = 1;
                    $albaranEdiLineas->codcli = $albaran->codcli;
                    $albaranEdiLineas->tipalb = 'S';
                    $albaranEdiLineas->seralb = $albaran->seralb;
                    $albaranEdiLineas->ejerci = $albaran->ejerci;
                    $albaranEdiLineas->numalb = $albaran->numalb;
                    $albaranEdiLineas->idpalet = $i + 1;
                    $albaranEdiLineas->idcaja = $j + 1;
                    $albaranEdiLineas->numlin = 1;
                    $albaranEdiLineas->cantidad_total = $udsBulto * $numBultos;
                    $albaranEdi->totqty += $udsBulto * $numBultos;
                    $albaranEdiLineas->descripcion = $articulo->descri;
                    $albaranEdiLineas->ean = $articulo->codbar;
                    $albaranEdiLineas->save();
                    $lineasArr[] = $albaranEdiLineas;

                    for($k=0; $k<count($palets[$i][$j]); $k++) {
                        $numBultos = $palets[$i][$j][$k];

                        if($numBultos > 0 ){
                            $albaranEdiLoc = new AlbaranEdiLocalizaciones();
                            $albaranEdiLoc->codemp = 1;
                            $albaranEdiLoc->coddel = 1;
                            $albaranEdiLoc->codcli = $albaran->codcli;
                            $albaranEdiLoc->tipalb = 'S';
                            $albaranEdiLoc->seralb = $albaran->seralb;
                            $albaranEdiLoc->ejerci = $albaran->ejerci;
                            $albaranEdiLoc->numalb = $albaran->numalb;
                            $albaranEdiLoc->idpalet = $i + 1;
                            $albaranEdiLoc->idcaja = $j + 1;
                            $albaranEdiLoc->numlin = 1;
                            $albaranEdiLoc->idloc = $k + 1;
                            $albaranEdiLoc->lugar = $tiendasList[$k]->ean;
                            $albaranEdiLoc->cantidad = $numBultos * $udsBulto;
                            $albaranEdiLoc->save();
                            $localizaciones[] = $albaranEdiLoc;
                        }
                    }

                }
            }
        }

        $albaranEdi->save();

        $this->createEdiXml($albaranEdi, $paletsArr, $cajas, $lineasArr, $localizaciones);

    }

    private function createEdiXml($albaranEdi, $albaranEdiPalets, $albaranEdiCajas, $albaranEdiLin, $albaranEdiLoc) {
        $cab = new \SimpleXMLElement("<CAB></CAB>");
        $cab->addAttribute("IDCAB", 1);
        $cab->addAttribute("NUMDES", $albaranEdi->num_expedicion);
        $cab->addattribute("TIPO", $albaranEdi->tipo);
        $cab->addAttribute("FECENT", $albaranEdi->fecha_entrega);
        $cab->addAttribute("FECDES", $albaranEdi->fecha_expedicion);
        $cab->addAttribute("NUMALB", $albaranEdi->num_albaran);
        $cab->addAttribute("NUMPED", $albaranEdi->num_pedido);
        $cab->addAttribute("ORIGEN", $albaranEdi->origen);
        $cab->addAttribute("DESTINO", $albaranEdi->destino);
        $cab->addAttribute("PROVEEDOR", $albaranEdi->proveedor);
        $cab->addAttribute("COMPRADOR", $albaranEdi->comprador);
        $cab->addAttribute("DPTO", $albaranEdi->departamento);
        $cab->addAttribute("TOTQTY", $albaranEdi->totqty);
        $cab->addAttribute("IDENTIF", $albaranEdi->identif);

        $cps = 1;
        $idloc = 0;
        $idlin = 0;

        $embCamion = $cab->addChild('EMB', '');
        $embCamion->addAttribute("IDCAB", 1);
        $embCamion->addAttribute("IDEMB", $cps);

        $numPalets = floatval(count($albaranEdiPalets));
        $embCamion->addAttribute("CANTEMB", number_format($numPalets, 3, '.', ''));
        $embCamion->addAttribute("CPS", $cps);
        $embCamion->addAttribute("TIPEMB", 201);

        foreach($albaranEdiPalets as $palet) {
            $cps++;
            $cpsPalet = $cps;
            $embPalet = $cab->addChild("EMB", "");
            $embPalet->addAttribute("IDCAB", 1);
            $embPalet->addAttribute("IDEMB", $cps);
            $embPalet->addAttribute("CPS", $cps);
            $embPalet->addAttribute("CPSPADRE", 1);
            $cantemb = 0;
            foreach($albaranEdiCajas as $caja) {
                if($caja->idpalet == $palet->idpalet) {
                    $cantemb += $caja->cantidad;
                }
            }
            $embPalet->addAttribute("CANTEMB", number_format($cantemb, 3, '.', ''));
            $embPalet->addAttribute("TIPEMB", $palet->tipoEmb);
            $embPalet->addAttribute("SSCC1", $palet->sscc);
            $embPalet->addAttribute("TCAJAS", $cantemb);
            $embPalet->addAttribute("TIPO2", "CT");

            foreach($albaranEdiCajas as $caja) {
                for($i=0; $i<$caja->cantidad;$i++) {
                    $cps++;
                    $embCaja = $cab->addChild("EMB", "");
                    $embCaja->addAttribute("IDCAB", 1);
                    $embCaja->addAttribute("IDEMB", $cps);
                    $embCaja->addAttribute("CPS", $cps);
                    $embCaja->addAttribute("CPSPADRE", $cpsPalet);
                    $embCaja->addAttribute("CANTEMB", number_format(1.0, 3, '.', ''));
                    $embCaja->addAttribute("TIPEMB", "CT");
                    $sscc = intval(substr_replace($caja->sscc, '', -1));
                    $sscc+=$i;
                    $sscc .= $this->getSsccDigitControl($sscc);
                    $embCaja->addAttribute("SSCC1", $sscc);


                    foreach($albaranEdiLin as $linea) {
                        if($linea->idcaja == $caja->idcaja && $linea->idpalet == $palet->idpalet) {
                            $idlin++;
                            $lin = $embCaja->addChild("LIN", "");
                            $lin->addAttribute("IDCAB", 1);
                            $lin->addAttribute("IDEMB", $cps);
                            $udsLinea = $linea->cantidad_total/$caja->cantidad;
                            $lin->addAttribute("CENVFAC", number_format($udsLinea, 3, '.', ''));
                            $lin->addAttribute("CUEXP", number_format($udsLinea, 3, '.', ''));
                            $lin->addAttribute("DESCRIP", $linea->descripcion);
                            $lin->addAttribute("EAN", $linea->ean);
                            $lin->addAttribute("IDLIN", $idlin);
                            $lin->addAttribute("TIPART", "CU");

                            foreach($albaranEdiLoc as $index => $localizacion) {

                                if($localizacion->numlin == $linea->numlin && $localizacion->idcaja == $caja->idcaja && $localizacion->idpalet == $palet->idpalet) {
                                    $idloc++;
                                    $loc = $lin->addChild("LOC", "");
                                    $loc->addAttribute("IDCAB", 1);
                                    $loc->addAttribute("IDEMB", $cps);
                                    $loc->addAttribute("IDLIN", $idlin);
                                    $loc->addAttribute("IDLOC", $idloc);
                                    $loc->addAttribute("LUGAR", $localizacion->lugar);
                                    $loc->addAttribute("CANTIDAD", number_format($udsLinea, 3, '.', ''));
                                    $localizacion->cantidad -= $udsLinea;
                                    if(!$localizacion->cantidad)
                                        unset($albaranEdiLoc[$index]);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        $dom = dom_import_simplexml($cab)->ownerDocument;
        $dom->formatOutput = TRUE;
        $formatted = $dom->saveXML();
        $datetime = Carbon::create()->format("Ymdhis");
        file_put_contents("/ASPEDI/PRODUCCION/SALIDA/".$datetime.".xml", $formatted);
        exec("sh enviar_a_ediwin_asp.sh");
        return json_encode(["success"=>true]);

    }

    public function getAlbaranForEdi() {

        $in = \Request::all();
        $ejercicio = $in["ejercicio"];
        $cliente = $in["numCliente"];
        $numAlbaran = $in["numAlbaran"];
        $camiones = $in["numCamiones"];
        $albaran = new \stdClass();
        $pedido = new \stdClass();
        $linAlbaran = new \stdClass();
        $products = new \stdClass();
        $result = [];


        if(!$this->getAlbaran($ejercicio, $cliente, $numAlbaran, $albaran)) {
            return json_encode($albaran);
        }

        if(!$this->getPedido($ejercicio, $cliente, $albaran->numped, $pedido)) {
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

        //$bultos = $this->getBultos($cliente, $linAlbaran, $products);
        $clienteEdi = EdiClientes::where("cod_interno", $cliente)->where("cliente_logival", 1)->first();

        $comprador = $albaran->codter;
        $comprador = $comprador . $this->getSsccDigitControl($comprador);



        $pedidoEdi = EdiCabped::where("numped", $pedido->refped)
            ->where("comprador", $comprador)
            ->where("vendedor", $clienteEdi->ean)
            ->where("ejercicio", $ejercicio)
            ->first();

        $tiendas = EdiLoclped::distinct()->select('lugar')->where("cabped_id", $pedidoEdi->id)->get();


        $result["data"]["tiendasList"] = array();

        foreach($tiendas as $tienda) {

            $tiendaEdi = EdiClientes::where("ean", $tienda->lugar)->first();
            $result["data"]["tiendasList"][] = $tiendaEdi;
        }



        $result["success"] = true;
        $result["data"]["albaran"] = $albaran;
        $result["data"]["lin_albaran"] = $linAlbaran;
        $result["data"]["products"] = $products;
        //$result["data"]["bultos"] = $bultos;

        $result["data"]["numCamiones"] = $camiones;



        return $result;
    }

    private function getPedidoEdi($ejercicio, $codcli, $pedido_base) {

        $clienteEdi = EdiClientes::where("cod_interno", $codcli)->where("cliente_logival", 1)->first();
        $eanVendedor = $clienteEdi->ean;
        return EdiCabped::where("pedido_base", $pedido_base)->where("ejercicio", $ejercicio)->where("vendedor", $eanVendedor)->first();
    }

    private function getPedido($ejercicio, $cliente, $numPedido, &$pedido) {
        $query = "SELECT * FROM pedidos where codemp='1' and coddel='1' and codcli='$cliente' and tipped='S' and ejeped='$ejercicio' and numped='$numPedido'";


        $pedidoJson = Ctsql::ctsqlExport($query);
        $pedido = json_decode($pedidoJson[0]);

        if(!$pedido->success) {
            return false;
        }

        if(count($pedido->data) > 0)
            $pedido = $pedido->data[0];

        else $pedido = null;

        return true;
    }

    private function isXml($file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return ($ext == "xml" || $ext == "XML");
    }

    private function getNextSscc($codcli) {
        $sscc = \DB::table("albaran_edi_cajas")->select(\DB::raw("MAX(SUBSTRING(sscc, 1, CHAR_LENGTH(sscc) - 1)) AS sscc_no_digit"))->where("codcli", $codcli)->first();

        $sscc = $sscc->sscc_no_digit;
        if(!$sscc)
            $sscc = $this->getFirstSscc($codcli);

        else {
            $sscc++;
            $sscc.=$this->getSsccDigitControl($sscc);
        }

        return $sscc;

    }

    private function getFirstSscc($codcli) {
        $query = "SELECT * FROM clientes WHERE codemp = 1 AND codcli = $codcli";
        $cliente = Ctsql::ctsqlExport($query);

        $cliente = json_decode($cliente[0]);
        $cliente = $cliente->data[0];
        //$gcp = $cliente->codpro;
        $gcp = "843602146";
        $gcpNoPrefix = substr($gcp, 2);
        $countGcp = strlen($gcpNoPrefix);
        $totalToFill = 14-$countGcp;
        $sscc = "3".$gcp;
        for($i=0; $i<$totalToFill-1; $i++) {
            $sscc.="0";
        }
        $sscc.="1";
        $sscc.=$this->getSsccDigitControl($sscc);
        return $sscc;
    }

    private function getSsccDigitControl($sscc) {
        $controlDigit = 0;
        $acum = 0;

        for($i=0; $i<strlen($sscc); $i++) {
            $digit = substr($sscc, $i, 1);
            $digit = intval($digit);

            if((strlen($sscc)%2!=0 && $i%2==0) || (strlen($sscc)%2==0 && $i%2!=0)) {
                $acum += $digit * 3;
            }
            else if((strlen($sscc)%2!=0 && $i%2!=0) || (strlen($sscc)%2==0 && $i%2==0)) {
                $acum += $digit;
            }
        }


        while($acum%10 != 0) {
            $controlDigit++;
            $acum++;
        }

        return $controlDigit;
    }

    private function getAlbaran($ejercicio, $cliente, $numAlbaran, &$albaran) {

        $query = "SELECT * FROM albaran where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran'";


        $albaranJson = Ctsql::ctsqlExport($query);
        $albaran = json_decode($albaranJson[0]);

        if(!$albaran->success) {
            return false;
        }

        if(count($albaran->data) > 0)
            $albaran = $albaran->data[0];

        else $albaran = null;

        return true;
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

        $productsJson = Ctsql::ctsqlExport($query);
        $products = json_decode($productsJson[0]);
        if(!$products->success) {
            return false;
        }

        $products = $products->data;
        return true;
    }

    private function getLinAlbaran($ejercicio, $cliente, $numAlbaran, &$linAlbaran) {
        $query = "SELECT * FROM linalbar where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran' ORDER BY horizo ASC, vertic ASC";
        $linAlbarJson = Ctsql::ctsqlExport($query);
        $linAlbaran = json_decode($linAlbarJson[0]);

        if(!$linAlbaran->success) {
            return false;
        }

        $linAlbaran = $linAlbaran->data;


        return true;
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

    private function addAttributesToObject($objectXml, &$object) {
        foreach($objectXml->attributes() as $index=>$value) {
            $object->$index = $value;
        }
    }

    /**
     * @param $file
     */
    private function getOrderObject($file)
    {
        $xml = new \SimpleXMLElement(file_get_contents($file));
        $cabecera = new \StdClass();

        foreach ($xml->attributes() as $index => $value) {
            $cabecera->$index = $value;
        }

        $obser = new \StdClass();
        foreach($xml->OBSER as $obserXml) {
            $this->addAttributesToObject($obserXml, $obser);
        }



        $cabecera->LINEAS = [];
        foreach ($xml->LINEA as $lineaXml) {
            $linea = new \StdClass();
            $this->addAttributesToObject($lineaXml, $linea);
            $linea->LOCS = [];
            $linea->OBSERLS = [];

            foreach ($lineaXml->LOC as $locXml) {
                $loc = new \StdClass();
                $this->addAttributesToObject($locXml, $loc);
                $linea->LOCS[] = $loc;
            }

            foreach ($lineaXml->OBSERL as $obserXml) {
                $obser = new \StdClass();
                $this->addAttributesToObject($obserXml, $obser);
                $linea->OBSERLS[] = $obser;
            }

            $cabecera->LINEAS[] = $linea;
        }

        return $cabecera;
    }

    private function savePedido($pedido) {

        $cab = new EdiCabped();
        foreach(get_object_vars($pedido) as $index=>$var) {
            if(!is_array($var) && \Schema::hasColumn($cab->getTable(), $index)) {
                $index = strtolower($index);
                $cab->$index = $var;
            }
            if($index == "fecha") {
                $year = Carbon::createFromFormat("YmdHi", $var)->year;
                $cab->ejercicio = $year;
            }
        }

        $cab->save();


        foreach($pedido->LINEAS as $linea) {
            $lins = new EdiLinped();
            foreach(get_object_vars($linea) as $index => $var) {
                if (!is_array($var) && \Schema::hasColumn($lins->getTable(), $index)) {
                    $index = strtolower($index);
                    $lins->$index = $var;
                }
            }
            $lins->cabped_id = $cab->id;
            foreach($linea->LOCS as $loc) {
                $locDb = new EdiLoclped();
                foreach(get_object_vars($loc) as $index => $var) {
                    if (!is_array($var) && \Schema::hasColumn($locDb->getTable(), $index)) {
                        $index = strtolower($index);
                        $locDb->$index = $var;
                    }
                }
                $locDb->cabped_id = $cab->id;
                $locDb->save();
            }
            $lins->save();
        }

        $this->savePedidoToMultibase($cab);
    }

    private function savePedidoToMultibase($cabped) {

        $codcli = EdiClientes::where("ean", $cabped->vendedor)->first();
        $codcli = $codcli->cod_interno;
        $fechaPedido = Carbon::createFromFormat("YmdHi", $cabped->fecha);
        $fechaEntrega = Carbon::createFromFormat("YmdHi", $cabped->fechaere);
        $ejeped = $fechaPedido->year;
        $refped = intval($cabped->numped);
        $fecped = $fechaPedido->format("d/m/Y");
        $fecent = $fechaEntrega->format("d/m/Y");
        $comprador = EdiClientes::where("ean", $cabped->comprador)->first();
        $nomtec = $comprador->nombre;
        $nomfis = $comprador->nombre_fiscal;
        $dirtec = $comprador->direccion;
        $pobtec = $comprador->poblacion . " (".$comprador->provincia.")";
        $cpotec = $comprador->cp;
        $observ = $comprador->observaciones . " No. PEDIDO: ".$refped;
        $pobdis = $pobtec;
        $ctsql = "SELECT MAX(numped) as maxped FROM pedidos WHERE codcli=$codcli";
        $eanComprador = substr_replace($comprador->ean, '', -1);
        $maxPedido = Ctsql::ctsqlExport($ctsql);
        $maxPedido = json_decode($maxPedido[0]);
        if(count($maxPedido->data) < 1) {
            $numped = 1;
        }
        else {
            $numped = intval($maxPedido->data[0]->maxped) + 1;
        }

        $cabped->pedido_base = $numped;
        $cabped->save();


        $query = "INSERT INTO pedidos ("
            ."codemp, coddel, codcli, tipped, serped, ejeped, numped,"
            ."fecped, inddis, totbul, totkil, totvol,"
            ."reembo, imptot, nomtec, dirtec, pobtec, cpotec,"
            ."codtec, observ, transp, indser, reserv, fecent,"
            ."estado, envweb, pobdis, cpodis, nomfis, refped,"
            ."valora, apliva, tipiva, ejeope, numope, finope,"
            ."txtven, okpick"
            .") "
            ."VALUES "
            ."(1, 1, $codcli, 'S', '', $ejeped, $numped,"
            ."'$fecped', '', 0, 0, 0,"
            ."0, 0, '$nomtec', '$dirtec', '$pobtec', $cpotec,"
            ."'$eanComprador', '$observ', '', 'N', 'N', '$fecent',"
            ."'', '', '$pobdis', 0, '$nomfis', '$refped',"
            ."'N', 'N', 0, 0, 0, '',"
            ."'', ''"
            .")";

        Ctsql::ctsqlImport($query);
        $this->saveLineasPedido($cabped, $codcli, $numped);

    }

    private function saveLineasPedido($cabped, $codcli, $numped) {

        $fechaPedido = Carbon::createFromFormat("YmdHi", $cabped->fecha);
        $ejeped = $fechaPedido->year;

        $linsPed = EdiLinped::where("cabped_id", $cabped->id)->get();

        foreach($linsPed as $linPed) {

            $numlin = $linPed->clave2;
            $sku = $linPed->refcli;
            $cantid = $linPed->cantped;
            $descri = $linPed->descmer;

            $ctsql = "SELECT * FROM artic WHERE codcli=$codcli and codart='$sku'";
            $result = Ctsql::ctsqlExport($ctsql);
            $result = json_decode($result[0]);

            if(count($result->data) < 1) {
                $this->adviseNoArtic($sku, $descri, $cantid, $numped, $codcli);
            }

            else  {
                $descri = $result->data[0]->descri;

                $query = "INSERT INTO linpedidos ("
                    . "codemp, coddel, codcli, tipped, serped, ejeped, numped, numlin,"
                    . "codart, cantid, bultos, kilos, volume, precio, dtoli1,"
                    . "dtoli2, descri, estado, tipdoc, tipiva, edilin, asocia,"
                    . "nopick, lnpick, codkit)"
                    . "VALUES"
                    . "(1, 1, $codcli, 'S', '', $ejeped, $numped, $numlin,"
                    . "'$sku', $cantid, 0, 0, 0, 0, 0,"
                    . "0, '$descri', '', 'P', 0, 'S', 0,"
                    . "0, 0, '')";

                Ctsql::ctsqlImport($query);
            }

        }

    }

    private function existsArtic($codart, $codcli) {
        $result = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codart = $codart AND codcli= $codcli");
        $data = json_decode($result[0]);
        return count($data) > 0;
    }

    private function adviseNoArtic($codart, $descri, $cantid, $numped, $codcli) {

        $data["codart"] = $codart;
        $data["descri"] = $descri;
        $data["cantid"] = $cantid;
        $data["numped"] = $numped;
        $data["codcli"] = $codcli;

        \Mail::send("emails.orders.edi-no-artic", $data, function ($message) use ($data)  {
            $message->from("noreply@logival.es", "Logival Avisos");
            $message->to("admon@logival.es", "Yolanda");
            $message->subject("Pedido EDI con artículo erróneo");
        });
    }

    public function getTest() {
        $query = "SELECT * FROM albaran WHERE numalb=1 and codcli=176";
        $result = Ctsql::ctsqlExport($query);
        $result = json_decode($result[0]);


        dd($result);
    }

}