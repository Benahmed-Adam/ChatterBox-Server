<?php

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

http_response_code(200);
echo json_encode([
    "success" => true,
]);