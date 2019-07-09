<?php
use Swoole\Redis\Server;
use Swoole\Coroutine\Redis;

$serv = new Server('0.0.0.0', 10086, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
$serv->setHandler('set', function ($fd, $data) use ($serv) {
$cli = new Redis;
$cli->connect('0.0.0.0', 6379);
$cli->set($data[0], $data[1]);
$serv->send($fd, Server::format(Server::INT, 1));
});

$serv->start();