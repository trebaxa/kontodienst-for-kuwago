<?php

/**
 * path where to store account access data like username and pin.
 * It will be stored encrypted. Folder should one level lower than root of domain 
 */


# load needed classes
require ('lib/phpFinTS/vendor/autoload.php');
require ("classes/kd_master.class.php");
require ("classes/kd_rest.class.php");
require ("classes/kd_rest.api.class.php");

# execute API
$api = new kd_api;
$api->process_api();
