<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 27/6/16
 * Time: 12:56
 */



namespace App\Http\Controllers;


use App\Ctsql;





class BarcodeReaderController extends Controller {

    private $address = "localhost";
    private $service_port = "1234";
    private $socket = null;
    private static $instance;

    /**
     * BarcodeReaderController constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance() {
        return self::$instance;
    }

    public function getSendBarcode() {
        return view('barcode.sender');
    }


    public function postSendBarcode() {

        $socket = $this->getSocket();

        $in = \Request::get("code")."\r\n";
        $out = '';

        //echo "Enviando codigo barras ...";
        socket_write($socket, $in, strlen($in));

        //echo "Cerrando socket...";
        socket_close($socket);
        //echo "OK.\n\n";
        return view('barcode.sender');

    }

    function getSocket() {
        /* Crear un socket TCP/IP. */
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            die("socket_create() falló: razón: " . socket_strerror(socket_last_error()) . "\n");
        }

        //echo "Intentando conectar a '$address' en el puerto '$service_port'...";
        $result = socket_connect($this->socket, $this->address, $this->service_port);
        if ($result === false) {
            die("socket_connect() falló.\nRazón: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n");
        } else {
            return $this->socket;
        }
    }

    function close() {
        if($this->socket != null) {
            socket_close($this->socket);
        }
    }

    function listenBarcode() {

        $socket = $this->getSocket();
        $time_ini = time();
        do {
            if (false === ($buf = socket_read($socket, 2048, PHP_NORMAL_READ))) {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);

                die("Could not receive data: [$errorcode] $errormsg \n");
            }

            if(strlen($buf) > 1) {
                break;
            }

            if((time() - $time_ini) > 30) {
                break;
            }
        } while(true);

        if(strlen($buf) < 1) {
            $buf = null;
        }
        $this->close();
        $buf = preg_replace( "/\r|\n/", "", $buf );
        $buf = ltrim($buf, '0');

        return $buf;
    }

    public function getListen() {
        //$barcode = $this->listenBarcode();
        //dd($barcode);
        return view("barcode.listen");
    }

    public function getListenProductBarcode() {

        $result['success'] = true;

        try {
            $barcode = $this->listenBarcode();
            if ($barcode != null) {
                $data = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codart = '$barcode'");
                $data = json_decode($data[0]);
                $data = $data->data;
                foreach($data as &$product) {
                    $dataOcup = Ctsql::ctsqlExport("SELECT * FROM ocupalmac WHERE codart = '{$product->codart}' AND codcli = {$product->codcli}");
                    $dataOcup = json_decode($dataOcup[0]);
                    $dataOcup = $dataOcup->data;
                    $product->ubics = $dataOcup;
                }
                $result['data'] = $data;
                $result['barcode'] = $barcode;
            } else {
                $result['success'] = false;
                $result['timeout'] = true;
            }
            return json_encode($result);
        }
        catch(\Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
            return $result;
        }
    }
}
