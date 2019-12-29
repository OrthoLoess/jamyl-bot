<?php namespace JamylBot\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class JbMapController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | JbMap Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders the "JumpBridge Map" for the ProviBloc and
    | is configured to allow anyone. 
    | 
    |
    */

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function index()
    {
        $url = Cache::rememberForever('jbmap_url', function () {
            return \config('jbmap.url');
        });
        return http_redirect($url);
    }

}
