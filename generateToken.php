<?php
include('config.php');

$token = Braintree_ClientToken::generate();
$output = ['client_token' => $token];

header('Content-Type: application/json');

echo json_encode($output);
