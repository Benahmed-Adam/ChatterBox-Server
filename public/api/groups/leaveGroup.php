<?php

require __DIR__ . '/../../../bootstrap.php';
use Entity\Exception\EntityNotFoundException;
use Entity\Exception\UnauthorizedException;
use Entity\User;
use Entity\GroupChat;

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

$groupId = $data->groupId ?? null;
$groupId = is_string($groupId) ? trim($groupId) : $groupId;

$token = $data->token ?? null;

if ($userId === null || $userId === '' || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le userId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($groupId === null || $groupId === '' || !is_numeric($groupId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le groupId est obligatoire et doit être un nombre valide"]);
    exit;
}

if ($token === null || $token === '') {
    http_response_code(400);
    echo json_encode(["erreur" => "Token manquant"]);
    exit;
}

try {
    $user = User::getUserById($userId);

    if ($user->getToken() !== $token) {
        throw new UnauthorizedException("Accès refusé");
    }

    $group = GroupChat::getGroupById($groupId);

    $user->leaveGroup($group->getId());
    http_response_code(200);
    echo json_encode([
        "succès" => "Le groupe a bien été quitté",
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

GroupChat::purgeEmptyGroups();
