<?php

use Entity\User;
use Entity\GroupChat;
use Entity\Exception\EntityNotFoundException;
use Entity\Exception\UnauthorizedException;

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

$addUserId = $data->addUserId ?? null;
$addUserId = is_string($addUserId) ? trim($addUserId) : $addUserId;

$groupId = isset($data->groupId) && is_numeric($data->groupId) ? (int) $data->groupId : null;
$token = $data->token ?? null;

if ($userId === null || $userId === '' || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le userId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($addUserId === null || $addUserId === '' || !is_numeric($addUserId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le userId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($groupId === null || $groupId === '' || !is_numeric($groupId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le groupId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($token === null || $token === "") {
    http_response_code(400);
    echo json_encode(["erreur" => "Token manquant"]);
    exit;
}

try {
    $userFrom = User::getUserById($userId);

    if ($userFrom->getToken() !== $token) {
        throw new UnauthorizedException("Accès refusé");
    }

    $group = GroupChat::getGroupById($groupId);

    $user = User::getUserById($addUserId);
    $user->joinGroup($groupId);

    http_response_code(200);
    echo json_encode(["success" => "L'utilisateur a rejoint le groupe avec succès"]);

} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(["erreur" => $e->getMessage()]);
} catch (UnauthorizedException $e) {
    http_response_code(403);
    echo json_encode(["erreur" => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
}
