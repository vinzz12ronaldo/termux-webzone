<?php

declare(strict_types=1);

namespace App\Commands\Create;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;

class Zend extends Command
{
    protected $dir;

    protected $path;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create:zend
							{name?}
							{--path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create Zend projects';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        $this->callSilently('settings:init');
        $this->dir = $this->getData()['project_dir'];
        $this->line(exec('clear'));
        $this->logo();
        $this->init();
    }

    public function getData()
    {
        $json_object = file_get_contents(config('settings.PATH') . '/settings.json');
        return json_decode($json_object, true);
    }

    public function logo(): void
    {
        $figlet = new Figlet();
        $this->comment($figlet->setFont(config('logo.font'))->render('Zend'));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function init(): void
    {
        //name of project
        if (! empty($this->argument('name'))) {
            $this->name = $this->argument('name');
        } else {
            //planing to generate random names from a new package.
            $this->name = 'Zend_project';
        }

        //set path
        if (! empty($this->option('path'))) {
            $this->path = $this->option('path');
        } elseif (! empty($this->dir) && is_dir($this->dir)) {
            $this->path = $this->dir;
        } else {
            $this->path = '/sdcard';
        }

        //check if directory exists
        if (! $this->checkDir()) {
            exit;
        }
        $this->line(exec('tput sgr0'));
        $this->info('Creating Zend app');
        $this->newline();
        $this->create();
        $this->newline();
        $this->comment("Zend App created successfully on {$this->path}/{$this->name}");

    
    }

    private function checkDir()
    {
        if (file_exists($this->path . '/' . $this->name)) {
            $this->error('A duplicate file/directory found in the path. Please choose a better name.');
            return false;
        }
        return true;

    
    }

    private function create(): void
    {
        $cmd = "cd {$this->path} && composer create-project zendframework/zendframework \"{$this->name}\"";
        $this->exec($cmd);
    }

    private function exec($command): void
    {
        $this->line(exec($command));
    }
}
