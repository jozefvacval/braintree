<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include('vendor/autoload.php');


$btSettings = [
    "test_mode"   => "on",
    "merchant_id" => "XXXXXXXXXXXXXXXXXXXXX",
    "merchant_account_id" => "XXXXXXXXXXXXXXXXXXXXX",
    "public_key"  => "XXXXXXXXXXXXXXXXXXXXX",
    "private_key" => "XXXXXXXXXXXXXXXXXXXXX",
];



if ($btSettings['test_mode'] == "on")
{
    Braintree_Configuration::environment('sandbox');
}
else
{
    Braintree_Configuration::environment('production');
}

Braintree_Configuration::merchantId($btSettings["merchant_id"]);
Braintree_Configuration::publicKey($btSettings["public_key"]);
Braintree_Configuration::privateKey($btSettings["private_key"]);

