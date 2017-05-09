<?php
// define('__ROOT__', dirname(dirname(__FILE__))); 

use Workerman\Worker;
use Workerman\Lib\Timer;

require_once __DIR__ .'./Workerman/Autoloader.php';


$ws_worker = new Worker("websocket://0.0.0.0:191");

$ws_worker->count = 1;
$ws_worker->onConnect = 'onConnect';
$ws_worker->onMessage = 'onMessage';
$ws_worker->onClose = 'onClose';

$RoomList = array();
$RoomLimit = 100;
$RoomCount = count($RoomList);
$RoomMaxPeople = 2;

function sendToAll($connection,$data){
    foreach($connection->worker->connections as $client){
        $client->send($data);
    }
}

function getCount($connection){
    return count($connection->worker->connections);
}
function createRoom($connection){
    if (count($RoomList) > $RoomLimit) {
        $result->result = -1;
        $result->roomNumber = -1;
    } else {
        $result->result = 0;
        $result->roomNumber = count($RoomList)+1;
        $result->creator = $connection->id;
        $result->count = 1;
        $RoomList[$result->roomNumber] = $result;
    }
    return $result;
}
function joinRoom($connection,$roomId){
    if ($RoomList[$roomId] == null) {
        $result->result = -1;
        $result->message = "没有这个房间";
    } elseif ($RoomList[$roomId].count > $RoomMaxPeople) {
        $result->result = -2;
        $result->message = "房间人满";
    } elseif ($connection->isInRoom) {
        $result->result = -3;
        $result->message = "已经在房间中";
    } else {
        $result->result = 0;
        $result->message = "加入成功";
        // $RoomList[$roomId]::addInRoom($connection);
        $RoomList[$roomId]->count++;;
        $result->room = $RoomList[$roomId];
        $connection->isInRoom = true;
    }
    return $result;
}


function onConnect($connection) {
    $_timeout = 10;
    $_count = getCount($connection);
    $_onlineTips = "玩家{$connection->id}已上线!当前玩家数量:".$_count."\n";
    sendToAll($connection,$_onlineTips);
    print_r($_onlineTips);
    $connection->auth_timer_id = Timer::add($_timeout, function()use($connection){
        $connection->send("超时未认证,连接关闭");
        $connection->close();
    }, null, false);
};


function onMessage($connection, $data)
{
    $result = json_decode($data);
    switch($result->msg) {
        case 'login':
            if ($result->token == 1) {
                Timer::del($connection->auth_timer_id);
                $connection->send("登录成功!");
                $connection->isLogin = true;
            }
        break;
        case 'message':
            sendToAll($connection,"玩家{$connection->id}：".$result->context);
        break;
        case 'createroom':
            $re = $connection->send(createRoom($connection));
            $connection->send(json_encode($re));
        break;
        case 'joinroom':
            $re = joinRoom($connection,$result->roomId);
            $connection->send(json_encode($re));
        break;
    }
}

function onClose($connection){
    sendToAll($connection,"玩家{$connection->id}已下线!当前玩家数量:".getCount($connection));
    print_r("玩家{$connection->id}已下线!当前玩家数量:".getCount($connection)."\n");
}
// 运行worker
Worker::runAll();