<?php

require_once __DIR__ . '/../credentials.php';

if (!isset($_GET['hash']) || !is_string($_GET['hash'])) {
    return http_response_code(400);
}

if (!isset($_GET['id']) || !is_string($_GET['id']) || empty($_GET['id'])) {
    return http_response_code(400);
}

if ($_GET['hash'] !== hash_hmac('sha1', $_GET['id'], I_FRAME_HASH_SECRET)) {
    return http_response_code(401);
}

$data = json_decode(file_get_contents(__DIR__ . '/../people.json'), true);

$foundUser = null;

foreach ($data as $user) {
    if ($user['id'] == $_GET['id']) {
        $foundUser = $user;
        break;
    }
}

if (! isset($foundUser)) {
    return http_response_code(404);
}

$user = $foundUser;

?>
<html>
    <head>
        <title><?php echo htmlspecialchars($user['name']); ?></title>
    </head>
    <body>
        <h1><?php echo htmlspecialchars($user['name']); ?></h1>
        <h2>Id</h2>
        <p><?php echo htmlspecialchars($user['id']); ?></p>
        <h2>Address</h2>
        <p><?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
    </body>
</html>
