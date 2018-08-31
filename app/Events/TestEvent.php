<?php

namespace App\Events;

use Hhxsv5\LaravelS\Swoole\Task\Event;

class TestEvent extends Event
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }
}
