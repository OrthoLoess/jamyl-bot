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

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::get('login', 'AuthController@redirectToProvider');

Route::get('callback', 'AuthController@handleProviderCallback');

Route::post('sendping', function (Request $request, Pingbot $pingbot){
    return $pingbot->processPingCommand($request->all());
});

Route::get('test', function (\JamylBot\Userbot\ApiMonkey $api){
    //return $api->checkCharacter('1124364023,');90274790

    $api->addToAffiliationQueue('1124364023');
    $api->addToAffiliationQueue('902747f905');

    $api->sendQueuedCall();

});

Route::get('slack', function (\JamylBot\Userbot\SlackMonkey $slack){
    //return $slack->getUsers();//'G04G7KMFM');
    //$slack->setActive('U04FM6218');
    //$slack->sendInvite('mail@ratship.net', 'Trevor Kipling', ['C04G7GNLT']);

    return 'done';
});