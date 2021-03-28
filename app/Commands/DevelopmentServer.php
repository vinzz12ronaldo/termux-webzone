<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class DevelopmentServer extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'server:dev
							{--port=8088}
							{--n}';
    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start localhost for development';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->checkInstallation();
    }
    
    public function checkInstallation()
    {
    	$path = "/sdcard/www";
    
    	if(!is_dir($path)){
	    	mkdir($path);
			$this->comment('Directory created..');
			$this->createIndex($path);
	    }
	 $this->start();
    }
    
    private function start()
    {
    	$path = "/sdcard/www";
	    echo exec('clear');
    	$this->logo();
	    $this->comment("Localhost Services Started....");
	    $this->line("\n");
		$this->launch();
    	$cmd = exec("php -S 127.0.0.1:{$this->option('port')} -t {$path}");
    }
    
    private function launch()
    {
    	if(!$this->option('n'))
	    {
    	return shell_exec("xdg-open http://127.0.0.1:{$this->option('port')}");
	    }
    }
    
    private function createIndex($path)
    {
    	$cmd = exec("touch {$path}/index.php && echo -e \"<center><h1>Created Successfully</h1><p>Everything just went fine.....</p><\center>\" >> {$path}/index.php");
    }
    
    public function logo()
	{
		 $figlet = new \Laminas\Text\Figlet\Figlet();
		echo $figlet->setFont(config('logo.font'))->render(config('logo.name'));
	}
    
    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
