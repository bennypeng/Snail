<?php

namespace App\Sockets;
use Hhxsv5\LaravelS\Swoole\Socket\UdpSocket;

class TestUdpSocket extends UdpSocket
{
    public function onPacket(\swoole_server $server, $data, array $clientInfo)
    {
        \Log::info('New UDP Package: ' . $data . PHP_EOL);
        $server->sendto($clientInfo['address'], $clientInfo['port'], "Server received data: " . $data . PHP_EOL);
    }

}