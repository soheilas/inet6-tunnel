<?php
$host = '127.0.0.1';
$port = 8080;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, $host, $port);
socket_listen($socket);

while (true) {
    $client = socket_accept($socket);
    $input = socket_read($client, 1024);

    preg_match('/X-Real-IP: (.+)/', $input, $matches);
    $clientIP = isset($matches[1]) ? trim($matches[1]) : '127.0.0.1';

    $allowed = checkClientIP($clientIP);

    if ($allowed) {
        $response = "HTTP/1.1 200 OK\r\n\r\n";
    } else {
        $response = "HTTP/1.1 403 Forbidden\r\n\r\n";
    }

    socket_write($client, $response);
    socket_close($client);
}

function checkClientIP($clientIP) {
    $clientsFile = '/var/www/licensing/data/clients.txt';
    if (!file_exists($clientsFile)) return false;

    $lines = file($clientsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        $parts = explode(',', $line);
        if (count($parts) >= 3) {
            $ip = trim($parts[0]);
            $expiry = trim($parts[1]);
            $active = trim($parts[2]);

            if ($ip === $clientIP && $active === '1' && strtotime($expiry) > time()) {
                return true;
            }
        }
    }
    return false;
}
?>
