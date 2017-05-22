<?php
// define('__ROOT__', dirname(dirname(__FILE__))); 
// use Workerman_linux\Worker;
// use Workerman_linux\Lib\Timer;
// require_once __DIR__ .'/Workerman_linux/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

require_once __DIR__ .'/Workerman_win/Autoloader.php';
include_once 'Room.php';
$ws_worker = new Worker("websocket://0.0.0.0:2346");
$ws_worker->count = 1;
$ws_worker->onConnect = 'onConnect';
$ws_worker->onMessage = 'onMessage';
$ws_worker->onClose = 'onClose';

function sendToAll($connection, $data)
{
    foreach ($connection->worker->connections as $client) {
        $client->send($data);
    }
}
function getCount($connection)
{
    return count($connection->worker->connections);
}

function onConnect($connection)
{
    $_timeout = 10;
    $_count = getCount($connection);
    $_onlineTips = " 玩家 {$connection->id} 已上线! 当前玩家数量:".$_count."\n";
    sendToAll($connection, $_onlineTips);
    print_r($_onlineTips);
    $connection->auth_timer_id = Timer::add($_timeout, function () use ($connection) {
        $connection->send(" 超时未认证, 连接关闭 ");
        $connection->close();
    }, null, false);
};

function onMessage($connection, $data)
{
    $result = json_decode($data);
    var_dump($result);
    switch ($result->mCmd) {
        case 0:
            if ($result->token == 1) {
                Timer::del($connection->auth_timer_id);
                $connection->send("login success!");
                $connection->isLogin = true;
            }
            break;
        case 'message':
            sendToAll($connection, " 玩家 {$connection->id}：".$result->context);
            break;
        case 'createroom':
            $re = $connection->send(createRoom($connection));
            $connection->send(json_encode($re));
            break;
        case 'joinroom':
            $re = joinRoom($connection, $result->roomId);
            $connection->send(json_encode($re));
            break;
    }
}

function onClose($connection)
{
    sendToAll($connection, " 玩家 {$connection->id} 已下线! 当前玩家数量:".getCount($connection));
    print_r(" 玩家 {$connection->id} 已下线! 当前玩家数量:".getCount($connection)."\n");
}
// 运行 worker
Worker::runAll();
