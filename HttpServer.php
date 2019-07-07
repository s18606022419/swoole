<?php

Class HttpServer{


    private $service = null;

    public function __construct()
    {
        $this->service = new swoole_http_server('127.0.0.1',9500);
    }

    public function start()
    {
        $this->service->on('request',  array($this,'onRequest'));
        $this->service->start();
    }

    public function onRequest($request, $response)
    {
        $response->end('response');
    }

    public function onClose($service, $fd)
    {
        echo 'Client:'.$fd.'->close';
    }

}
$service = new HttpServer();
$service->start();

