<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Killbot\Killbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RunKillbot extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'killbot:fire';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Tells the killbot to check zKill for new kills.';

	/**
	 * Create a new command instance.
	 *
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
	public function fire(Killbot $killbot)
	{
		$killbot->cycleCorps();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
