<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\Userbot\SlackMonkey;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TrollPunkslap extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jamyl:punk';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Happy Birthday message to p-drama';

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
	public function fire(SlackMonkey $slackMonkey)
	{
        $payload = [
            'channel' => '#p-drama',
            'username' => config('pingbot.ping-bot-name'),
            'icon_emoji' => config('pingbot.ping-bot-emoji'),
            'text' => 'HAPPY BIRTHDAY @punkslap',
            'link_names' => 1,
        ];
        $slackMonkey->sendMessageToServer($payload);
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
