<?php

namespace mirzaev\notchat;

use OpenSwoole\Server,
	OpenSwoole\WebSocket\Server as websocket,
	OpenSwoole\Http\Request,
	OpenSwoole\WebSocket\Frame,
	OpenSwoole\Constant;

$server = new websocket("0.0.0.0", 2024, Server::POOL_MODE, Constant::SOCK_TCP | Constant::SSL);

$server->set([
	'ssl_cert_file' => '/etc/letsencrypt/live/mirzaev.sexy/fullchain.pem',
	'ssl_key_file' => '/etc/letsencrypt/live/mirzaev.sexy/privkey.pem'
]);

$server->on("Start", function (Server $server) {
	echo "OpenSwoole WebSocket Server is started at http://127.0.0.1:2024\n";
});

$server->on('Open', function (Server $server, Request $request) {
	echo "connection open: {$request->fd}\n";

	$server->tick(1000, function () use ($server, $request) {
		$server->push($request->fd, json_encode(["hello", time()]));
	});
});

$server->on('Message', function (Server $server, Frame $frame) {
	echo "received message: {$frame->data}\n";
	$server->push($frame->fd, json_encode(["hello", time()]));
});

$server->on('Close', function (Server $server, int $fd) {
	echo "connection close: {$fd}\n";
});

$server->on('Disconnect', function (Server $server, int $fd) {
	echo "connection disconnect: {$fd}\n";
});

$server->start();
