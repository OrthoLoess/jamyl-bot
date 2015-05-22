<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Userbot\Userbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ReadStandings extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jamyl:standings';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Read changes to standings into app and update.';

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
     * @param Userbot $userbot
     *
     * @return mixed
     */
	public function fire(Userbot $userbot)
	{
		$userbot->readNewStandings();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
//			['example', InputArgument::REQUIRED, 'An example argument.'],
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
//			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
