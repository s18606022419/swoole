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

    private $connectList = [];

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
                'worker_num' => 1,
                'dispatch_mode' => 2,
                'open_length_check' => true,
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_length_type' => 'N',
                'package_max_length' => 2465792, //单位字节, TODO: 计算实际需求调整大小
            )
        );
        $this->server->on('Open', array($this, 'onOpen'));
        $this->server->on('Message', array($this, 'onMessage'));
        $this->server->on('Close', array($this, 'onClose'));
        echo 'start...................';
        $this->server->start();
    }


    /**
     * 客户端启动时回调.
     *
     * @param \swoole_websocket_server $server
     */
    public function onOpen($server, $request)
    {
        echo $request->fd . '连接了' . PHP_EOL;//打印到我们终端
        print_r('request:'.$request->get."\n");
        print_r('name:'.$request->get['name']."\n");
        $this->connectList[$request->fd] = $request->get['name'];//将请求对象上的fd，也就是客户端的唯一标识，可以把它理解为客户端id，存入集合中
    }

    /**
     * 接收到客户端消息时回调.
     *
     * @param \swoole_websocket_server $server
     */
    public function onMessage($server, $frame)
    {
        //print_r(date('Y-m-d H:i:s', time()).'--------Get Message from: '.$frame->fd."\n");
        echo $this->connectList[$frame->fd] . '来了，说：' . $frame->data . PHP_EOL;//打印到我们终端
        echo '在人数' . json_encode($this->connectList) . PHP_EOL;//打印到我们终端
        //将这个用户的信息存入集合
        foreach ($this->connectList as $fd => $name) {//遍历客户端的集合，拿到每个在线的客户端id
            //将客户端发来的消息，推送给所有用户，也可以叫广播给所有在线客户端
            $server->push($fd, json_encode(['name' => $this->connectList[$frame->fd], 'msg' => $frame->data]));
        }
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
        echo $fd . '走了' . PHP_EOL;//打印到我们终端
        unset($this->connectList[$fd]);//将断开了的客户端id，清除出集合
    }
}

// 启动webSocket服务
$webSocket = new WebSocket();
$webSocket->start();
