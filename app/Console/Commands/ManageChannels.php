<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Channel;
use JamylBot\Userbot\Userbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ManageChannels extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jamyl:manage';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sets channel memberships on slack to match groups';

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
	public function fire(Userbot $userbot)
	{
		foreach (config('slack.channels-to-manage') as $channelId) {
            $channel = Channel::findBySlackId($channelId);
            $userbot->manageChannel($channel);
        }
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
