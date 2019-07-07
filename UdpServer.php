<?php

Class UdpServer{


    private $service = null;

    public function __construct()
    {
        $this->service = new swoole_server('127.0.0.1',9500, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
    }

    public function start()
    {
        $this->service->on('Packet', array($this,'onPacket'));
        $this->service->on('close',  array($this,'onClose'));
        $this->service->start();
    }

    public function onPacket($service, $data, $clientInfo)
    {
        var_dump($clientInfo);
        $service->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    }

    public function onClose($service, $fd)
    {
        echo 'Client:'.$fd.'->close';
    }

}
$service = new UdpServer();
$service->start();
#netcat -u 127.0.0.1 9502
