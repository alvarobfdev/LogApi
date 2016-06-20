<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 13/6/16
 * Time: 12:25
 */

namespace App\RestApiModels;


class Articulo extends Model
{
    public static $validation = [
        'codart' => 'required|unique_codart',
        'descri' => 'required',
        'kgsuni' => 'required|numeric',
        'codean' => 'integer|unique:articulos,codean',
        'udsbul' => 'integer'
    ];

    public static $adminValidation = [
        'codcli' => 'required|integer',
        'basalm' => 'required|in:PAL,UBI,BUL',
        'baralm' => 'required|in:Z,Y',
        'caduci' => 'required|in:N,L,A,C',
        'ctlser' => 'required|in:N,S',
        'basman' => 'required|in:UDS,PAL,KGS,BUL',
        'basmen' => 'required|in:UDS,PAL,KGS,BUL',
        'basmsa' => 'required|in:UDS,PAL,KGS,BUL',
    ];

    public static $validationFilters = [
        'codart' => 'string',
        'descri' => 'string',
        'kgsuni' => 'numeric',
        'codean' => 'integer',
        'limit' => 'integer',
        'page' => 'integer',

    ];

    public static $validationUpdate = [
        'codart' => 'string|unique_codart',
        'descri' => 'string',
        'kgsuni' => 'numeric',
        'codean' => 'integer|unique_codean'
    ];

    public static $validationAdminUpdate = [
        'codcli' => 'integer|exists:users,codcli',
        'basalm' => 'in:PAL,UBI,BUL',
        'baralm' => 'in:Z,Y',
        'caduci' => 'in:N,L,A,C',
        'ctlser' => 'in:N,S',
        'basman' => 'in:UDS,PAL,KGS,BUL',
        'basmen' => 'in:UDS,PAL,KGS,BUL',
        'basmsa' => 'in:UDS,PAL,KGS,BUL',
    ];


    public static $showable = [
        'codart',
        'descri',
        'kgsuni',
        'codean'
    ];
}