<?php

Class TcpServer{


    private $service = null;

    public function __construct()
    {
        $this->service = new swoole_server('127.0.0.1',9500);
    }

    public function start()
    {
        $this->service->on('connect', array($this,'onConnect'));
        $this->service->on('receive',  array($this,'onReceive'));
        $this->service->on('close',  array($this,'onClose'));
        $this->service->start();
    }

    public function onConnect($service, $fd)
    {
        echo 'Client:'.$fd.'链接了';
    }

    public function onReceive($service, $fd, $fromid, $data)
    {
        echo 'Client:'.$fd.'->说了：'.$data;
    }

    public function onClose($service, $fd)
    {
        echo 'Client:'.$fd.'->close';
    }
}
$service = new TcpServer();
$service->start();
#telnet 127.0.0.1 9500 测试
