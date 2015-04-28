<?php namespace JamylBot\Http\Controllers;

use JamylBot\Http\Requests;
use JamylBot\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Socialize;

class AuthController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
//
	}

    public function redirectToProvider()
    {
        return Socialize::with('eveonline')->redirect();
    }

    public function handleProviderCallback()
    {
        $user = Socialize::with('eveonline')->user();

        dd($user);
    }

}
