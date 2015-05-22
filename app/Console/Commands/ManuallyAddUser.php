<?php namespace JamylBot\Console\Commands;

use Illuminate\Console\Command;
use JamylBot\User;
use JamylBot\Userbot\ApiMonkey;
use JamylBot\Userbot\Userbot;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ManuallyAddUser extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jamyl:adduser';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Bypass login to manually add a user by id and email';

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
	public function fire(Userbot $userbot, ApiMonkey $api)
	{
		$user = User::create([
            'char_id'   => $this->argument('char_id'),
            'char_name' => $api->getCharName($this->argument('char_id')),
            'email'     => $this->argument('email'),
            'password'  => Userbot::generatePassword(16),
            'next_check'=> \Carbon\Carbon::now('UTC'),
        ]);
        $userbot->updateSingle($user->char_id);
        $userbot->linkSlackMembers();
        $user->save();
        $this->info('User created and API work done.');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['char_id', InputArgument::REQUIRED, 'EVE Character ID'],
            ['email', InputArgument::REQUIRED, 'Email registered on slack'],
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
