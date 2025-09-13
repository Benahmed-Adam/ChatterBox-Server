<?php

use Entity\Exception\EntityNotFoundException;
use Entity\Exception\UnauthorizedException;
use Entity\User;

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(["erreur" => "Méthode non autorisée"]);
    exit;
}

$rawData = file_get_contents('php://input');
$data = json_decode($rawData);

$userId = $data->userId ?? null;
$userId = is_string($userId) ? trim($userId) : $userId;
$token = $data->token ?? null;
$newPassword = $data->newPassword ?? null;

if ($userId === null || $userId === '' || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le userId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($token === null || $token === "") {
    http_response_code(400);
    echo json_encode(["erreur" => "Token manquant"]);
    exit;
}

if ($token === null || $token === "") {
    http_response_code(400);
    echo json_encode(["erreur" => "Token manquant"]);
    exit;
}

if ($newPassword === null || $newPassword === "") {
    http_response_code(400);
    echo json_encode(["erreur" => "Mot de passe manquant"]);
    exit;
}

if (mb_strlen($newPassword) < 3 || mb_strlen($newPassword) > 20) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 3 caractères et faire moins de 20 caractères"]);
    exit;
}

try {
    $user = User::getUserById((int)$userId);

    if ($user->getToken() !== $token) {
        throw new UnauthorizedException("Accès refusé");
    }

    $user->setPassword($newPassword)->save();

    http_response_code(200);
    echo json_encode([
        "success" => "Le mot de passe a bien été changé"
    ]);

} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
} catch (UnauthorizedException $e) {
    http_response_code(403);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
}
