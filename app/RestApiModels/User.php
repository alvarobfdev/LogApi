<?php

namespace App\RestApiModels;
use Moloquent;

/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 11:44
 */
class User extends Moloquent
{
    public static $obligatory_post_fields = [
        "name", "surname"
    ];

    public static $optional_post_fields = [
        "company"
    ];
}