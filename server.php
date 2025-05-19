<?php
// Требуется библиотека Ratchet для WebSocket (рекомендуется)

// Но для простоты — минимальный сервер на сокетах (без WebSocket протокола)

// Используй библиотеку Ratchet для реального WebSocket сервера!

set_time_limit(0);
ob_implicit_flush();

$host = '127.0.0.1';
$port = 8081;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($sock, $host, $port);
socket_listen($sock);

$clients = [];

echo "Server started at ws://$host:$port\n";

while (true) {
    $read = $clients;
    $read[] = $sock;

    $write = $except = null;
    if (socket_select($read, $write, $except, null) < 1) {
        continue;
    }

    if (in_array($sock, $read)) {
        $newsock = socket_accept($sock);
        $clients[] = $newsock;
        $key = array_search($sock, $read);
        unset($read[$key]);
        echo "New client connected\n";
    }

    foreach ($read as $client) {
        $data = socket_read($client, 2048, PHP_NORMAL_READ);
        if ($data === false) {
            $key = array_search($client, $clients);
            unset($clients[$key]);
            socket_close($client);
            echo "Client disconnected\n";
            continue;
        }
        $data = trim($data);
        if (!$data) {
            continue;
        }

        echo "Received: $data\n";

        // Отправляем сообщение всем клиентам
        foreach ($clients as $sendClient) {
            socket_write($sendClient, $data . "\n");
        }
    }
}

socket_close($sock);
