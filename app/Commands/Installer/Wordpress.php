<?php

declare(strict_types=1);

namespace App\Commands\Installer;

use Illuminate\Console\Scheduling\Schedule;
use Laminas\Text\Figlet\Figlet;
use LaravelZero\Framework\Commands\Command;
use ZipArchive;

class Wordpress extends Command
{
    protected $wordpress;

    protected $port;

    protected $dir;

    protected $zip;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'installer:wordpress
							{--f|--force}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install wordpress Locally';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->callSilently('settings:init');
        $this->wordpress = config('wordpress.PATH');
        $this->zip = config('wordpress.WORDPRESS');
        $this->dir = config('wordpress.DIR');
        if ($this->option('force')) {
            $this->removeDir();
        }
        $this->install();
    }

    public function install()
    {
        if ($this->checkInstallation()) {
            $this->error('Wordpress is already installed. Use "server:wordpress" to start wordpress server.');
            return false;
        }

        $this->info(exec('clear'));
        $this->logo();
        $this->comment("\nInstalling Wordpress...\n");
        $link = config('wordpress.DOWNLOAD_LINK');
        $lines = shell_exec("curl -w '\n%{http_code}\n' {$link} -o {$this->zip}");
        $lines = explode("\n", trim($lines));
        $status = $lines[count($lines) - 1];
        $this->checkDownloadStatus($status);
    }

    public function checkInstallation()
    {
        if (is_dir($this->wordpress) && file_exists($this->wordpress . '/readme.html')) {
            return true;
        }
        return false;
    }

    public function logo(): void
    {
        $figlet = new Figlet();
        $this->comment($figlet->setFont(config('logo.font'))->render('WordPress'));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function removeDir(): void
    {
        $this->task("\nRemoving Old Files", function () {
            if (is_dir($this->wordpress)) {
                $cmd = shell_exec("rm -rf {$this->wordpress}");
                if (is_null($cmd)) {
                    return true;
                }
                return false;
            }
            if (file_exists($this->zip)) {
                $cmd = shell_exec("rm {$this->zip}");
                if (is_null($cmd)) {
                    return true;
                }
                return false;
            }
            return true;
        });
    }

    private function checkDownloadStatus($status): void
    {
        switch ($status) {
            case 000:
                $this->error('Cannot connect to Server');
                break;
            case 200:
                $this->comment("\nDownloaded Successfully...!!!\n");
                $this->runTasks();
                break;
            case 404:
                $this->error('File not found on server..');
                break;
            default:
                $this->error('An Unknown Error occurred...');
        }
    }

    private function runTasks(): void
    {
        $a = $this->task('Verifying download ', function () {
            if (file_exists($this->zip)) {
                return true;
            }
            return false;
        });

        $b = $this->task('Extracting WordPress ', function () {
            if ($this->unzip()) {
                return true;
            }
            return false;
        });

        if ($a && $b) {
            $this->successMessage();
        } else {
            $this->errorMessage();
        }
    }

    private function unzip()
    {
        $zip = new ZipArchive();
        $file = $this->zip;

        // open archive
        if ($zip->open($file) !== true) {
            return false;
        }
        // extract contents to destination directory
        $zip->extractTo($this->wordpress);
        // close archive
        $zip->close();
        return true;
    }

    private function successMessage(): void
    {
        $this->info("\n Successfully installed wordpress. use \"webzone server:wordpress\" command to start the server");
    }

    private function errorMessage(): void
    {
        $this->error("\n Faced an error while installing WordPress. Use \"-f or --force\" option for a forcefull installation.");
    }
}
