<?php

/**
 * WebSocket 服务端.
 *
 * @author    Albert <albert@fzhxkj.com>
 *
 * @since     2017/08/01
 *
 * @copyright Copyright (c) 2010-2017 HUAXIONG Inc. (http://www.fzhxkj.com)
 */
class Client
{
    private $client = null;

    private $connectList = [];

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_ASYNC);
        $this->client->connect('127.0.0.1', 9500);
        $data = [
            'to_id' => 12,
            'body' => '这是个测试',
        ];
        $this->client->send('这是个测试');
    }

}

// 启动webSocket服务
$webSocket = new Client();
