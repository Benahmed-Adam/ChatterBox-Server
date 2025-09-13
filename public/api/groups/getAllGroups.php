<?php

require __DIR__ . '/../../../bootstrap.php';
use Entity\Collection\GroupCollection;

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

try {
    $res = [];
    $groups = GroupCollection::getAllGroups();
    foreach ($groups as $group) {
        $res[] = [
            "id" => $group->getId(),
            "name" => $group->getName(),
        ];
    }
    http_response_code(200);
    echo json_encode(["succès" => $res]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
}
