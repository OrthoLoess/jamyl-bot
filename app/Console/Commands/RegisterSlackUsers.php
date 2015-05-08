<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Userbot\Userbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RegisterSlackUsers extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'slack:register';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'For all users without a slack ID, will look for their email on the slack user list.';

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
		$userbot->linkSlackMembers();
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
