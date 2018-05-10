<?php
    @date_default_timezone_set('Asia/Shanghai');

    function encrypt($input,$key='tcPK4Yc#hq6y!gih') {
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $input, MCRYPT_MODE_ECB);
        return base64_encode($encrypted);
    }
    function decrypt($sStr,$key='tcPK4Yc#hq6y!gih') {
        $encryptedData = base64_decode($sStr);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_ECB);
    }

    $code2fd_table = new swoole_table(8192);
    $code2fd_table->column('fd', swoole_table::TYPE_INT, 4);//1,2,4,8
    $code2fd_table->create();

    $fd2code_table = new swoole_table(8192);
    $fd2code_table->column('code', swoole_table::TYPE_INT, 4);
    $fd2code_table->create();

    $serv = new swoole_websocket_server("0.0.0.0", 8801);
    $serv->set(array(
        'worker_num' => 8,
        'max_conn' =>1024,
        'daemonize'=>1,
        'log_file'=>'/root/data/fw_log.log'
    ));
    $serv->on('request', function ($request, $response) {
        if(!isset($request->get)){
            $response->end("params is null");
            return;
        }
        @$op=$request->get['op'];
        @$token=$request->get['token'];
        if($op=="get_clientid"){
            if(md5("op=".$op."&secrect=nnx1H73BQFGGf!MRj@WwfujlTq#Jj3Lu")!=$token){
                $response->end(json_encode(array("success"=>false,"data"=>"token error")));
                return;
            }
            $clientid=encrypt(time());
            $response->end(json_encode(array("success"=>true,"data"=>$clientid)));
            return;
        }elseif($op=="push_client"){
            global $serv,$code2fd_table;
            $code=intval($request->get['code']);
            $pid=$request->get['pid'];
            if(empty($code)){
                $response->end(json_encode(array("success"=>false,"data"=>"code is null")));
                return;
            }
            if(md5("code=".$code."&op=".$op."&pid=".$pid."&secrect=nnx1H73BQFGGf!MRj@WwfujlTq#Jj3Lu")!=$token){
                $response->end(json_encode(array("success"=>false,"data"=>"token error")));
                return;
            }
            $row=$code2fd_table->get($code);
            if(!$row){
                $response->end(json_encode(array("success"=>false,"data"=>"code not exist")));
                return;
            }
            $serv->push($row["fd"],$pid);
            $code2fd_table->del($code);
            $response->end(json_encode(array("success"=>true,"data"=>"")));
            return;
        }
        $response->end("method not exist");
    });
    $serv->on('Open', function($server, $req) {
        if(!isset($req->get)){
            $server->close($req->fd);
            return;
        }
        $clientid=$req->get['clientid'];
        if(empty($clientid)){
            $server->close($req->fd);
            return;
        }
        $client_time=decrypt($clientid);
        if(empty($client_time)||time()-intval($client_time)>10){
            $server->close($req->fd);
            return;
        }
        global $fd2code_table;
        print_r(date("Y-m-d H:i:s",time())."-------Connection Open:".$req->fd."----------Total Connection Num: ".count($fd2code_table)."\n");
    });
    $serv->on('Message', function($server, $frame) {
        $code=intval($frame->data);
        if(empty($code)){
            return;
        }
        global $code2fd_table,$fd2code_table;
        $code2fd_table->set($code,array("fd"=>$frame->fd));
        $fd2code_table->set($frame->fd,array("code"=>$code));
        print_r(date("Y-m-d H:i:s",time())."--------Ready Code: ".$code."\n");
    });
    $serv->on('Close', function($server, $fd) {
        global $code2fd_table,$fd2code_table;
        $code_array=$fd2code_table->get($fd);
        if($code_array===false) return;
        $code2fd_table->del($code_array["code"]);
        $fd2code_table->del($fd);
        print_r(date("Y-m-d H:i:s",time())."-------Connection Close: ".$fd."\n");
    });
    $serv->start();

