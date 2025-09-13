<?php

require __DIR__ . '/../../../bootstrap.php';
use Entity\Exception\UserAlreadyExists;
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

if (mb_strlen($username) < 3 || mb_strlen($username) > 20) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le nom d'utilisateur doit contenir au moins 3 caractères et faire moins de 20 caractères"]);
    exit;
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores"]);
    exit;
}

if (mb_strlen($password) < 3 || mb_strlen($password) > 20) {
    http_response_code(400);
    echo json_encode(["erreur" => "Le mot de passe doit contenir au moins 3 caractères et faire moins de 20 caractères"]);
    exit;
}

try {
    $user = User::create($username, $password);
    http_response_code(201);
    echo json_encode([
        "succès" => "Utilisateur créé avec succès"
    ]);
} catch (UserAlreadyExists $e) {
    http_response_code(409);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erreur" => $e->getMessage()]);
    exit;
}
