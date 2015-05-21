<?php namespace JamylBot\Http\Controllers;

use JamylBot\Group;
use JamylBot\Http\Requests;
use JamylBot\Http\Controllers\Controller;
use JamylBot\User;


class GroupController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$groups = Group::all();
        foreach ($groups as $group) {
            $ownerNames = [];
            $owners = $group->getOwners();
            foreach ($owners as $owner) {
                $ownerNames[] = User::find($owner)->char_name;
            }
            $group->owners = implode(', ', $ownerNames);
        }
        return view('admin.groups.index', compact('groups'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('admin.groups.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		Group::create(['name' => \Request::input('name'), 'owners' => \Auth::user()->id]);
        return redirect('/admin/groups');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        /** @var Group $group */
        $group = Group::find($id);
        if ($group == null) {
            abort(404);
        }
        return view('admin.groups.show', [
            'id'    => $group->id,
            'name'  => $group->name,
            'channels'  => $group->channels,
            'users'     => $group->users,
        ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$group = Group::find($id)->first();
        $group->channels()->sync([]);
        $group->users()->sync([]);
        $group->delete();
        return redirect('/admin/groups');
	}

}
