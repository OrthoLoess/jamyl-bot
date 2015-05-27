<?php namespace JamylBot\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use JamylBot\Group;

class MustBeAdmin {

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/home')->with('auth_message', 'Must be logged in.');
            }
        }
        /** @var \JamylBot\User $user */
        $user = $this->auth->user();
        if ($user->admin) {
            return $next($request);
        }
        $groupId = $request->groupId ? $request->groupId : $request->groups;
        if ($groupId) {
            /** @var Group $group */
            $group = Group::find($groupId);
            if ($group->isOwner($user->id)) {
                return $next($request);
            }
        }

        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect('/home')->with('auth_message', 'Access Denied');
        }


    }

}
