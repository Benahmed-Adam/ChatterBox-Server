<?php

require __DIR__ . '/../../../bootstrap.php';
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

$username = isset($data->username) ? trim($data->username) : null;
$password = isset($data->password) ? $data->password : null;

if (!$data || empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Nom d'utilisateur ou mot de passe manquant ou vide"]);
    exit;
}

try {
    $user = User::getUserByUsername($username);
    if (!password_verify($password, $user->getPassword())) {
        throw new UnauthorizedException("Accès refusé");
    }
    http_response_code(200);
    echo json_encode([
        "token" => $user->getToken(),
        "id" => $user->getId()
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
