<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 7/4/16
 * Time: 10:58
 */

namespace App\Http\Controllers;


use App\Ctsql;
use App\EcommerceOrders;
use App\WoocommerceApi;
use Carbon\Carbon;

class DasanciController extends Controller
{

    private $linesError = array();
    private $skipAlbaran = array();

    public function getSyncNewOrders() {
        $max_updated_at = EcommerceOrders::where("cliente_id", 158)->max("updated_at");
        $orders = WoocommerceApi::getOrders(["filter[updated_at_min]"=>"$max_updated_at"]);
        foreach($orders->orders as $order) {

            if(Carbon::parse($order->updated_at)->timestamp > Carbon::parse($max_updated_at)->timestamp) {
                $this->syncOrder($order);
            }
        }
        $this->syncAlbaranes();
        $this->syncAllStock();
    }

    public function getSyncAllOrders() {
        $orders = WoocommerceApi::getOrders();
        EcommerceOrders::where("cliente_id", 158)->delete();

        foreach($orders->orders as $order) {

            $orderDB = $this->getOrderDB($order);

            if($order->status == "processing") {
                $this->syncOrderMultibase($order);
                $orderDB->multibase_sync = 1;
            }
            $orderDB->save();
        }
    }


    private function syncAllStock() {

        $products = [];
        $productsAlmac = $this->getAlmacenProducts();
        $stockAlmac = $this->getStockAlmacen();
        $numWebProducts = WoocommerceApi::countProducts();
        $webStock = WoocommerceApi::getProducts(["filter[limit]"=>$numWebProducts]);
        $webStock = $webStock->products;

        $productsUpdated = array();

        foreach($productsAlmac as $product) {
            $stock_qty = $this->getStockProductAlmac($product->codart, $stockAlmac);
            $pendiente = $this->getStockPendienteServir($product->codart);
            $webStockQty = $this->getWebStockProduct($product->codart, $webStock);
            $stock_qty = $stock_qty-$pendiente;

            if($webStockQty != $stock_qty) {

                $products[] = [
                    "sku" => $product->codart,
                    "managing_stock" => true,
                    "stock_quantity" => $stock_qty
                ];
            }

            if(count($products) >= 10) {
                $productsUpdated[] = WoocommerceApi::updateProducts($products);
                $products = [];
            }
        }

        if(count($products) >= 0) {
            $productsUpdated[] = WoocommerceApi::updateProducts($products);
        }

        //dd($productsUpdated);
    }

    private function getWebStockProduct($codart, $webStock) {

        foreach($webStock as $product) {
            if($product->sku == $codart) {
                return $product->stock_quantity;
            }
        }
        return 0;
    }

    private function getStockPendienteServir($codart) {
        $orders = EcommerceOrders::where("cliente_id", 158)->where("status", "processing")->get();
        $cantidad = 0;
        foreach($orders as $order) {
            $orderNumber = $order->order_number;
            $ejercicio = Carbon::createFromFormat('Y-m-d H:i:s', $order->created_at)->year;
            $query = "SELECT * FROM linpedidos WHERE codemp=1 AND coddel=1 AND codcli=158 AND tipped='S'
                      AND serped = 'WB' AND ejeped = $ejercicio AND numped = $orderNumber AND codart = '$codart'";

            $linpedidos = Ctsql::ctsqlExport($query);
            $linpedidos = json_decode($linpedidos[0]);
            if(!$linpedidos->success) {
                dd($linpedidos->error->type.": ".$linpedidos->error->message);
            }
            foreach($linpedidos->data as $linea) {
                $cantidad += $linea->cantid;
            }
        }
        return $cantidad;
    }



    private function orderExists($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $numped = $order->order_number;

        $orderMulti = Ctsql::ctsqlExport("SELECT * FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = $ejercicio AND
          numped = $numped
        ");

        $orderMulti = json_decode($orderMulti[0]);

        return (count($orderMulti->data) > 0);

    }

    private function syncOrderMultibase($order) {



        if($this->orderExists($order)) {
            echo "Insertando en Multibase...<br>";
            $this->addOrderToMultibase($order);
            if(count($this->linesError) > 0) {
                echo "Error en líneas: ".var_dump($this->linesError);
            }
        }
        else {
            echo("HAY QUE ACTUALIZAR");
            //$this->updateOrderOnMultibase();
        }
    }

    private function getAlmacenProducts() {
        $productosAlmacen = Ctsql::ctsqlExport("SELECT * FROM artic where codemp=1 AND (codcli=158 or codcli=1580)");
        $productosAlmacen = json_decode($productosAlmacen[0]);
        return $productosAlmacen->data;
    }

    private function getStockAlmacen() {
        $stock = Ctsql::ctsqlExport("SELECT * FROM ocupalmac where codcli = 158 and codemp = 1 and coddel=1");
        return json_decode($stock[0])->data;
    }

    private function getStockProductAlmac($codart, $stockAlmac) {
        $uds = 0;
        foreach($stockAlmac as $product) {
            if($product->codart == $codart) {
                $uds += $product->udsart;
            }
        }
        return $uds;
    }

    /**
     * @param $order
     * @param $ejercicio
     * @param $numped
     */
    private function addOrderToMultibase($order)
    {
        echo "Insertando Orden...<br>";
        $this->insertOrder($order);

        echo "Insertando Líneas...<br>";
        $this->insertOrderLines($order);
    }

    private function insertOrderLines($order) {
        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $numped = $order->order_number;
        $lines = $order->line_items;
        $numlin = 1;
        foreach($lines as $line) {
            $sku = $line->sku;
            $articulo = Ctsql::ctsqlExport("SELECT * FROM artic WHERE codemp = 1 AND codcli = 158 AND codart='$sku'");
            $articulo = json_decode($articulo[0]);
            $articulo = $articulo->data;

            if(count($articulo) > 0) {

                $cantid = $line->quantity;
                $descri = $line->name;

                $output = Ctsql::ctsqlImport("INSERT INTO linpedidos ("
                    ."codemp, coddel, codcli, tipped, serped, ejeped, numped, numlin,"
                    ."codart, cantid, bultos, kilos, volume, precio, dtoli1,"
                    ."dtoli2, descri, estado, tipdoc, tipiva, edilin, asocia,"
                    ."nopick, lnpick, codkit)"
                    ."VALUES"
                    ."(1, 1, 158, 'S', 'WB', $ejercicio, $numped, $numlin,"
                    ."'$sku', $cantid, 0, 0, 0, 0, 0,"
                    ."0, '$descri', '', 'P', 0, 'S', 0,"
                    ."0, 0, '')");
                var_dump($output);
                $numlin++;
            }

            else {
                $this->linesError[] = $line;
            }
        }
    }

    private function insertOrder($order) {
        list($fecped, $nomtec, $dirtec, $pobtec, $cpotec, $pobdis, $codtec, $observ, $ejercicio, $numped) = $this->getVariablesForQuery($order);

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
            ."(1, 1, 158, 'S', 'WB', $ejercicio, $numped,"
            ."'$fecped', '', 0, 0, 0,"
            ."0, 0, '$nomtec', '$dirtec', '$pobtec', $cpotec,"
            ."'$codtec', '$observ', '', 'N', 'N', '$fecped',"
            ."'', '', '$pobdis', 0, '$nomtec', 'WEB-$numped',"
            ."'N', 'N', 0, 0, 0, '',"
            ."'', ''"
            .")";

        $output = Ctsql::ctsqlImport($query);

        $output = json_decode($output[0]);
        if(!$output->success) {

            dd($output->error->type.": ".$output->error->message);
        }
    }

    /**
     * @param $order
     * @return array
     */
    private function getVariablesForQuery($order)
    {
        $fecped = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->format("d/m/Y");
        $shipping_address = $order->shipping_address;
        $nomtec = $shipping_address->first_name . " " . $shipping_address->last_name;
        $dirtec = $shipping_address->address_1;
        $pobtec = $shipping_address->city;
        $cpotec = $shipping_address->postcode;
        $pobdis = $shipping_address->city;
        $codtec = $order->customer_id;
        $billing_address = $order->billing_address;
        $observ = "NIF: " . $billing_address->nif . " Telf: " . $billing_address->phone . " Email:" . $billing_address->email;
        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $numped = $order->order_number;
        return array($fecped, $nomtec, $dirtec, $pobtec, $cpotec, $pobdis, $codtec, $observ, $ejercicio, $numped);
    }

    public function getTest() {
        /*$pedido = Ctsql::ctsqlImport("DELETE FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = 2016 AND
          numped = 1535");*/



        $pedido = Ctsql::ctsqlExport("SELECT * FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = 2016 AND
          numped = 1535");

        var_dump($pedido);

        $pedido = Ctsql::ctsqlExport("SELECT * FROM linpedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = 2016 AND
          numped = 1535");

        var_dump($pedido);
    }





    private function syncAlbaranes() {
        $ordersDB = EcommerceOrders::where("cliente_id", 158)->where("status", "processing")->get();
        $ordersUpd = [];
        foreach($ordersDB as $orderDB) {
            $ejercicio = Carbon::createFromFormat('Y-m-d H:i:s', $orderDB->created_at)->year;
            $albaranes = Ctsql::ctsqlExport("SELECT * FROM albaran WHERE
              codemp=1 AND
              coddel=1 AND
              codcli=158 AND
              serped = 'WB' AND
              tipalb = 'S' AND
              ejeped = $ejercicio AND
              numped = ".$orderDB->order_number);
            $albaranes = json_decode($albaranes[0]);
            $albaranes = $albaranes->data;

            if(count($albaranes) > 0) {
                $ordersUpd[] = [
                    "id" => $orderDB->order_number,
                    "status" => "completed"
                ];
                $orderDB->status = "completed";
            }
            $orderDB->save();
        }
        if(count($ordersUpd) > 0) {
            WoocommerceApi::updateOrders($ordersUpd);
        }

    }

    private function syncOrder($order) {
        if($this->orderExists($order)) {
            $this->updateOrder($order);
        }
        else {
            $orderDB = $this->getOrderDB($order);
            if($order->status = "processing") {
                $this->addOrderToMultibase($order);
                $orderDB->multibase_sync = 1;
            }
            $orderDB->save();
            $this->sendNewOrderEmail($order);
        }
    }

    private function updateOrder($order) {
        $orderDB = $this->getOrderDB($order, false);

        if($orderDB->status == "processing" && $order->status == "cancelled") {
            $this->cancelOrder($order, $orderDB);
        }

        elseif(($orderDB->status == "processing" || $orderDB->status == "completed") && $order->status == "refunded") {
            $this->refundOrder($order, $orderDB);
        }

        elseif($order->status == "processing") {
            $this->updateOrderData($order);
            $this->updateOrderLines($order);
            $orderDB->status = $order->status;
            $orderDB->save();
            $this->sendUpdatedOrderEmail($order);
        }
    }

    private function updateOrderData($order) {

        list($fecped, $nomtec, $dirtec, $pobtec, $cpotec, $pobdis, $codtec, $observ, $ejercicio, $numped) = $this->getVariablesForQuery($order);

        $query = "UPDATE pedidos SET "
            ."fecped='$fecped', nomtec = '$nomtec',"
            ."dirtec = '$dirtec', pobtec = '$pobtec', cpotec = $cpotec,"
            ."codtec = '$codtec', observ = '$observ', fecent ='$fecped',"
            ."pobdis = '$pobdis', nomfis = '$nomtec', refped = 'WEB-$numped'"
            ." WHERE codemp=1 AND coddel=1 AND codcli=158 AND serped = 'WB' AND tipped = 'S' AND ejeped = $ejercicio AND numped = $numped";


        $output = Ctsql::ctsqlImport($query);

        $output = json_decode($output[0]);
        if(!$output->success) {

            dd($output->error->type.": ".$output->error->message);
        }
    }

    private function updateOrderLines($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;


        $query = "DELETE FROM linpedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = $ejercicio  AND
          numped = " . $order->order_number;

        $output = Ctsql::ctsqlImport($query);

        $output = json_decode($output[0]);
        if(!$output->success) {

            dd($output->error->type.": ".$output->error->message);
        }

        $this->insertOrderLines($order);
    }

    private function cancelOrder($order, $orderDB) {
        $this->sendCancelledOrderEmail($order);
        $orderDB->status = $order->status;
        $orderDB->save();
    }

    private function refundOrder($order, $orderDB) {
        echo "Pedido devuelto ";
        $this->sendRefundedOrderEmail($order);
        $orderDB->status = $order->status;
        $orderDB->save();
    }

    private function sendCancelledOrderEmail($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $pedido = Ctsql::ctsqlExport("SELECT * FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = $ejercicio AND
          numped = ".$order->order_number);

        $pedido = json_decode($pedido[0]);
        $pedido = $pedido->data[0];

        $data = [
            "num_pedido" => $pedido->tipped."/".$pedido->serped."/".$pedido->ejeped."/".$pedido->numped,
            "num_cliente" => $pedido->codcli
        ];

        \Mail::send("emails.orders.cancelled", $data, function ($message) use ($data)  {
            $message->from("noreply@logival.es", "Logival Avisos");
            $message->to("alvaro@logival.es", "Yolanda");
            $message->subject("Pedido ".$data["num_pedido"]." CANCELADO");
        });
    }

    private function sendRefundedOrderEmail($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $pedido = Ctsql::ctsqlExport("SELECT * FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = $ejercicio AND
          numped = ".$order->order_number);

        $pedido = json_decode($pedido[0]);
        $pedido = $pedido->data[0];

        $data = [
            "num_pedido" => $pedido->tipped."/".$pedido->serped."/".$pedido->ejeped."/".$pedido->numped,
            "num_cliente" => $pedido->codcli
        ];

        \Mail::send("emails.orders.refunded", $data, function ($message) use ($data)  {
            $message->from("noreply@logival.es", "Logival Avisos");
            $message->to("alvaro@logival.es", "Yolanda");
            $message->subject("Pedido ".$data["num_pedido"]." DEVUELTO");
        });
    }

    private function sendUpdatedOrderEmail($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;
        $pedido = Ctsql::ctsqlExport("SELECT * FROM pedidos WHERE
          codemp=1 AND
          coddel=1 AND
          codcli=158 AND
          serped = 'WB' AND
          tipped = 'S' AND
          ejeped = $ejercicio AND
          numped = ".$order->order_number);

        $pedido = json_decode($pedido[0]);
        $pedido = $pedido->data[0];

        $data = [
            "num_pedido" => $pedido->tipped."/".$pedido->serped."/".$pedido->ejeped."/".$pedido->numped,
            "num_cliente" => $pedido->codcli
        ];

        \Mail::send("emails.orders.updated", $data, function ($message) use ($data)  {
            $message->from("noreply@logival.es", "Logival Avisos");
            $message->to("alvaro@logival.es", "Yolanda");
            $message->subject("Pedido ".$data["num_pedido"]." MODIFICADO");
        });
    }

    private function sendNewOrderEmail($order) {

        $ejercicio = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->year;


        $data = [
            "num_pedido" => "S/WB/$ejercicio/".$order->order_number,
            "num_cliente" => 158
        ];

        \Mail::send("emails.orders.created", $data, function ($message) use ($data)  {
            $message->from("noreply@logival.es", "Logival Avisos");
            $message->to("alvaro@logival.es", "Yolanda");
            $message->subject("Pedido ".$data["num_pedido"]." CREADO");
        });
    }

    /**
     * @param $order
     * @return EcommerceOrders
     */
    private function getOrderDB($order, $newOrder = true)
    {
        if($newOrder)
            $orderDB = new EcommerceOrders();
        else {
            $orderDB = EcommerceOrders::where("cliente_id", 158)->where("order_number", $order->order_number)->first();
            return $orderDB;
        }
        $orderDB->cliente_id = 158;
        $orderDB->order_number = $order->order_number;
        $orderDB->status = $order->status;
        $createdAt = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->created_at)->toDateTimeString();
        $updatedAt = Carbon::createFromFormat('Y-m-d\TH:i:sP', $order->updated_at)->toDateTimeString();
        $orderDB->updated_at = $updatedAt;
        $orderDB->created_at = $createdAt;
        return $orderDB;
    }




}