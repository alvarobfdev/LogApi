<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 17/3/16
 * Time: 9:34
 */

namespace App;


class CtsqlExport
{
    public static function ctsqlExport($query) {
        $args = "-q \"$query\"";
        exec("java -jar ".storage_path("app/bin")."/CtsqlExport.jar $args", $output);
        return $output;
    }
}