<?php namespace JamylBot\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use JamylBot\Userbot\Userbot;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

    protected $userbot;
    protected $user;

	/**
	 * Create a new controller instance.
	 *
     * @param Userbot $userbot
	 */
	public function __construct(Userbot $userbot)
	{
		$this->middleware('auth');
        $this->userbot = $userbot;
        $this->user = \Auth::user();
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('home', [
            'name' => $this->user->char_name,
            'avatar' => $this->user->getAvatarUrl(),
            'email' => $this->user->email,
            'slackName' => $this->user->slack_name,
            'status' => $this->user->status,
            'corp' => $this->user->corp_name,
            'alliance' => $this->user->alliance_name,
        ]);
	}

    public function addEmail(Request $request)
    {
        $email = $request->input('email');
        //dd($email);
        $this->userbot->addEmail($this->user, $email);
        return redirect('home');
    }

}
