<?php

declare(strict_types=1);

namespace App\Commands\Create;

use App\Helpers\ComposerPackageInstaller;
use App\Helpers\Webzone;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Lumen extends Command
{
    protected $dir;

    protected $path;

    protected $signature = 'create:lumen
							{name=blog}
							{--path=}';

    protected $description = 'Create Lumen projects';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->callSilently('settings:init');

        $this->installer = new ComposerPackageInstaller('Lumen', 'lumen');
        $this->webzone = new Webzone();

        $this->webzone->clear();
        $this->webzone->logo('Lumen', 'comment');
        $this->init();
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
        $path = $this->option('path');

        $this->task('Setting up Installer', function () use ($path): void {
            $this->installer->setProperties($this->argument('name'), $path);
            $this->installer->setComposerPackage('laravel/lumen');
        });

        $this->task('Checking if project exists', function () {
            if ($this->installer->checkIfProjectExists()) {
                $this->status = false;
                $this->newline(2);
                $this->error('Project with same name already exists');
                $this->newline();
                return false;
            }
            $this->status = true;
            return true;
        });

        if ($this->status) {
            $this->install();
        }
    }

    private function install(): void
    {
        $this->newline();
        $this->info('Creating Lumen Application...');
        $this->newline();
        $this->installer->install();
        $this->newline();
        $this->info("  Lumen app created successfully at {$this->installer->mainPath}/{$this->installer->name}. ");
        $this->newline();
        $this->comment('    Create Something Awesome ');
    }
}