<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/1/16
 * Time: 12:03
 */

namespace App\Http\Controllers;



use App\Cliente;
use App\FacturaWeb;
use App\User;
use Carbon\Carbon;

class ApiController extends Controller
{

    private $webInvoicesToAdd = array();
    private $webInvoicesToModify = array();
    private $webInvoicesToDelete = array();

    private $invoicesToWebRefresh = array();
    private $tmpDir = null;


    public function postPdfInvoice()
    {
        $data = \Request::get("jsonData");
        dd($data);
        $data = utf8_encode($data);
        $facturas = json_decode($data, true);

        //$view = $this->viewInvoice($facturas[0]);
        //return $view;

        /*Generamos ZIP*/
        $uid = uniqid();
        $zip = new \Chumper\Zipper\Zipper;
        $zip->make(storage_path() . "/app/tmp/$uid/facturas.zip");

        $tmpDir = "/tmp/$uid";
        $this->tmpDir = storage_path('app').$tmpDir;

        /*Insertamos cada factura en el zip*/
        foreach ($facturas as $factura) {
            $view = $this->viewInvoice($factura);
            return $view;
            $view = str_replace("localhost:8080", "localhost", $view);
            $pdfContents = \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)->output();
            $nombreFact = "factura-{$factura['id']['serfac']}-{$factura['id']['ejefac']}-{$factura['id']['numfac']}.pdf";
            $zip->addString($nombreFact, $pdfContents);
            $uidPdf = uniqid();

            \File::put($this->tmpDir."/$uidPdf.pdf", $pdfContents);
            $this->invoicesToWebRefresh[$uidPdf] = $factura;

        }

        /*$return = $this->refreshWeb();

        if($return != "ok") {
            die($return);
        }*/

        /*Cerramos ZIP, eliminamos temportal y  descargamos*/
        $zip->close();
        $response = \Response::make(file_get_contents(storage_path() . "/app/tmp/$uid/facturas.zip"));
        \Storage::drive("local")->deleteDirectory("tmp/$uid");
        $response->header('Content-Disposition', 'attachment; filename="facturas.zip"');
        $response->header('Content-Length', strlen($response->getOriginalContent()));
        \File::deleteDirectory(storage_path("app") . "/tmp/$uid");

        return $response;
    }

    public function refreshWeb() {
        $return = "ok";
        foreach($this->invoicesToWebRefresh as $facturaName => $factura) {

            if ($factura["web"]) {

                if ($this->webInvoiceExists($factura)) {
                    $return = $this->addWebInvoiceToModify($factura, null, $this->tmpDir."/$facturaName.pdf", $facturaName);
                } else {
                    $return = $this->addWebInvoiceToAdd($factura, null, $this->tmpDir."/$facturaName.pdf", $facturaName);
                }
            } else {
                if ($this->webInvoiceExists($factura)) {
                    $return = $this->addWebInvoiceToDelete($factura);
                }
            }
        }
        if($return != "ok") {
            return $return;
        }

        $return = $this->refreshWebDB();

        return $return;
    }


    public function postPdfInvoicesSummary()
    {
        $data = \Request::get("jsonData");
        $facturas = json_decode($data, true);
        $data['facturas'] = $facturas;
        $view = view('factura.invoicesSummary', $data)->render();
        \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)->download();


    }

    public function postSendInvoiceToClient()
    {
        $data = \Request::get("jsonData");
        $facturas = json_decode($data, true);
        $tmpUid = uniqid();
        $facturasInfo = array();
        foreach($facturas as $factura) {
            $this->prepareInvoiceForSending($factura, $tmpUid, $facturasInfo);
        }
        $this->sendInvoicesToClient($tmpUid, $facturasInfo);
        \File::deleteDirectory(storage_path("app")."/tmp/".$tmpUid);
        return "ok";
    }

    private function prepareInvoiceForSending($factura, $tmpUid, &$facturasInfo) {
        $codCli = $factura["codcli"];
        $cliente = Cliente::where("codcli", $codCli)->firstOrFail();
        if($cliente->email) {
            $tmpDir = "/tmp/" . $tmpUid . "/emails/" . $cliente->email;
            $this->savePdf($factura, $tmpDir);
            if(!array_key_exists($cliente->email, $facturasInfo)) {
                $facturasInfo[$cliente->email] = array();
            }
            $facturasInfo[$cliente->email][] = $factura;
        }
    }

    private function sendInvoicesToClient($tmpUid, $facturasInfo = array())
    {

        $tmpDir = "/tmp/" . $tmpUid . "/emails";
        $list = \File::directories(storage_path("app") . $tmpDir);
        foreach ($list as $directory) {
            $invoicesDir = $directory;
            $email = basename($directory);
            $uid = uniqid();
            $zip = new \Chumper\Zipper\Zipper;
            $zipPath = storage_path() . "/app/tmp/$uid/facturas.zip";
            $zip->make($zipPath);
            $files = glob($invoicesDir);
            $zip->add($files);
            $zip->close();
            $this->sendInvoicesEmail($zipPath, $email, $facturasInfo);
        }
    }

    private function sendInvoicesEmail($zipPath, $email, $facturasInfo) {

        $data = [
            "pathToImage" => public_path()."/logival/img/logo-transparente.png",
            "facturas" => $facturasInfo[$email]
        ];

        $codCli = $facturasInfo[$email][0]["codcli"];
        $cliente = Cliente::where("codcli", $codCli)->firstOrFail();


        try {
            \Mail::send("emails.invoice", $data, function ($message) use ($zipPath, $cliente, $email, $data) {
                $message->from("admon@logival.es", "Aviso de factura");
                $message->to($email, $cliente->nomcli);
                $message->subject("Emisión de facturas");
                $message->attach($zipPath);
            });
        }
        catch (\Exception $e)
        {
            dd($e->getMessage());
        }

        foreach($data["facturas"] as $factura) {

            $facturaWeb = FacturaWeb::where("ejercicio", $factura["id"]["ejefac"])->where("num_factura", $factura["id"]["numfac"])->first();

            if ($facturaWeb) {
                $facturaWeb->email_sent = 1;
                $facturaWeb->save();
            }

        }
        \File::deleteDirectory(dirname($zipPath));
        return "ok";

    }

        /*
        $codCli = $factura["codcli"];
        $cliente = Cliente::where("codcli", $codCli)->firstOrFail();
        if($cliente && $cliente->email) {
            $tmpUid = uniqid();
            $tmpDir = "/tmp/".$tmpUid;
            list($uidPdf, $pdfPath) = $this->savePdf($factura, $tmpDir);

            $data = [
                "pathToImage" => public_path()."/logival/img/logo-transparente.png",
                "numFactura" => $factura["id"]["serfac"] . "/" . $factura["id"]["ejefac"] . "/" . $factura["id"]["numfac"],
                "fechaFactura" => Carbon::parse($factura['fecfac'])->format("d/m/Y")
            ];

            try {
                \Mail::send("emails.invoice", $data, function ($message) use ($pdfPath, $cliente, $factura, $data) {
                    $message->from("logival@logival.es", "Aviso de factura");
                    $message->to($cliente->email, $cliente->nomacc);
                    $message->subject("Emisión de factura " . $data["numFactura"]);
                    $message->attach($pdfPath);
                });
            }
            catch (\Exception $e)
            {
                dd($e->getMessage());
            }

            $facturaWeb = FacturaWeb::where("ejercicio", $factura["id"]["ejefac"])->where("num_factura", $factura["id"]["numfac"])->first();

            if($facturaWeb) {
                $facturaWeb->email_sent = 1;
                $facturaWeb->save();
            }
            \File::deleteDirectory(storage_path("app").$tmpDir);

            return "ok";

        }*/


    private function groupsInvoice($factura)
    {
        $groups = array();

        foreach ($factura['lines'] as $index => $line) {
            if (array_key_exists($line['aplica'], FacturaController::$GROUPS_DESCRIPTIONS)) {
                if (!isset($groups[$line['aplica']]["importe"]))
                    $groups[$line['aplica']]["importe"] = 0;

                $groups[$line['aplica']]["descripcion"] = FacturaController::$GROUPS_DESCRIPTIONS[$line['aplica']];
                $groups[$line['aplica']]["importe"] += $line['import_'];
            }
        }

        return $groups;
    }

    private function viewInvoice($factura)
    {

        $data['factura'] = $factura;
        $data['groups'] = $this->groupsInvoice($factura);
        $view = view('factura.pdfInvoice', $data)->render();
        return $view;
    }

    public function postRefreshPdfWeb()
    {

        try {
            $data = \Request::get("jsonData");
            $data = utf8_encode($data);
            $facturas = json_decode($data, true);
            $uidFolder = uniqid();
            $tmpDir = "/tmp/$uidFolder";
            \File::makeDirectory(storage_path("app") . $tmpDir);

            $ftpConnection = \FTP::connection();
            $facturasPost = array();

            $return = "ok";

            foreach ($facturas as $factura) {


                if ($factura["web"]) {

                    if ($this->webInvoiceExists($factura)) {
                        $return = $this->addWebInvoiceToModify($factura, $tmpDir);
                    } else {
                        $return = $this->addWebInvoiceToAdd($factura, $tmpDir);
                    }
                } else {
                    if ($this->webInvoiceExists($factura)) {
                        $return = $this->addWebInvoiceToDelete($factura);
                    }
                }

                if ($return != "ok") {
                    return "error: " . $return;
                }
            }

            \File::deleteDirectory(storage_path("app") . $tmpDir);


            $return = $this->refreshWebDB();


            if ($return != "ok") {
                return "error: " . $return;
            }

            return "ok";


        } catch (\Exception $e) {
            return "error: " .$e->getMessage()." ". $e->getTraceAsString();
        }
    }

    private function sendPostRequest($url, $data)
    {
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    private function webInvoiceExists($factura) {

        return (FacturasWeb::where("ejercicio", $factura["id"]["ejefac"])->where("num_factura", $factura["id"]["numfac"])->count() > 0);
    }

    private function addWebInvoiceToAdd($factura, $tmpDir, $pdfPath = null, $uidPdf = null)
    {

        $ftpConnection = \FTP::connection();

        if($pdfPath == null || $uidPdf == null)
            list($uidPdf, $pdfPath) = $this->savePdf($factura, $tmpDir);

        if (!$ftpConnection->uploadFile($pdfPath, "clientes.logival.es/facturas/$uidPdf.pdf")) {
            return "error: Fallo al cargar factura $uidPdf.pdf a la web";
        }

        //Guardamos en bbdd intermedia
        $facturaModel = new FacturasWeb();
        $facturaModel->fecha = Carbon::parse($factura["fecfac"])->format("Y-m-d");
        $facturaModel->ejercicio = $factura["id"]["ejefac"];
        $facturaModel->num_factura = $factura["id"]["numfac"];
        $facturaModel->cliente_id = $factura["codcli"];
        $facturaModel->total_factura = $factura["totfac"];
        $facturaModel->file_pdf = "$uidPdf.pdf";
        $facturaModel->reducida = $factura["reducida"];

        //Revert on fail
        if(!$facturaModel->save()) {
            $ftpConnection->delete("clientes.logival.es/facturas/$uidPdf.pdf");
            return "error: Fallo al actualizar base de datos interna";
        }


        $this->webInvoicesToAdd[] = [
            "fecha" => $facturaModel->fecha,
            "ejercicio" => $facturaModel->ejercicio,
            "num_factura" => $facturaModel->num_factura,
            "cliente_id" => $facturaModel->cliente_id,
            "total_factura" => $facturaModel->total_factura,
            "file_pdf" => $facturaModel->file_pdf

        ];

        \FTP::disconnect();
        return "ok";
    }

    private function addWebInvoiceToModify($factura, $tmpDir, $pdfPath = null, $uidPdf = null) {
        $facturaModel = FacturasWeb::where("ejercicio", $factura["id"]["ejefac"])->where("num_factura", $factura["id"]["numfac"])->firstOrFail();
        $pdfName = $facturaModel->file_pdf;

        $ftpConnection = \FTP::connection();
        $ftpConnection->delete("clientes.logival.es/facturas/$pdfName");

        if($pdfPath == null || $uidPdf == null)
            list($uidPdf, $pdfPath) = $this->savePdf($factura, $tmpDir);

        if (!$ftpConnection->uploadFile($pdfPath, "clientes.logival.es/facturas/$uidPdf.pdf")) {
            return "error: Fallo al cargar factura $uidPdf.pdf a la web";
        }

        $facturaModel->fecha = Carbon::parse($factura["fecfac"])->format("Y-m-d");
        $facturaModel->cliente_id = $factura["codcli"];
        $facturaModel->total_factura = $factura["totfac"];
        $facturaModel->file_pdf = "$uidPdf.pdf";
        $facturaModel->reducida = $factura["reducida"];

        //Revert on fail
        if(!$facturaModel->save()) {
            $ftpConnection->delete("clientes.logival.es/facturas/$pdfName");
            return "error: Fallo al actualizar base de datos interna";
        }

        $this->webInvoicesToModify[] = [
            "fecha" => $facturaModel->fecha,
            "ejercicio" => $facturaModel->ejercicio,
            "num_factura" => $facturaModel->num_factura,
            "cliente_id" => $facturaModel->cliente_id,
            "total_factura" => $facturaModel->total_factura,
            "file_pdf" => $facturaModel->file_pdf
        ];

        \FTP::disconnect();

        return "ok";
    }

    private function addWebInvoiceToDelete($factura) {
        $facturaModel = FacturasWeb::where("ejercicio", $factura["id"]["ejefac"])->where("num_factura", $factura["id"]["numfac"])->firstOrFail();
        $pdfName = $facturaModel->file_pdf;
        $ftpConnection = \FTP::connection();


        $this->webInvoicesToDelete[] = [
            "fecha" => $facturaModel->fecha,
            "ejercicio" => $facturaModel->ejercicio,
            "num_factura" => $facturaModel->num_factura,
            "cliente_id" => $facturaModel->cliente_id,
            "total_factura" => $facturaModel->total_factura,
            "file_pdf" => $facturaModel->file_pdf
        ];

        if(!$facturaModel->delete()) {
            return "error: Fallo al actualizar base de datos interna";
        }

        $ftpConnection->delete("clientes.logival.es/facturas/$pdfName");
        \FTP::disconnect();

        return "ok";
    }

    /**
     * @param $factura
     * @param $tmpDir
     * @return array
     */
    private function savePdf($factura, $tmpDir)
    {
        $view = $this->viewInvoice($factura);
        $view = str_replace("localhost:8080", "localhost", $view);

        $uidPdf = uniqid();
        $pdfPath = storage_path("app") . "$tmpDir/$uidPdf.pdf";

        \PDF::loadHTML($view)->setPaper('a4')->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)->setOption('margin-left', 0)->setOption('margin-top', 0)
            ->save($pdfPath);
        return array($uidPdf, $pdfPath);
    }

    /**
     * @return string
     */
    private function refreshWebDB()
    {
        $return = "ok";

        if (count($this->webInvoicesToAdd) > 0) {
            $postData = ["jsonData" => json_encode($this->webInvoicesToAdd)];
            $return = $this->sendPostRequest("https://clientes.logival.es/api.php?user=api&pass=logivalapp&function=addInvoice", $postData);

            if($return != "ok") {
                $this->revert($this->webInvoicesToAdd);
            }
        }

        if (count($this->webInvoicesToModify) > 0) {
            $postData = ["jsonData" => json_encode($this->webInvoicesToModify)];
            $return = $this->sendPostRequest("https://clientes.logival.es/api.php?user=api&pass=logivalapp&function=modifyInvoice", $postData);
            if($return != "ok") {
                $this->revert($this->webInvoicesToModify);
            }
        }

        if (count($this->webInvoicesToDelete) > 0) {
            $postData = ["jsonData" => json_encode($this->webInvoicesToDelete)];
            $return = $this->sendPostRequest("https://clientes.logival.es/api.php?user=api&pass=logivalapp&function=deleteInvoice", $postData);
        }

        return $return;
    }

    private function revert($facturas) {
        $ftpConnection = \FTP::connection();

        foreach($facturas as $factura) {
            $numFac = $factura["num_factura"];
            $ejercicio = $factura["ejercicio"];
            $pdfName = $factura["file_pdf"];
            FacturasWeb::where("num_factura", $numFac)->where("ejercicio", $ejercicio)->delete();
            $ftpConnection->delete("clientes.logival.es/facturas/$pdfName");
        }
        \FTP::disconnect();
    }


}