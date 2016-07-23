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

Route::group(['domain' => env('SLACK_DOMAIN', 'localhost')], function() {

    Route::get('/', 'WelcomeController@index');

    Route::get('home', 'HomeController@index');

    Route::get('auth/login', 'AuthController@redirectToProvider');

    Route::get('auth/logout', function () {
        Auth::logout();
        return redirect('/');
    });

    Route::get('callback', 'AuthController@handleProviderCallback');

    Route::post('sendping', function (Request $request, Pingbot $pingbot) {
        return $pingbot->processPingCommand($request->all());
    });
    Route::post('registerslack', function (Request $request, \JamylBot\Userbot\Userbot $userbot) {
        return $userbot->registerSlack($request->all());
    });
    Route::post('portrait', 'CommandController@getPortrait');
    Route::post('command', 'CommandController@chooseCommand');

    Route::post('csmbot', function (Request $request) {
        return array (
            'text'    =>  "Provi Candidates for CSM XI:\n"
                            ."Jin'taan - http://forum.cva-eve.org/index.php?topic=8962 | https://forums.eveonline.com/default.aspx?g=posts&t=464401\n"
                            ."Utari Onzo - http://forum.cva-eve.org/index.php?topic=8979.0 | https://forums.eveonline.com/default.aspx?g=posts&t=464487\n"
                            ."Voting begins on February 29th\n",
        );
    });

    Route::post('form/addEmail', 'HomeController@addEmail');

    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {

        Route::resource('groups', 'GroupController');
        Route::post('groups/{groupId}/add-user', 'GroupController@addUserToGroup')->where(['groupId' => '[0-9]+']);
        Route::post('groups/{groupId}/remove-user', 'GroupController@removeUserFromGroup')->where(['groupId' => '[0-9]+']);
        Route::post('groups/{groupId}/add-channel', 'GroupController@addChannelToGroup')->where(['groupId' => '[0-9]+']);
        Route::post('groups/{groupId}/remove-channel', 'GroupController@removeChannelFromGroup')->where(['groupId' => '[0-9]+']);
        Route::post('groups/{groupId}/add-owner', 'GroupController@addOwnerToGroup')->where(['groupId' => '[0-9]+']);
        Route::post('groups/{groupId}/remove-owner', 'GroupController@removeOwnerFromGroup')->where(['groupId' => '[0-9]+']);
        ROute::post('groups/{groupId}/add-corp', 'GroupController@addCorpToGroup')->where(['groupId' => '[0-9]+']);
        ROute::post('groups/{groupId}/remove-corp', 'GroupController@removeCorpFromGroup')->where(['groupId' => '[0-9]+']);

        Route::controller('users', 'UserController');

    });

});

Route::get('/', 'WelcomeController@portal');
