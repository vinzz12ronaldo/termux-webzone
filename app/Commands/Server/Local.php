<?php

declare(strict_types=1);

namespace App\Commands\Server;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Local extends Command
{
    protected $path;

    protected $port;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'server:dev
							{--n}
							{--port=}
							{--path=}
							';
    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start localhost for development';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        $this->callSilently('settings:init');
        $this->setPort();
        $this->setDir();
        $this->checkInstallation();
    }

    public function getData()
    {
        $json_object = file_get_contents(config('settings.PATH') . '/settings.json');
        return json_decode($json_object, true);
    }

    public function checkInstallation(): void
    {
        if (! is_dir($this->path)) {
            mkdir($this->path);
            $this->comment('Directory created..');
            $this->createIndex();
        }
        $this->start();
    }

    public function logo(): void
    {
        $figlet = new Figlet();
        echo $figlet->setFont(config('logo.font'))->render(config('logo.name'));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function setPort(): void
    {
        if (! empty($this->option('port'))) {
            $this->port = $this->option('port');
        } elseif (! empty($this->getData()['php_port'])) {
            $this->port = $this->getData()['php_port'];
        } else {
            $this->port = config('pma.LOCALHOST_PORT');
        }
    }

    private function setDir(): void
    {
        if (! empty($this->option('path'))) {
            $this->path = $this->option('path');
        } elseif (! empty($this->getData()['localhost_path'])) {
            $this->path = $this->getData()['localhost_path'];
        } else {
            $this->path = config('pma.PROJECT_BASE_PATH');
        }
    }

    private function createIndex(): void
    {
        $cmd = exec("touch {$this->path}/index.php && echo -e \"<center><h1>Created Successfully</h1><p>Everything just went fine.....</p><\center>\" >> {$this->path}/index.php");
    }

    private function start(): void
    {
        echo exec('clear');
        $this->logo();
        $this->comment('Starting Localhost Server....');
        $this->line("\n");
        $this->launch();
        $cmd = exec("php -S 127.0.0.1:{$this->port} -t {$this->path}");
    }

    private function launch()
    {
        if (! $this->option('n')) {
            return shell_exec("xdg-open http://127.0.0.1:{$this->port}");
        }
    }
}
