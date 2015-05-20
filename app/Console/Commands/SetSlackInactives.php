<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Userbot\Userbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SetSlackInactives extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'slack:inactives';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Checks all users, if they are not listed as neutral, blue or light blue, sets their slack account inactive.';

    /**
     * Create a new command instance.
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
		$userbot->setSlackInactives();
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
