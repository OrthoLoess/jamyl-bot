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
    public function view()
    {
        $url = Cache::rememberForever('jbmap_url', function () {
            return \config('jbmap.url');
        });
        $desc = Cache::rememberForever('jbmap_desc', function () {
            return \config('jbmap.description');
        });
        return view('jbmap', ['jburl' => $url, 'description' => $desc ]);
    }

}
