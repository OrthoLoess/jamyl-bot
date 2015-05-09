<?php namespace JamylBot\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JamylBot\Http\Requests;
use JamylBot\Http\Controllers\Controller;

use Illuminate\Http\Request;
use JamylBot\User;

class CommandController extends Controller {

    public $requestVars;

    public function __construct(Request $request)
    {
        $this->requestVars = $request->all();
    }

	/**
	 * Return a link to the users character portrait.
	 *
	 * @return Response
	 */
	public function getPortrait()
	{
        if (!$this->checkToken())
            return 'Authentication error';
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
        return config('eve.avatar_url').$user->char_id.$suffix;
	}

    /**
     * @return bool
     */
    protected function checkToken()
    {
        return $this->requestVars['token'] == config('slack.command-tokens.'.$this->requestVars['command']);
    }

}
