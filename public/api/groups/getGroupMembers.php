<?php

use Entity\Exception\EntityNotFoundException;
use Entity\GroupChat;
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

$groupId = isset($data->groupId) && is_numeric($data->groupId) ? (int) $data->groupId : null;

if ($groupId === null || $groupId === '' || !is_numeric($groupId)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le groupId est obligatoire et doit être un nombre valide"]);
    exit;
}

try {
    $group = GroupChat::getGroupById($groupId);
    $res = [];
    $users = $group->getUsers();
    foreach ($users as $user) {
        $res[] = [
            "userId" => $user->getId(),
            "username" => $user->getUsername(),
            "token" => "N'y crois même pas"
        ];
    }
    echo json_encode(["success" => $res]);
} catch (EntityNotFoundException $e) {
    http_response_code(404);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
}
