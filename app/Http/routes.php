<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Http\Request;
use \JamylBot\Pingbot\Pingbot;

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::get('login', ['middleware' => 'guest', 'uses' => 'AuthController@redirectToProvider']);

Route::get('auth/logout', function() {
    Auth::logout();
    return redirect('/');
});

Route::get('callback', ['middleware' => 'guest', 'uses' => 'AuthController@handleProviderCallback']);

Route::post('sendping', function (Request $request, Pingbot $pingbot){
    return $pingbot->processPingCommand($request->all());
});

Route::get('test', function (\JamylBot\Userbot\Userbot $bot){
    //return $api->checkCharacter('1124364023,');90274790

    //dd(JamylBot\User::listNeedUpdateIds(10));
    //$api->addToAffiliationQueue('1124364023', true);
    //$api->addToAffiliationQueue('902747f905');
    $bot->performUpdates();
    //$api->sendQueuedCall();

});

Route::get('slack', function (\JamylBot\Userbot\SlackMonkey $slack){
    //return $slack->getUsers();//'G04G7KMFM');
    //$slack->setActive('U04FM6218');
    //$slack->sendInvite('mail@ratship.net', 'Trevor Kipling', ['C04G7GNLT']);

    return 'done';
});