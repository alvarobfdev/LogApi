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
use App\AlbaranFisicoEdi;
use App\AlbaranLineasFisicoEdi;
use App\Ctsql;
use App\EdiCabped;
use App\EdiClientes;
use App\EdiLinped;
use App\EdiLoclped;
use App\EquivalenciaCodigosPlataformas;
use App\ProductsEdiModel;
use Carbon\Carbon;
use Faker\Provider\File;

class EdiController extends Controller
{

    public static $toybagsCod = 176;

    public static $sscc = null;

    private $productsNotFound = [];

    public function getAlbaranPdf($numcli, $ejerci, $numalb, $html = null) {


        $data["albaran"] = AlbaranFisicoEdi::where("codcli", $numcli)->where("num_albaran", $numalb)->where("ejerci", $ejerci)->first();
        $data["lineas"] = AlbaranLineasFisicoEdi::where("codcli", $numcli)->where("num_albaran", $numalb)->get();
        $data["cliente"] = EdiClientes::where("cod_interno", $numcli)->where("cliente_logival", 1)->first();



        $view = view("albaran.albaran-pdf", $data);
        if($html) {
            return $view;
        }
        else return \PDF::loadHTML($view)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-top', 0)
            ->stream();



    }

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

    public function removeAlbaranEdi($codcli, $ejerci, $numalb) {

        AlbaranEdi::where("codcli", $codcli)->where("ejerci", $ejerci)->where("numalb", $numalb)->delete();
        AlbaranEdiCajas::where("codcli", $codcli)->where("ejerci", $ejerci)->where("numalb", $numalb)->delete();
        AlbaranEdiLineas::where("codcli", $codcli)->where("ejerci", $ejerci)->where("numalb", $numalb)->delete();
        AlbaranEdiLocalizaciones::where("codcli", $codcli)->where("ejerci", $ejerci)->where("numalb", $numalb)->delete();
        AlbaranEdiPalets::where("codcli", $codcli)->where("ejerci", $ejerci)->where("numalb", $numalb)->delete();
        AlbaranFisicoEdi::where("codcli", $codcli)->where("ejerci", $ejerci)->where("num_albaran", $numalb)->delete();
        AlbaranLineasFisicoEdi::where("codcli", $codcli)->where("ejerci", $ejerci)->where("num_albaran", $numalb)->delete();


    }

    public function postFinishExportEdi() {

        
        $palets = \Request::get("palets");
        $albaran = \Request::get("albaran");
        $tipoPalets = \Request::get("tipoPalets");
        $tiendasList = \Request::get("tiendasList");
        $lineas = \Request::get("lineas");
        $bultosCapas = \Request::get("bultosCapas");
        $modify = \Request::get("modify");
        $locs = \Request::get("locs");



        $albaranEdi = new AlbaranEdi();
        $albaranEdi->palets_json = $palets;
        $albaranEdi->albaran_json = $albaran;
        $albaranEdi->tipo_palets_json = $tipoPalets;
        $albaranEdi->tiendas_list_json = $tiendasList;
        $albaranEdi->lineas_json = $lineas;
        $albaranEdi->bultos_capa_json = $bultosCapas;
        $albaranEdi->locs_json = $locs;

        $palets = json_decode($palets);
        $albaran = json_decode($albaran);
        $tipoPalets = json_decode($tipoPalets);
        $tiendasList = json_decode($tiendasList);
        $lineas = json_decode($lineas);
        $bultosCapas = json_decode($bultosCapas);
        $pedido = new \StdClass();


        $numSerie = "";
        if($albaran->seralb != "") {
            $numSerie = $albaran->seralb;
        }

        $ejerShort = substr($albaran->ejerci, -2);


        $albaranAsArg = $numSerie.$ejerShort.$albaran->numalb;

        if($modify == "true") {
            $this->removeAlbaranEdi($albaran->codcli, $albaran->ejerci, $albaranAsArg);
        }



        $this->getPedido($albaran->ejeped, $albaran->codcli, $albaran->numped, $pedido);

        $albaranEdi->num_expedicion = $numSerie.$ejerShort.$albaran->numalb;
        $pedidoEdi = $this->getPedidoEdi($albaran->ejeped, $albaran->codcli, $albaran->numped);

        if($pedidoEdi->nodo == "YB1") {
            $albaranEdi->tipo = "YA5";
        }
        else {
            $albaranEdi->tipo = "351";
        }



        $albaranEdi->fecha_expedicion = Carbon::parse($albaran->fecalb)->format("YmdHi");
        $albaranEdi->fecha_entrega = Carbon::parse($pedido->fecent)->format("YmdHi");
        $albaranEdi->num_albaran = $numSerie.$ejerShort.$albaran->numalb;
        $albaranEdi->numalb = $numSerie.$ejerShort.$albaran->numalb;

        $albaranEdi->num_pedido = $albaran->numped;
        $albaranEdi->pedido_ref = $pedidoEdi->numped;
        $albaranEdi->origen = "8473098842005";
        $comprador = EdiClientes::where("ean", $pedidoEdi->comprador)->first();
        if($comprador && $comprador->nombre_fiscal=="EL CORTE INGLÉS, S.A.") {
            $albaranEdi->destino = "8422416000016";
        }
        if($comprador && $comprador->nombre_fiscal=="DIA S.A") {
            $albaranEdi->destino = "8480017300003";
        }
        else $albaranEdi->destino = $pedidoEdi->comprador;
        $albaranEdi->proveedor = $pedidoEdi->vendedor;
        $albaranEdi->comprador = $pedidoEdi->comprador;
        $albaranEdi->departamento = $pedidoEdi->depto;
        $albaranEdi->receptor = $pedidoEdi->receptor;
        $albaranEdi->totqty = 0;


        $albaranEdi->codcli = $albaran->codcli;
        $albaranEdi->ejerci = $albaran->ejerci;
        $paletsArr = array();
        $cajas = array();
        $lineasArr = array();
        $localizaciones = array();
        $numPalets = count($palets);

        for($i=0; $i<$numPalets; $i++) {
            $albaranEdiPalets = new AlbaranEdiPalets();

            $albaranEdiPalets->codcli = $albaran->codcli;
            $albaranEdiPalets->ejerci = $albaran->ejerci;
            $albaranEdiPalets->numalb = $albaranEdi->num_albaran;
            $albaranEdiPalets->idpalet = $i+1;
            $albaranEdiPalets->tipoEmb = $tipoPalets[$i];
            $albaranEdiPalets->sscc = $this->getNextSscc($albaran->codcli);
            $albaranEdiPalets->save();
            $paletsArr[] = $albaranEdiPalets;


            for($j=0; $j<count($palets[$i]); $j++) {
                if(is_array($palets[$i][$j]))
                    $numBultos = array_sum($palets[$i][$j]);
                else $numBultos = $palets[$i][$j];
                if($numBultos>0) {
                    $albaranEdiCajas = new AlbaranEdiCajas();

                    $albaranEdiCajas->codcli = $albaran->codcli;
                    $albaranEdiCajas->ejerci = $albaran->ejerci;
                    $albaranEdiCajas->numalb = $albaranEdi->num_albaran;
                    $albaranEdiCajas->idpalet = $i + 1;
                    $albaranEdiCajas->idcaja = $j + 1;
                    $albaranEdiCajas->sscc = $this->getNextSscc($albaran->codcli, $numBultos);
                    $albaranEdiCajas->cantidad = $numBultos;
                    $albaranEdiCajas->bultosCapas = $bultosCapas[$i][$j];
                    $albaranEdiCajas->formato = $lineas[$j]->formato;
                    $albaranEdiCajas->save();
                    $cajas[] = $albaranEdiCajas;

                    $query = "SELECT descri, codbar, codart FROM artic WHERE codemp=1 and codcli = {$lineas[$j]->codcli} and codart = '{$lineas[$j]->codart}'";
                    $result = Ctsql::ctsqlExport($query);
                    $result = json_decode($result[0]);
                    $articulo = $result->data[0];

                    $query = "SELECT * FROM linalbar WHERE codemp=1 AND coddel = 1 AND codcli = ".$albaran->codcli." AND tipalb = 'S' AND ejerci = ".$albaran->ejerci.
                        "AND numalb='".$albaran->numalb . "' AND numlin = ".($j+1);

                    if($numSerie != "") {
                        $query .= " AND seralb = '$numSerie'";
                    }

                    $articulos = Ctsql::ctsqlExport($query);
                    $articulos = json_decode($articulos[0]);
                    $articuloLinea = $articulos->data[0];


                    $udsBulto = $lineas[$j]->cantid/$lineas[$j]->bultos;

                    $albaranEdiLineas = new AlbaranEdiLineas();

                    $albaranEdiLineas->codcli = $albaran->codcli;
                    $albaranEdiLineas->ejerci = $albaran->ejerci;
                    $albaranEdiLineas->numalb = $albaranEdi->num_albaran;
                    $albaranEdiLineas->idpalet = $i + 1;
                    $albaranEdiLineas->idcaja = $j + 1;
                    $albaranEdiLineas->numlin = 1;
                    $albaranEdiLineas->cantidad_total = $udsBulto * $numBultos;
                    $albaranEdi->totqty += $udsBulto * $numBultos;
                    $albaranEdiLineas->descripcion = $articulo->descri;
                    $albaranEdiLineas->ean = $articulo->codbar;
                    $albaranEdiLineas->referencia = $articulo->codart;
                    $albaranEdiLineas->uds_bulto = $udsBulto;
                    $albaranEdiLineas->bultos = $numBultos;
                    $albaranEdiLineas->lote = $articuloLinea->lotefb;
                    $albaranEdiLineas->save();
                    $lineasArr[] = $albaranEdiLineas;

                    for($k=0; $k<count($palets[$i][$j]); $k++) {
                        $numBultos = $palets[$i][$j][$k];

                        if($numBultos > 0 ){
                            $albaranEdiLoc = new AlbaranEdiLocalizaciones();

                            $albaranEdiLoc->codcli = $albaran->codcli;
                            $albaranEdiLoc->ejerci = $albaran->ejerci;
                            $albaranEdiLoc->numalb = $albaranEdi->num_albaran;
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
        $this->createAlbaranFisico($albaranEdi, $albaran, $lineasArr);
        $this->createEdiXml($albaranEdi, $paletsArr, $cajas, $lineasArr, $localizaciones, $pedidoEdi);
        return json_encode(["result"=>"success"]);

    }

    private function createAlbaranFisico($albaranEdi, $albaran, $lineasArr) {
        $albaranFisico = new AlbaranFisicoEdi();

        $albaranFisico->codcli = $albaran->codcli;
        $albaranFisico->ejerci = $albaran->ejerci;
        $albaranFisico->num_albaran = $albaranEdi->num_albaran;

        $clienteEdi = EdiClientes::where("cod_interno", $albaran->codcli)->where("cliente_logival", 1)->first();
        $entrageEdi = EdiClientes::where("ean", $albaranEdi->receptor)->first();
        $pideEdi = EdiClientes::where("ean", $albaranEdi->comprador)->first();

        $lineas = AlbaranEdiLineas::where("codcli", $albaranEdi->codcli)
            ->where("ejerci", $albaranEdi->ejerci)
            ->where("numalb", $albaranEdi->num_albaran)->get();

        $numBultos = 0;
        foreach($lineas as $linea) {
            $numBultos += $linea->bultos;
        }

        $numPalets = AlbaranEdiPalets::where("codcli", $albaranEdi->codcli)
            ->where("ejerci", $albaranEdi->ejerci)
            ->where("numalb", $albaranEdi->num_albaran)->count();

        if($albaranEdi->comprador == "8480010023213")
            $albaranFisico->codcli_proveedor = $clienteEdi->cod_eroski;

        $albaranFisico->razon_proveedor = $clienteEdi->nombre;
        $albaranFisico->direccion_proveedor = $clienteEdi->direccion;
        $albaranFisico->poblacion_proveedor = $clienteEdi->poblacion;
        $albaranFisico->provincia_proveedor = $clienteEdi->provincia;
        $albaranFisico->cp_proveedor = $clienteEdi->cp;
        $albaranFisico->telf_proveedor = $clienteEdi->tfno;
        $albaranFisico->fax_proveedor = $clienteEdi->fax;
        $albaranFisico->email_proveedor = $clienteEdi->email;
        $albaranFisico->fecha_entrega = Carbon::createFromFormat("YmdHi", $albaranEdi->fecha_entrega)->toDateTimeString();
        $albaranFisico->num_pedido_logival = $albaran->numped;
        $albaranFisico->num_pedido_cliente = $albaranEdi->pedido_ref;
        $albaranFisico->punto_operacional_proveedor = $clienteEdi->ean;
        $albaranFisico->codigo_entrega = $entrageEdi->cod_interno;
        $albaranFisico->nombre_entrega = $entrageEdi->nombre;
        $albaranFisico->direccion_entrega = $entrageEdi->direccion;
        $albaranFisico->cp_entrega = $entrageEdi->cp;
        $albaranFisico->provincia_entrega = $entrageEdi->provincia;
        $albaranFisico->poblacion_entrega = $entrageEdi->poblacion;
        $albaranFisico->codigo_pide = $pideEdi->cod_interno;
        $albaranFisico->nombre_pide = $pideEdi->nombre;
        $albaranFisico->direccion_pide = $pideEdi->direccion;
        $albaranFisico->cp_pide = $pideEdi->cp;
        $albaranFisico->provincia_pide = $pideEdi->provincia;
        $albaranFisico->poblacion_pide = $pideEdi->poblacion;
        $albaranFisico->depto_compra = $albaranEdi->departamento;
        $albaranFisico->logo_proveedor = $clienteEdi->logo;
        $albaranFisico->num_bultos = $numBultos;
        $albaranFisico->num_palets = $numPalets;
        $albaranFisico->save();

        $this->createLineasAlbaranFisico($albaran, $lineasArr, $albaranEdi);
    }

    private function createLineasAlbaranFisico($albaran, $lineasArr, $albaranEdi) {

        $lineasFis = [];
        foreach($lineasArr as $linea) {
            if(!array_key_exists($linea->ean, $lineasFis)) {
                $lineasFis[$linea->ean]["bultos"] = 0;
                $lineasFis[$linea->ean]["cantidad_total"] = 0;
            }
            $lineasFis[$linea->ean]["bultos"]+=$linea->bultos;
            $lineasFis[$linea->ean]["cantidad_total"]+=$linea->cantidad_total;
            $lineasFis[$linea->ean]["referencia"] = $linea->referencia;
            $lineasFis[$linea->ean]["uds_bulto"] = $linea->uds_bulto;
            $lineasFis[$linea->ean]["descripcion"] = $linea->descripcion;
        }
        foreach($lineasFis as $ean=>$linea) {
            $lineaFis = new AlbaranLineasFisicoEdi();
            $lineaFis->codcli = $albaran->codcli;
            $lineaFis->ejerci = $albaran->ejerci;
            $lineaFis->num_albaran = $albaranEdi->num_albaran;
            $lineaFis->referencia = $linea['referencia'];
            $lineaFis->bultos = $linea["bultos"];
            $lineaFis->uds_bulto = $linea["uds_bulto"];
            $lineaFis->uds_totales = $linea["cantidad_total"];
            $lineaFis->descripcion = $linea["descripcion"];
            $lineaFis->ean = $ean;
            $lineaFis->save();
        }
    }


    private function createEdiXml($albaranEdi, $albaranEdiPalets, $albaranEdiCajas, $albaranEdiLin, $albaranEdiLoc, $pedidoEdi) {
        $cab = new \SimpleXMLElement("<CAB></CAB>");
        $cab->addAttribute("IDCAB", 1);
        $cab->addAttribute("NUMDES", $albaranEdi->num_expedicion);
        $cab->addattribute("TIPO", $albaranEdi->tipo);
        $cab->addAttribute("FECENT", $albaranEdi->fecha_entrega);
        $cab->addAttribute("FECDES", $albaranEdi->fecha_expedicion);
        $cab->addAttribute("NUMALB", $albaranEdi->num_albaran);
        $cab->addAttribute("NUMPED", $albaranEdi->pedido_ref);
        $cab->addAttribute("ORIGEN", $albaranEdi->origen);
        $cab->addAttribute("DESTINO", $albaranEdi->destino);
        $cab->addAttribute("PROVEEDOR", $albaranEdi->proveedor);
        $cab->addAttribute("COMPRADOR", $albaranEdi->comprador);
        $cab->addAttribute("RECEPTOR", $albaranEdi->receptor);
        $cab->addAttribute("DPTO", $albaranEdi->departamento);
        $cab->addAttribute("TOTQTY", $albaranEdi->totqty);
        $cab->addAttribute("IDENTIF", $albaranEdi->identif);
        $cab->addAttribute("MATRIC", $albaranEdi->matricula_transportista);
        $cab->addAttribute("ETAPATRANS", "20");
        $cab->addAttribute("TIPTRANS", "30");



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
            $numCajas = floatval(count($albaranEdiCajas));
            $embPalet->addAttribute("CANTEMB", number_format($cantemb, 3, '.', ''));
            $embPalet->addAttribute("TIPEMB", "CT");
            $embPalet->addAttribute("SSCC1", $palet->sscc);
            $embPalet->addAttribute("TCAJAS", $cantemb);
            $embPalet->addAttribute("TIPO2", "CT");

            foreach($albaranEdiCajas as $caja) {
                for($i=0; $i<$caja->cantidad;$i++) {
                    if($caja->idpalet == $palet->idpalet) {
                        $cps++;
                        $embCaja = $cab->addChild("EMB", "");
                        $embCaja->addAttribute("IDCAB", 1);
                        $embCaja->addAttribute("IDEMB", $cps);
                        $embCaja->addAttribute("CPS", $cps);
                        $embCaja->addAttribute("CPSPADRE", $cpsPalet);
                        $embCaja->addAttribute("CANTEMB", number_format(1.0, 3, '.', ''));
                        $embCaja->addAttribute("TIPEMB", "CT");
                        $embCaja->addAttribute("FORMATO", $caja->formato);
                        $numFormatos = $this->getNumFormatos($albaranEdiLin, $albaranEdiCajas, $caja, $palet);
                        if($numFormatos > 0)
                            $embCaja->addAttribute("NUMFORMATO", $numFormatos);
                        $embCaja->addAttribute("UMEDIDA", "PCE");
                        if($caja->formato == "201" || $caja->formato == "200") {
                            $bultform = $cantemb;
                        }
                        else {
                            $bultform = $caja->bultosCapas;
                        }
                        $embCaja->addAttribute("BULTFORM", $bultform);
                        $embCaja->addAttribute("BULTPALET", $cantemb);


                        $sscc = intval(substr_replace($caja->sscc, '', -1));
                        $sscc += $i;
                        $sscc .= $this->getSsccDigitControl($sscc);
                        $embCaja->addAttribute("SSCC1", $sscc);


                        foreach ($albaranEdiLin as $linea) {
                            if ($linea->idcaja == $caja->idcaja && $linea->idpalet == $palet->idpalet) {
                                $idlin++;
                                $lin = $embCaja->addChild("LIN", "");
                                $lin->addAttribute("IDCAB", 1);
                                $lin->addAttribute("IDEMB", $cps);
                                $udsLinea = $linea->cantidad_total / $caja->cantidad;
                                $lin->addAttribute("CENVFAC", number_format($udsLinea, 3, '.', ''));
                                $lin->addAttribute("CUEXP", number_format($udsLinea, 3, '.', ''));
                                $lin->addAttribute("DESCRIP", $linea->descripcion);
                                $lin->addAttribute("EAN", $linea->ean);
                                $lin->addAttribute("IDLIN", $idlin);
                                $lin->addAttribute("TIPART", "CU");
                                $lin->addAttribute("REFCLI", $linea->referencia);
                                $lin->addAttribute("NUMEXP", $this->calculateDun($linea->ean));
                                $lin->addAttribute("NUMPED", $albaranEdi->pedido_ref);
                                $linPedido = EdiLinped::where("refean", $linea->ean)->where("cabped_id", $pedidoEdi->id)->first();
                                $lin->addAttribute("NUMLINPED", $linPedido->clave2);

                                $lin->addAttribute("LOTE", $linea->lote);
                                $lin->addAttribute("PROPMERC", $albaranEdi->proveedor);


                                foreach ($albaranEdiLoc as $index => $localizacion) {

                                    if ($localizacion->numlin == $linea->numlin && $localizacion->idcaja == $caja->idcaja && $localizacion->idpalet == $palet->idpalet) {
                                        $idloc++;
                                        $loc = $lin->addChild("LOC", "");
                                        $loc->addAttribute("IDCAB", 1);
                                        $loc->addAttribute("IDEMB", $cps);
                                        $loc->addAttribute("IDLIN", $idlin);
                                        $loc->addAttribute("IDLOC", $idloc);
                                        $loc->addAttribute("LUGAR", $localizacion->lugar);
                                        $loc->addAttribute("CANTIDAD", number_format($udsLinea, 3, '.', ''));
                                        $localizacion->cantidad -= $udsLinea;
                                        if (!$localizacion->cantidad)
                                            unset($albaranEdiLoc[$index]);
                                        break;
                                    }
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
        //file_put_contents(storage_path("app/tmp/").$datetime.".xml", $formatted);
        exec("cd /ASPEDI && ./enviar_a_ediwin_asp.sh", $output);
    }

    private function getAlbaranEdi($cliente, $ejercicio, $numAlbaran, $numSerie) {

        $ejerShort = substr($ejercicio, -2);
        


        return AlbaranEdi::where("codcli", $cliente)
            ->where("ejerci", $ejercicio)
            ->where("numalb", $numSerie.$ejerShort.$numAlbaran)->first();
    }

    public function getAlbaranForEdi() {

        $in = \Request::all();
        $ejercicio = $in["ejercicio"];
        $cliente = $in["numCliente"];
        $numAlbaran = $in["numAlbaran"];
        $camiones = $in["numCamiones"];
        $numSerie = $in["numSerie"];
        $albaran = new \stdClass();
        $pedido = new \stdClass();
        $linAlbaran = new \stdClass();
        $products = new \stdClass();
        $result = [];



        if(!$this->getAlbaran($ejercicio, $cliente, $numAlbaran, $albaran, $numSerie)) {
            return json_encode($albaran);
        }

        if(!$this->getPedido($ejercicio, $cliente, $albaran->numped, $pedido)) {
            return json_encode($albaran);
        }

        if(!$this->getLinAlbaran($ejercicio, $cliente, $numAlbaran, $linAlbaran, $numSerie)) {
            return json_encode($linAlbaran);
        }

        $cods = [];
        foreach($linAlbaran as $lin) {
            $cods[] = $lin->codart;
        }

        if(!$this->getProducts($cliente, $cods, $products)) {
            return json_encode($products);
        }

        if($albaranEdi = $this->getAlbaranEdi($cliente, $ejercicio, $numAlbaran, $numSerie)) {
            $result["success"] = true;
            $result["modify"] = true;
            $result["data"]["lin_albaran"] = json_decode($albaranEdi->lineas_json);
            $result["data"]["albaran"] = json_decode($albaranEdi->albaran_json);
            $result["data"]["products"] = $products;
            $result["data"]["bultosCapas"] = json_decode($albaranEdi->bultos_capa_json);
            $result["data"]["tipoPalets"] = json_decode($albaranEdi->tipo_palets_json);
            $result["data"]["tiendasList"] = json_decode($albaranEdi->tiendas_list_json);
            $result["data"]["palets"] = json_decode($albaranEdi->palets_json);
            $result["data"]["locs"] = json_decode($albaranEdi->locs_json);

            return $result;
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

        foreach($linAlbaran as &$lin) {
            $linEdi = EdiLinped::where("cabped_id", $pedidoEdi->id)->where("refcli", $lin->codart)->first();
            $lin->formato = $linEdi->formato;
            $lin->udsbul = $lin->cantid / $lin->bultos;

        }


        $tiendas = \DB::connection("mysql")->select( \DB::raw("select loc.* from edi_loclped loc
            inner join edi_clientes cli on loc.lugar = cli.ean  where loc.cabped_id = {$pedidoEdi->id} group by lugar order by cli.cod_interno asc
         ") );


        $ejerShort = substr($albaran->ejerci, -2);

        $locs_registered = \DB::connection("mysql")->select( \DB::raw("SELECT * FROM albaran_edi_localizaciones WHERE numalb LIKE '%$ejerShort$numAlbaran' ") );



        $result["data"]["tiendasList"] = array();

        $faltanTiendas = array();

        foreach($tiendas as $tienda) {

            $tiendaEdi = EdiClientes::where("ean", $tienda->lugar)->first();
            if(!$tiendaEdi) {
                $faltanTiendas[] = $tienda->lugar;
            }


            $result["data"]["tiendasList"][] = $tiendaEdi;

        }

        $locs = EdiLoclped::where("cabped_id", $pedidoEdi->id)->get();

        foreach($locs as &$loc) {
            $linped = EdiLinped::where("cabped_id", $pedidoEdi->id)->where("clave2", $loc->clave2)->first();
            $loc->prod = $linped->refean;
            $loc->codart = $this->getCodigoArticulo($linped->refean, $products);
            foreach($locs_registered as $loc_registered) {
                if($loc_registered->lugar == $loc->lugar) {
                    $loc->cantidad -= $loc_registered->cantidad;
                }
            }
        }

        if(count($faltanTiendas) > 0) {
            $result["success"] = false;
            $result["error"] = "Faltan las siguientes tiendas en el sistema:\n".implode("\n", $faltanTiendas);
            return $result;
        }


        $result["success"] = true;
        $result["modify"] = false;
        $result["data"]["albaran"] = $albaran;
        $result["data"]["lin_albaran"] = $linAlbaran;
        $result["data"]["products"] = $products;
        $result["data"]["locs"] = $locs;
        //$result["data"]["bultos"] = $bultos;

        $result["data"]["numCamiones"] = $camiones;



        return $result;
    }

    private function getCodigoArticulo($ean, $products) {
        foreach($products as $product) {
            if($product->codbar == $ean) {
                return $product->codart;
            }
        }

        return "";
    }



    private function getNumFormatos($linAlbaran, $cajasAlbaran, $caja, $palet) {

        $palets = [];
        $capas = 0;
        $ean = "";
        foreach($linAlbaran as $linea) {
            if($linea->idcaja == $caja->idcaja && $palet->idpalet == $linea->idpalet) {
                $ean = $linea->ean;
            }
        }

        foreach($linAlbaran as $linea) {
            if($linea->ean == $ean) {
                $palets[$linea->idpalet] = 1;
                foreach($cajasAlbaran as $cajaAlbaran) {
                    if($cajaAlbaran->idpalet == $linea->idpalet && $linea->idcaja == $cajaAlbaran->idcaja) {
                        if($cajaAlbaran->bultosCapas == 0) {
                            return 0;
                        }
                        $capas += ceil($cajaAlbaran->cantidad / $cajaAlbaran->bultosCapas);
                    }
                }
            }
        }

        if($caja->formato == "201" || $caja->formato == "200") {
            return array_sum($palets);
        }
        else return $capas;

    }

    private function calculateDun($ean) {
        $ean = substr($ean,0,-1);
        $dun = "1" . $ean;
        $dun .= $this->getSsccDigitControl($dun);
        return $dun;
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

    private function getNextSscc($codcli, $cant = 1) {

        if(!self::$sscc) {
            $ssccCaja = \DB::connection('mysql')->table("albaran_edi_cajas")->select(\DB::connection('mysql')->raw("MAX(SUBSTRING(sscc, 1, CHAR_LENGTH(sscc) - 1)) AS sscc_no_digit"), "cantidad")->where("codcli", $codcli)->first();

            if($ssccCaja->sscc_no_digit) {
                $nextSccc = $ssccCaja->sscc_no_digit + $ssccCaja->cantidad;
            }

            else $nextSccc = $this->getFirstSscc($codcli);

            self::$sscc = $nextSccc;
        }
        else {
            $nextSccc = self::$sscc+1;
            self::$sscc += $cant;

        }

        $sscc = $nextSccc.$this->getSsccDigitControl($nextSccc);
        return $sscc;

    }

    private function getFirstSscc($codcli) {

        $cliente = EdiClientes::where("cod_interno", $codcli)->where("cliente_logival", 1)->first();
        $gcp = $cliente->gcp;
        $countGcp = strlen($gcp);
        $totalToFill = 16-$countGcp;
        $sscc = "3".$gcp;
        for($i=0; $i<$totalToFill-1; $i++) {
            $sscc.="0";
        }
        $sscc.="1";
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

    private function getAlbaran($ejercicio, $cliente, $numAlbaran, &$albaran, $numSerie) {

        $query = "SELECT * FROM albaran where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran'";

        if($numSerie != "") {
            $query .= " AND seralb = '$numSerie'";
        }

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

    private function getLinAlbaran($ejercicio, $cliente, $numAlbaran, &$linAlbaran, $numSerie) {

        $query = "";
        if($numSerie != "") {
            $query = " AND seralb = '$numSerie' ";
        }

        $query = "SELECT * FROM linalbar where codemp='1' and coddel='1' and codcli='$cliente' and tipalb='S' and ejerci='$ejercicio' and numalb='$numAlbaran' $query ORDER BY horizo ASC, vertic ASC";
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
            if(!is_array($var) && \Schema::connection('mysql')->hasColumn($cab->getTable(), $index)) {
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
                if (!is_array($var) && \Schema::connection('mysql')->hasColumn($lins->getTable(), $index)) {
                    $index = strtolower($index);
                    $lins->$index = $var;
                }
            }
            $lins->cabped_id = $cab->id;
            foreach($linea->LOCS as $loc) {
                $locDb = new EdiLoclped();
                foreach(get_object_vars($loc) as $index => $var) {
                    if (!is_array($var) && \Schema::connection('mysql')->hasColumn($locDb->getTable(), $index)) {
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
        $receptor = EdiClientes::where("ean", $cabped->receptor)->first();
        $nomtec = $receptor->nombre;
        $nomfis = $receptor->nombre_fiscal;
        $dirtec = $receptor->direccion;
        $pobtec = $receptor->poblacion . " (".$receptor->provincia.")";
        $cpotec = $receptor->cp;
        $observ = $receptor->observaciones . " No. PEDIDO: ".$refped;
        if($receptor->tfno) {
            $observ .= " TFNO: ".$receptor->tfno;
        }
        $pobdis = $pobtec;
        $ctsql = "SELECT MAX(numped) as maxped FROM pedidos WHERE codcli=$codcli";
        $eanReceptor = substr_replace($receptor->ean, '', -1);
        $maxPedido = Ctsql::ctsqlExport($ctsql);
        $maxPedido = json_decode($maxPedido[0]);
        if(count($maxPedido->data) < 1) {
            $numped = 1;
        }
        else {
            $numped = intval($maxPedido->data[0]->maxped) + 1;
        }

        $comprador = EdiClientes::where("ean", $cabped->comprador)->first();

        if(!$comprador) {

            $linsPed = EdiLinped::where("cabped_id", $cabped->id)->get();
            foreach($linsPed as $linPed) {
                $linPed->delete();
            }
            $cabped->delete();
            die("NO EXISTE COMPRADOR");
        }

        $eanComprador = $comprador->ean;

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
            ."0, 0, '$nomtec', '$dirtec', '$pobtec', '$cpotec',"
            ."'$eanComprador', '$observ', '', 'N', 'N', '$fecent',"
            ."'', '', '$pobdis', 0, '$nomfis', '$refped',"
            ."'N', 'N', 0, 0, 0, '',"
            ."'', ''"
            .")";

        Ctsql::ctsqlImport($query);
        $this->productsNotFound = [];
        $this->saveLineasPedido($cabped, $codcli, $numped);
        $this->checkNotFoundProducts();

    }

    private function checkNotFoundProducts() {
        if(count($this->productsNotFound) > 0) {
            \Mail::send("emails.orders.edi-no-artic", ["data"=>$this->productsNotFound], function ($message)  {
                $message->from("noreply@logival.es", "Logival Avisos");
                $message->to("admon@logival.es", "Yolanda");
                $message->subject("Pedido EDI con artículos erróneos");
            });
        }
    }

    private function saveLineasPedido($cabped, $codcli, $numped) {

        $fechaPedido = Carbon::createFromFormat("YmdHi", $cabped->fecha);
        $ejeped = $fechaPedido->year;

        $linsPed = EdiLinped::where("cabped_id", $cabped->id)->get();

        foreach($linsPed as $linPed) {

            $numlin = $linPed->clave2;
            $sku = $linPed->refcli;
            $sku = ltrim($sku, '0');
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
        $data["codcli"] = $codcli;

        $this->productsNotFound[$numped][] = $data;
    }

    public function getExtractAddressData() {
        /*$query = "SELECT * FROM linalbar WHERE numalb=2 and codcli=176";
        $result = Ctsql::ctsqlExport($query);
        $result = json_decode($result[0]);


        dd($result);*/

        $clientes = EdiClientes::where("nombre_fiscal", 'SOCIEDAD DE COMPRAS MODERNAS, S.A.')->get();

        foreach ($clientes as $cliente) {
            $address = $cliente->direccion;
            $address = urlencode($address);
            if($address) {
                $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&sensor=false";
                $json = json_decode(file_get_contents($url), true);
                if ($json["status"] == "OK") {
                    $result = $json['results'][0];
                    $direccion = "";
                    $numero = null;
                    $cp = "";
                    $poblacion = "";
                    $provincia = "";
                    foreach ($result['address_components'] as $address_component) {
                        if ($this->isAddressComponent($address_component['types'], "street_number")) {
                            $numero = $address_component['short_name'];
                        }

                        if ($this->isAddressComponent($address_component['types'], "route")) {
                            $direccion = $address_component['short_name'];
                        }

                        if ($this->isAddressComponent($address_component['types'], "locality")) {
                            $poblacion = $address_component['long_name'];
                        }

                        if ($this->isAddressComponent($address_component['types'], "administrative_area_level_2")) {
                            $provincia = $address_component['long_name'];
                        }

                        if ($this->isAddressComponent($address_component['types'], "postal_code")) {
                            $cp = $address_component['long_name'];
                        }
                    }


                    if ($numero) {
                        $direccion .= ", $numero";
                    }
                    $cliente->direccion = $direccion;
                    $cliente->cp = $cp;
                    $cliente->poblacion = $poblacion;
                    $cliente->provincia = $provincia;
                    $cliente->save();
                }
            }
        }
    }

    private function isAddressComponent($types_to_search, $needle) {
        foreach($types_to_search as $type) {
            if($type == $needle) {
                return true;
            }
        }
        return false;
    }


    public function getCheckPedidos() {
        $sql = "SELECT * FROM pedidos WHERE codcli = 176";
        $pedidos = Ctsql::ctsqlExport($sql);
        dd(json_decode($pedidos[0]));

    }

    public function getEroskiFromPedido() {
        return view("etiquetas.formEroski");
    }

    public function getEroskiLabelsFromPedido($codcli, $ejercicio, $pedido_base, $html = null) {

        $pedidoEdi = $this->getPedidoEdi($ejercicio, $codcli, $pedido_base);

        $locs = EdiLoclped::where("cabped_id", $pedidoEdi->id)->get();

        $lineas = EdiLinped::where("cabped_id", $pedidoEdi->id)->get();

        $artic = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codcli = $codcli");

        $artics = json_decode($artic[0]);

        $artics = $artics->data;

        $proveedor = EdiClientes::where("cod_interno", $codcli)
            ->where("cliente_logival", 1)
            ->first();

        $tiendasResult = EdiClientes::all();
        $tiendas = [];
        $labels = [];

        foreach($tiendasResult as $tienda) {
            $tiendas[$tienda->ean]['code'] = $tienda->cod_interno;
            $tiendas[$tienda->ean]['nombre'] = $tienda->nombre;
        }




        foreach($lineas as $linea) {
            foreach($locs as $loc) {
                if($linea->clave2 == $loc->clave2) {
                    $udsLinea = 1;
                    foreach($artics as $artic) {
                        if($artic->codart == $linea->refcli) {
                            $nameArtic = $artic->descri;
                            $udsBulto = substr($nameArtic, -3);
                            $udsLinea = explode("/", $udsBulto)[1];

                        }
                    }
                    $labels[$loc->lugar]['codTienda'] = $tiendas[$loc->lugar]['code'];
                    $labels[$loc->lugar]['tienda'] = $tiendas[$loc->lugar]['code']."<br>".
                        $tiendas[$loc->lugar]['nombre'];

                    if(!array_key_exists("bultos", $labels[$loc->lugar])) {

                        $labels[$loc->lugar]['bultos'] = 0;
                    }

                    $labels[$loc->lugar]['bultos'] += $loc->cantidad / $udsLinea;
                }

            }
        }



        foreach($labels as $index=>$label) {
            if($label['bultos'] > 10) {
                unset($labels[$index]);
            }
            else $tiendasNumber[$index] = $labels[$index]["codTienda"];
        }

        array_multisort($tiendasNumber, SORT_ASC, $labels);


        $data["proveedor"] = $proveedor->cod_eroski."<br>".$proveedor->nombre_fiscal;
        $data["pedido"] = $pedidoEdi->numped;
        $data["labels"] = $labels;

        $view = view("etiquetas.eroski", $data);

        if($html) {
            return $view;
        }
        else return \PDF::loadHTML($view)
            ->setPaper('a4')
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-top', 0)
            ->stream();

    }

    public function getEroskiLabels($numcli, $ejerci, $numalb, $html = null) {

        $locs = AlbaranEdiLocalizaciones::where("ejerci", $ejerci)
            ->where("codcli", $numcli)
            ->where("numalb", $numalb)
            ->orderBy('lugar', 'ASC')
            ->get();

        $lineas = AlbaranEdiLineas::where("codcli", $numcli)
            ->where("ejerci", $ejerci)
            ->where("numalb", $numalb)
            ->get();

        $albaran = AlbaranEdi::where("codcli", $numcli)
            ->where("ejerci", $ejerci)
            ->where("numalb", $numalb)
            ->first();

        $proveedor = EdiClientes::where("cod_interno", $numcli)
            ->where("cliente_logival", 1)
            ->first();

        $tiendasResult = EdiClientes::all();
        $tiendas = [];
        $labels = [];

        foreach($tiendasResult as $tienda) {
            $tiendas[$tienda->ean]['code'] = $tienda->cod_interno;
            $tiendas[$tienda->ean]['nombre'] = $tienda->nombre;
        }


        foreach($lineas as $linea) {
            foreach($locs as $loc) {
                if($loc->idpalet == $linea->idpalet && $loc->idcaja == $linea->idcaja) {


                    $udsLinea = $linea->uds_bulto;
                    $labels[$loc->lugar]['tienda'] = $tiendas[$loc->lugar]['code']."<br>".
                        $tiendas[$loc->lugar]['nombre'];

                    if(!array_key_exists("bultos", $labels[$loc->lugar])) {

                        $labels[$loc->lugar]['bultos'] = 0;
                    }

                    $labels[$loc->lugar]['bultos'] += $loc->cantidad / $udsLinea;

                }
            }
        }





        $data["proveedor"] = $proveedor->cod_eroski."<br>".$proveedor->nombre_fiscal;
        $data["pedido"] = $albaran->pedido_ref;
        $data["labels"] = $labels;

        $view = view("etiquetas.eroski", $data);

        if($html) {
            return $view;
        }
        else return \PDF::loadHTML($view)
            ->setPaper('a4')
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-top', 0)
            ->stream();
    }


    public function getEstructuraEtiquetadoEci($numcli, $ejerci, $numalb, $html = null) {
        $estructura = $this->estructuraEtiquetado($numcli, $ejerci, $numalb, "ECI");
        return $this->getEstructuraView($estructura, $html);

    }

    public function getEstructuraEtiquetadoEroski($numcli, $ejerci, $numalb, $html = null) {
        $estructura = $this->estructuraEtiquetado($numcli, $ejerci, $numalb, "EROSKI");
        return $this->getEstructuraView($estructura, $html);
    }

    private function getEstructuraView($estructura, $html) {
        $view = view("etiquetas.estructura", ["estructura"=>$estructura]);

        if($html) {
            return $view;
        }
        else return \PDF::loadHTML($view)
            ->setPaper('a4')
            ->stream();
    }

    private function estructuraEtiquetado($numcli, $ejerci, $numalb, $plataforma) {


        $cajas = AlbaranEdiCajas::where("codcli", $numcli)
            ->where("ejerci", $ejerci)
            ->where("numalb", $numalb)->get();

        $lineas = AlbaranEdiLineas::where("codcli", $numcli)
            ->where("ejerci", $ejerci)
            ->where("numalb", $numalb)->get();

        $equivalencias =  EquivalenciaCodigosPlataformas::where("codcli", $numcli)
            ->where("plataforma", $plataforma)->get();

        $estructura = [];


            foreach($cajas as $caja) {

                $sscc_no_digit = substr($caja->sscc, 0, -1);
                $sscc_no_digit = intval($sscc_no_digit);
                $codProveedor = 0;
                foreach ($lineas as $linea) {
                    if ($linea->idpalet == $caja->idpalet && $linea->idcaja == $caja->idcaja) {
                        $referenciaPlataforma = $linea->referencia;
                        foreach ($equivalencias as $equivalencia) {
                            if ($equivalencia->cod_plataforma == $referenciaPlataforma) {
                                $codProveedor = $equivalencia->cod_proveedor;
                            }
                        }

                    }
                }

                for ($i = 0; $i < $caja->cantidad; $i++) {
                    $sscc = $sscc_no_digit + $i;
                    $sscc = $sscc . $this->getSsccDigitControl($sscc);

                    $estructura[$caja->idpalet][$sscc] = $codProveedor;
                }
            }


        return $estructura;
    }

    public function getBuildEroskiLabels($codcli = null , $ejercicio = null, $pedido_base = null, $html = null) {

        if(!$codcli) {
            return view("etiquetas.buildEroski");
        }

        $pedidoEdi = $this->getPedidoEdi($ejercicio, $codcli, $pedido_base);

        $locs = EdiLoclped::where("cabped_id", $pedidoEdi->id)->get();

        $lineas = EdiLinped::where("cabped_id", $pedidoEdi->id)->get();

        $artic = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codcli = $codcli");

        $artics = json_decode($artic[0]);

        $artics = $artics->data;

        $tiendasResult = EdiClientes::all();
        $tiendas = [];
        $labels = [];

        foreach($tiendasResult as $tienda) {
            $tiendas[$tienda->ean]['code'] = $tienda->cod_interno;
            $tiendas[$tienda->ean]['nombre'] = $tienda->nombre;
        }




        foreach($lineas as $linea) {
            foreach($locs as $loc) {
                if($linea->clave2 == $loc->clave2) {
                    $udsLinea = 1;
                    foreach($artics as $artic) {
                        if($artic->codart == $linea->refcli) {
                            $nameArtic = $artic->descri;
                            $udsBulto = substr($nameArtic, -3);
                            $udsLinea = explode("/", $udsBulto)[1];

                        }
                    }
                    $labels[$loc->lugar]['codTienda'] = $tiendas[$loc->lugar]['code'];
                    $labels[$loc->lugar]['tienda'] = $tiendas[$loc->lugar]['code']."<br>".
                        $tiendas[$loc->lugar]['nombre'];

                    if(!array_key_exists("bultos", $labels[$loc->lugar])) {

                        $labels[$loc->lugar]['bultos'] = 0;
                    }

                    $labels[$loc->lugar]['bultos'] += $loc->cantidad / $udsLinea;
                }

            }
        }



        foreach($labels as $index=>$label) {
            $tiendasNumber[$index] = $labels[$index]["codTienda"];
        }

        array_multisort($tiendasNumber, SORT_ASC, $labels);

        $data["labels"] = $labels;

        return view("etiquetas.buildEroski", $data);
    }

    public function postBuildEroskiLabels($codcli = null , $ejercicio = null, $pedido_base = null) {
        $pedidoEdi = $this->getPedidoEdi($ejercicio, $codcli, $pedido_base);
        $tiendas = \Request::get("tiendas");
        $numBultos = \Request::get("numBultos");
        $nombresTiendas = \Request::get("nombresTiendas");
        $labels = [];

        $proveedor = EdiClientes::where("cod_interno", $codcli)
            ->where("cliente_logival", 1)
            ->first();

        foreach($tiendas as $index=>$codTienda) {
            $labels[$index]["tienda"] = $nombresTiendas[$index];
            $labels[$index]["bultos"] = $numBultos[$index];
        }

        $data["proveedor"] = $proveedor->cod_eroski."<br>".$proveedor->nombre_fiscal;
        $data["pedido"] = $pedidoEdi->numped;
        $data["labels"] = $labels;

        $view = view("etiquetas.eroski", $data);


        return \PDF::loadHTML($view)
            ->setPaper('a4')
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-top', 0)
            ->stream();
    }


    public function getBuildCamionesTiendas($codcli = null , $ejercicio = null, $pedido_base = null, $numCamiones = null)
    {
        if(!$numCamiones) {
            return view("albaran.camiones-tiendas");
        }
        else {
            $pedidoEdi = $this->getPedidoEdi($ejercicio, $codcli, $pedido_base);

            $locs = EdiLoclped::where("cabped_id", $pedidoEdi->id)->get();

            $lineas = EdiLinped::where("cabped_id", $pedidoEdi->id)->get();

            $tiendasResult = EdiClientes::all();
            $tiendas = [];
            $labels = [];

            foreach($tiendasResult as $tienda) {
                $tiendas[$tienda->ean]['code'] = $tienda->cod_interno;
                $tiendas[$tienda->ean]['nombre'] = $tienda->nombre;
            }




            foreach($lineas as $linea) {
                foreach($locs as $loc) {
                    if($linea->clave2 == $loc->clave2) {
                        $labels[$loc->lugar]['codTienda'] = $tiendas[$loc->lugar]['code'];
                        $labels[$loc->lugar]['tienda'] = $tiendas[$loc->lugar]['code']."<br>".
                            $tiendas[$loc->lugar]['nombre'];
                        $labels[$loc->lugar]['ean'] = $loc->lugar;
                    }
                }
            }




            foreach($labels as $index=>$label) {
                $tiendasNumber[$index] = $labels[$index]["codTienda"];
            }

            array_multisort($tiendasNumber, SORT_ASC, $labels);

            $data["tiendas"] = $labels;
            $data["numCamiones"] = $numCamiones;

            return view("albaran.camiones-tiendas", $data);
        }
    }

    public function postBuildCamionesTiendas($codcli = null , $ejercicio = null, $pedido_base = null, $numCamiones = null) {
        $tiendasReq = \Request::get("tiendas");

        $pedidoEdi = $this->getPedidoEdi($ejercicio, $codcli, $pedido_base);
        $locs = EdiLoclped::where("cabped_id", $pedidoEdi->id)->get();
        $lineas = EdiLinped::where("cabped_id", $pedidoEdi->id)->get();

        $artics = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codcli='$codcli'");
        $artics = json_decode($artics[0]);
        $data = [];

        foreach($tiendasReq as $iCamion => $tiendas) {

            foreach($tiendas as $tienda) {
                foreach($locs as $loc) {
                    if($loc->lugar == $tienda) {
                        $linPedido = $this->getLinPedidoFromLoc($pedidoEdi->id, $loc, $lineas);
                        $udsBulto = $this->getUdsProduct($linPedido->refean, $linPedido->refcli, $artics->data);


                        if(!array_key_exists($iCamion, $data) || !array_key_exists($linPedido->refcli, $data[$iCamion])) {
                            $data[$iCamion][$linPedido->refcli]["cantidad"] = 0;
                            $data[$iCamion][$linPedido->refcli]["bultos"] = 0;
                        }
                        $data[$iCamion][$linPedido->refcli]["cantidad"] += $loc->cantidad;
                        $data[$iCamion][$linPedido->refcli]["bultos"] += $loc->cantidad / $udsBulto;

                    }
                }
            }
        }

        $data["camiones"] = $data;

        return view("albaran.camiones-tiendas", $data);
    }

    private function getLinPedidoFromLoc($idPedido, $loc, $lineas) {
        foreach($lineas as $linea) {
            if($linea->clave2 == $loc->clave2) {
                return $linea;
            }
        }

        return null;
    }

    private function getUdsProduct($refEan, $codart, $artics) {

        foreach($artics as $artic) {
            if($artic->codbar == $refEan && $codart == $artic->codart) {
                $nameArtic = $artic->descri;
                $udsBulto = substr($nameArtic, -3);
                return explode("/", $udsBulto)[1];
            }
        }

        return null;


    }
}