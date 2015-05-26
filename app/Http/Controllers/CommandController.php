<?php namespace JamylBot\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JamylBot\Http\Requests;
use JamylBot\Http\Controllers\Controller;

use Illuminate\Http\Request;
use JamylBot\User;
use JamylBot\Userbot\SlackMonkey;

class CommandController extends Controller {

    protected $requestVars;
    protected $slack;

    public function __construct(Request $request, SlackMonkey $slack)
    {
        $this->requestVars = $request->all();
        $this->slack = $slack;
    }

	/**
	 * Return a link to the users character portrait.
	 *
	 * @return Response
	 */
	public function getPortrait()
	{
        if (!$this->checkToken()) {
            return 'Authentication error';
        }
        try {
            $user = User::findBySlack($this->requestVars['user_id']);
        } catch (ModelNotFoundException $e) {
            return 'User not found. Did you register on the management system yet? If so, try again in 5 minutes, or type /register then try again.';
        }
        if (in_array($this->requestVars['text'], config('eve.avatar_sizes'))){
            $suffix = '_'.$this->requestVars['text'].'.jpg';
        } else {
            $suffix = '_512.jpg';
        }
        $link = config('eve.avatar_url').$user->char_id.$suffix;
        $payload = [
            'channel' => '@'.$this->requestVars['user_name'],
            'username' => config('pingbot.ping-bot-name'),
            'icon_emoji' => config('pingbot.ping-bot-emoji'),
            'text' => $link,
        ];
        $this->slack->sendMessageToServer($payload);
        return "Portrait link sent to slackbot DM channel \n".$link;
	}

    protected function punkBirthday()
    {
        $payload = [
            'channel' => '#p-drama',
            'username' => config('pingbot.ping-bot-name'),
            'icon_emoji' => config('pingbot.ping-bot-emoji'),
            'text' => 'HAPPY BIRTHDAY @punkslap',
            'link_names' => 1,
        ];
        $this->slack->sendMessageToServer($payload);
        return "Punk'd";
    }

    public function chooseCommand()
    {
        if (!$this->checkToken()) {
            return 'Authentication error';
        }
        switch ($this->requestVars['command']) {
            case '/punk':
                return $this->punkBirthday();
            default:
                return 'Unknown command';
        }
    }

    /**
     * @return bool
     */
    protected function checkToken()
    {
        return $this->requestVars['token'] == config('slack.command-tokens.'.$this->requestVars['command']);
    }

}
