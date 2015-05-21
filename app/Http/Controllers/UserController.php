<?php namespace JamylBot\Http\Controllers;

use JamylBot\Http\Requests;
use JamylBot\Http\Controllers\Controller;

use Illuminate\Http\Request;
use JamylBot\User;

class UserController extends Controller {

	public function getIndex()
    {
        // List all users
        $users = User::all();
        $title = 'All Users';
        return view('admin.users.index', compact('users', 'title'));
    }

    public function deleteIndex()
    {
        $id = \Request::input('id');
        // Delete user $id
        $user = User::find($id)->first();
        $user->groups()->sync([]);
        $user->delete();
        return redirect('/admin/users');
    }

    public function getPending()
    {
        // List users who have entered an email but have not registered on slack.
        $users = User::where();
    }

}
