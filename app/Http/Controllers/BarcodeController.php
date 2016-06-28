<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/3/16
 * Time: 9:42
 */

namespace App\Http\Controllers;




use App\Barcode;
use Milon\Barcode\DNS1D;

class BarcodeController extends Controller
{
    public function getEan() {
        return view("barcode.barcode-form", ["title" => "Obtener EAN-13", "label"=>"Código EAN-13"]);
    }

    public function postEan() {


        $success = true;
        $msgError = "";
        if(!\Request::has("code_number")) {
            $success = false;
            $msgError = "No se ha recibido ningún código";
        }

        $codeNumber = \Request::get("code_number");

        if(!is_numeric($codeNumber) || strlen($codeNumber) < 12 || strlen($codeNumber) > 13) {
            $success = false;
            $msgError = "El codigo no tiene un formato correcto";
        }

        $data = [
            "success" => $success,
            "msgError" => $msgError,
        ];

        $data["codeNumber"] = $codeNumber;
        $data["type"] = "ean13";

        $data['img'] =  '<img src="data:image/png;base64,' . \DNS1D::getBarcodePNG($codeNumber, "EAN13", 2.7, 90) . '" alt="barcode"   />';




        return view("barcode.barcode-form", ["title" => "Obtener EAN-13", "label"=>"Código EAN-13", "data"=>$data]);

    }

    function getBarcode() {
        $im     = imagecreatetruecolor(300, 300);
        $black  = ImageColorAllocate($im,0x00,0x00,0x00);
        $white  = ImageColorAllocate($im,0xff,0xff,0xff);
        imagefilledrectangle($im, 0, 0, 300, 300, $white);
        $type = \Request::get("type");
        $codeNumber = \Request::get("code");
        $data = Barcode::gd($im, $black, 150, 150, 0, $type, $codeNumber, 2, 50);
        $response = \Response::make(imagejpeg($im), 200);
        $response->header('Content-Type', 'image/jpeg');
        imagedestroy($im);
        return $response;
    }

    function ean13_get_digit($digits){
//first change digits to a string so that we can access individual numbers
        $digits =(string)$digits;
// 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits{1} + $digits{3} + $digits{5} + $digits{7} + $digits{9} + $digits{11};
// 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
// 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits{0} + $digits{2} + $digits{4} + $digits{6} + $digits{8} + $digits{10};
// 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
// 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
        return $digits . $check_digit;
    }
}