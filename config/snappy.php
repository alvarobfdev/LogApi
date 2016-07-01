<?php

return array(


    'pdf' => array(
        'enabled' => true,
        'binary' => '../vendor/bin/wkhtmltopdf',
        'timeout' => false,
        'options' => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary' => '/usr/local/bin/wkhtmltoimage',
        'timeout' => false,
        'options' => array(),
    ),


);
