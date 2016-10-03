<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 17/3/16
 * Time: 9:34
 */

namespace App;


class Ctsql
{
    public static function ctsqlExport($query) {
        $args = "-q \"$query\"";
        exec("java -jar ".storage_path("app/bin")."/CtsqlExport.jar $args", $output);
        return $output;
    }

    public static function ctsqlImport($query) {
        $args = "-q \"$query\" -u";
        exec("java -jar ".storage_path("app/bin")."/CtsqlExport.jar $args", $output);
        return $output;
    }

    public static function ctsqlExportData($query) {
        $result = self::ctsqlExport($query);
        $result = json_decode($result[0]);
        if($result->success)
            return $result->data;
        else return $result;
    }
}