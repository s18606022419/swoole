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
class WebSocket
{
    private $server = null;

    public function __construct()
    {
        $this->server = new \swoole_websocket_server('0.0.0.0', 9500);
    }


    /**
     * 启动webSocket.
     */
    public function start()
    {
        $this->server->set(
            array(
                'daemonize' => 0,
                'max_request' => 5000,
                'max_conn' => 1024,
                'worker_num' => 32,
                'dispatch_mode' => 2,
                'open_length_check' => true,
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_length_type' => 'N',
                'package_max_length' => 2465792, //单位字节, TODO: 计算实际需求调整大小
            )
        );
        $this->server->on('Request', array($this, 'onRequest'));  
        $this->server->on('Open', array($this, 'onOpen'));
        $this->server->on('Message', array($this, 'onMessage'));
        $this->server->on('Close', array($this, 'onClose'));
        
        $this->server->start();
    }

    /*
    * 发送
    */
    public function onRequest($request, $response){
        if(!isset($request->get)){
            $response->end("params is null");
            return;
        }
        @$wsId=$request->get['wsId'];
        @$data=$request->get['data'];

        if(empty($wsId)||empty($data)){
            $response->end("params is null");
        }
        $this->server->push(intval($wsId),$data);
        $response->end("success");
        return;
    }

    /**
     * 客户端启动时回调.
     *
     * @param \swoole_websocket_server $server
     */
    public function onOpen($server, $request)
    {
        swoole_set_process_name(sprintf('websocket %s process', 'master'));

        $clientId = $request->fd;
        $server->push($clientId, json_encode(['wsId' => $clientId]));

        print_r(date('Y-m-d H:i:s', time()).'-------Connection Open:'.$clientId."\n");
    }

    /**
     * 接收到客户端消息时回调.
     *
     * @param \swoole_websocket_server $server
     */
    public function onMessage($server, $frame)
    {
        //print_r(date('Y-m-d H:i:s', time()).'--------Get Message from: '.$frame->fd."\n");
    }

    /**
     * 关闭与客户端连接时回调.
     *
     * @param \swoole_websocket_server $server
     * @param int                      $fd
     * @param int                      $fromId
     */
    public function onClose($server, $fd, $fromId)
    {
        print_r(date('Y-m-d H:i:s', time()).'--------Connection Close: '.$fd."\n");
    }
}

// 启动webSocket服务
$webSocket = new WebSocket();
$webSocket->start();
