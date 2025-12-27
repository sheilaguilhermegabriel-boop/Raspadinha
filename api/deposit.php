<?php
require '../config.php';

$amount = $_POST['amount'];

// aqui você chama o endpoint REAL do seu gateway
// exemplo genérico:
$response = file_get_contents(GATEWAY_URL.'/deposit');

// salva no banco como PENDING
