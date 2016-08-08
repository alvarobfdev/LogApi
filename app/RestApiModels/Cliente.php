<?php

namespace App\RestApiModels;
use Moloquent;

/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 11:44
 */
class Cliente extends Model
{
    public static $validationFilters = [
        'codcli' => 'integer',
    ];

    public static $adminValidationFilters = [
        'nomcli' => 'string',
        'nomacc' => 'string'
    ];

    public static $showable = [
        'tipped',
        'numped',
        'fecent',
        'nomter',
        'dirter',
        'pobter',
        'provter',
        'cpter',
        'tlfter',
        'portes',
        'serpar',
        'reserv'
    ];

}