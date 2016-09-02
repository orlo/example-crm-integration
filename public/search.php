<?php

require_once __DIR__ . '/../credentials.php';

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];

if ($authHeader !== 'Basic ' . base64_encode(SEARCH_USERNAME . ':' . SEARCH_PASSWORD)) {
    return http_response_code(401);
}

if (!is_string($_GET['q']) || empty($_GET['q'])) {
    return http_response_code(400);
}

$data = json_decode(file_get_contents(__DIR__ . '/../people.json'), true);

$results = [];

$match = function ($query, $name) {
    $query = preg_quote($query, '!');
    if (preg_match('!' . $query . '!i', $name)) {
        return true;
    }
    return false;
};

foreach ($data as $user) {
    if ($match($_GET['q'], $user['name'])) {
        $results[] = $user;
    }
}

header('Content-Type: application/json');

echo json_encode(['results' => $results]);
