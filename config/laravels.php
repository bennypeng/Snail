<?php
/**
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings-CN.md  Chinese
 * @see https://github.com/hhxsv5/laravel-s/blob/master/Settings.md  English
 */
return [
    'listen_ip'          => env('LARAVELS_LISTEN_IP', '0.0.0.0'),
    'listen_port'        => env('LARAVELS_LISTEN_PORT', 8081),
    'socket_type'        => env('LARAVELS_SOCKET_TYPE', defined('SWOOLE_SOCK_TCP') ? \SWOOLE_SOCK_TCP : 1),
    'enable_gzip'        => env('LARAVELS_ENABLE_GZIP', false),
    'server'             => env('LARAVELS_SERVER', 'LaravelS'),
    'handle_static'      => env('LARAVELS_HANDLE_STATIC', false),
    'laravel_base_path'  => env('LARAVEL_BASE_PATH', base_path()),
    'inotify_reload'     => [
        'enable'     => env('LARAVELS_INOTIFY_RELOAD', false),
        'file_types' => ['.php'],
        'log'        => false,
    ],
    'websocket'          => [
        'enable'  => false,
        'handler' => \App\Services\WebSocketService::class,
    ],
    'sockets'            => [
        //  TCP协议
        /*
        [
            'host'     => '0.0.0.0',
            'port'     => 8082,
            'type'     => SWOOLE_SOCK_TCP,
            'settings' => [
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestTcpSocket::class
        ],
        //  UDP协议
        [
            'host'     => '0.0.0.0',
            'port'     => 8086,
            'type'     => SWOOLE_SOCK_UDP,
            'settings' => [
                'open_eof_check' => true,
                'package_eof'    => "\r\n",
            ],
            'handler'  => \App\Sockets\TestUdpSocket::class,
        ],
        */
    ],
    'processes'          => [
        //  自定义进程，用于创建一些特殊的工作进程，比如监控、上报或者其他特殊的任务
        //\App\Processes\TestProcess::class
    ],
    'timer'              => [
        'enable' => false,
        'jobs'   => [
            //  毫秒级定时任务
            // 在Linux的Crontab中开启每分钟执行一次
            // * * * * * /usr/bin/php7.2 /home/vagrant/Code/Laravel5.5_Common/artisan schedule:run >> /dev/null 2>&1
            //\App\Jobs\TestCronJob::class
        ],
    ],
    'events'             => [
        //  绑定事件与监听器，一个事件可以有多个监听器，多个监听器按顺序执行
        //\App\Events\TestEvent::class => [
        //    \App\Listeners\TestListener::class
        //]
    ],
    'swoole_tables'      => [
        // 场景：WebSocket中UserId与FD绑定
        'ws' => [// Key为Table名称，使用时会自动添加Table后缀，避免重名。这里定义名为wsTable的Table
            'size'   => 102400,//Table的最大行数
            'column' => [// Table的列定义
                ['name' => 'value', 'type' => \swoole_table::TYPE_INT, 'size' => 8],
            ],
        ],
    ],
    'register_providers' => [
    ],
    'swoole'             => [
        'daemonize'          => env('LARAVELS_DAEMONIZE', true),
        'dispatch_mode'      => 2,
        'reactor_num'        => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 4,
        'worker_num'         => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 8,
        'task_worker_num'    => function_exists('\swoole_cpu_num') ? \swoole_cpu_num() * 2 : 8,
        //'task_ipc_mode'      => 3,
        'task_ipc_mode'      => 2,
        'task_max_request'   => 3000,
        'task_tmpdir'        => @is_writable('/dev/shm/') ? '/dev/shm' : '/tmp',
        'message_queue_key'  => ftok(base_path('public/index.php'), 1),
        'max_request'        => 3000,
        'open_tcp_nodelay'   => true,
        'pid_file'           => storage_path('laravels.pid'),
        'log_file'           => storage_path('logs/swoole.log'),
        //'log_file'           => storage_path(sprintf('logs/swoole-%s.log', date('Y-m-d'))),
        //'log_file'           => sprintf('/var/log/swoole_log/swoole-%s.log', date('Y-m-d')),
        'log_level'          => 4,
        'document_root'      => base_path('public'),
        'buffer_output_size' => 16 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'package_max_length' => 4 * 1024 * 1024,
        'reload_async'       => true,  // 异步重启开关，设置为true时，将启用异步安全重启特性，Worker进程会等待异步事件完成后再退出
        'max_wait_time'      => 60,    // 进程最大等待时间
        'enable_reuse_port'  => true,  // 端口重用开关，设置为true时，多个进程可以同时进行Accept操作。

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
];
