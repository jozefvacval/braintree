<?php
include('config.php');

$token = Braintree_ClientToken::generate([
    'merchantAccountId' => $btSettings["merchant_account_id"], // from config file
]);
$output = ['client_token' => $token];

header('Content-Type: application/json');

echo json_encode($output);
