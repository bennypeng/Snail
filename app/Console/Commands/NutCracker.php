<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NutCracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nutcracker {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'twemproxy console tool: start|stop';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $action     = $this->argument('action');
        $confPath   = config_path('nutcracker') . '/nutcracker.yml';
        $logPath    = storage_path('logs') . '/nutcracker.log';
        $pidPath    = storage_path() . '/nutcracker.pid';
        $nutcracker = exec('which nutcracker');

        if (!file_exists($confPath))
        {
            $this->error(' Not found config file ');
            exit();
        }

        if (!$nutcracker)
        {
            $this->error(' Not found nutcracker ');
            exit();
        }

        switch ($action)
        {
            case 'start' :
                if (!is_file($pidPath))
                {
                    $ret = exec("{$nutcracker} -d -c {$confPath} -p {$pidPath} -o {$logPath}");

                    if (!$ret)
                    {
                        $this->info(" Start nutcracker success! ");
                        $this->info(" Config file: {$confPath} ");
                        $this->info(" Pid file   : {$pidPath} ");
                        $this->info(" Log file   : {$logPath} ");
                    } else {
                        $this->error(" Check log file : {$logPath} ");
                    }

                } else {
                    $pid = exec("cat {$pidPath}");
                    $this->error(" Nutcracker running##{$pid} ");
                }
                break;
            case 'stop' :
                if (is_file($pidPath))
                {
                    $pid = exec("cat {$pidPath}");
                    $ret = exec("kill -9 {$pid} && rm -rf {$pidPath}");

                    if ($ret)
                    {
                        $this->error(" {$ret} ");
                        exit();
                    }

                    $this->info(" Stop nutcracker#pid:{$pid} success! ");
                } else {
                    $this->error(' Not found pid file! ');
                }
                break;
            default:
                $this->error(' Usage: php artisan nutcracker {start|stop|restart} ');

        }
    }
}
