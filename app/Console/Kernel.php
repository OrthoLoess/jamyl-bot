<?php namespace JamylBot\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'JamylBot\Console\Commands\Inspire',
        'JamylBot\Console\Commands\CheckApis',
        'JamylBot\Console\Commands\RegisterSlackUsers',
        'JamylBot\Console\Commands\RunKillbot',
        'JamylBot\Console\Commands\SetSlackInactives',
        'JamylBot\Console\Commands\RunAllChecks',
        'JamylBot\Console\Commands\ReadStandings',
        'JamylBot\Console\Commands\GetSlackChannels',
        'JamylBot\Console\Commands\ManageChannels',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		//$schedule->command('inspire')->hourly();
        $schedule->command('jamyl:allchecks')->everyFiveMinutes();
        $schedule->command('jamyl:firekillbot')->everyFiveMinutes();
	}

}
