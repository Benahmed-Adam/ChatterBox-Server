<?php

use Entity\Collection\UserCollection;

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

$username = isset($_GET['username']) ? trim($_GET['username']) : null;

if ($username === null || $username === '') {
    http_response_code(400);
    echo json_encode(["erreur" => "Champ username manquant ou vide"]);
    exit;
}

try {
    $res = [];
    $users = UserCollection::getUsersByStartingName($username);
    foreach ($users as $user) {
        $res[] = [
            "userId" => $user->getId(),
            "username" => $user->getUsername(),
            "token" => "Nope"
        ];
    }
    http_response_code(200);
    echo json_encode(["success" => $res]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
}
